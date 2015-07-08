<?php

namespace Obullo\Log;

use Closure;
use Exception;
use LogicException;
use ErrorException;
use RuntimeException;
use Obullo\Queue\Queue;
use Obullo\Error\ErrorHandler;
use Obullo\Container\ContainerInterface;

/**
 * Logger Class
 * 
 * @category  Log
 * @package   Logger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class Logger extends AbstractLogger implements LoggerInterface
{
    public $c;                                // Container
    public $config;                           // Config object
    public $params = array();                 // Service parameters
    public $queries = false;                  // Whether to log sql queries
    public $benchmark = false;                // Whether to log benchmark, Memory usage ..
    public $channel = 'system';               // Default log channel

    protected $p = 100;                       // Default priority
    protected $writers = array();             // Available  writers: file, mongo, syslog & so on ..
    protected $handlers = array();            // Handlers
    protected $hcount = 0;                    // Handler count
    protected $hrcount = 0;                   // Handler record count
    protected $connect = false;               // Lazy connections
    protected $push = array();                // Push data
    protected $payload = array();             // Payload
    protected $priorityQueue = array();       // Log priority queue objects
    protected $handlerRecords = array();      // Handler records
    protected $loadedHandlers = array();      // Loaded handlers

    /**
     * Registered error handlers
     *
     * @var bool
     */
    protected static $registeredErrorHandler = false;
    protected static $registeredExceptionHandler = false;
    protected static $registeredFatalErrorShutdownFunction = false;

    /**
     * Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct(ContainerInterface $c, $params = array())
    {
        $this->c = $c;
        $this->params = $params;
        $this->enabled = $c['config']['log']['enabled'];
        $this->config  = $c['config']->load('logger');  // Load logger package configuration

        $this->initialize();
        register_shutdown_function(array($this, 'close'));
    }

    /**
     * Initialize config parameters
     * 
     * @return void
     */
    public function initialize()
    {
        $this->channel = $this->config['default']['channel'];
        $this->queries = $this->config['app']['query']['log'];
        $this->benchmark = $this->config['app']['benchmark']['log'];

        $this->detectRequest();  
    }

    /**
     * Lazy connections
     * 
     * We execute this method in LoggerTrait sendQueue() method 
     * then if connect == true we open the connection in close method.
     *
     * @param boolean $connect on / off connection
     * 
     * @return void
     */
    public function connect($connect = true)
    {
        $this->connect = $connect;
    }
    
    /**
     * Whether to learn we have log data
     * 
     * @return boolean
     */
    protected function isConnected()
    {
        return $this->connect;
    }

    /**
     * Detect logger type
     * 
     * @return void
     */
    protected function detectRequest()
    {
        $this->request = 'http'; // Http request
        if (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->request ='ajax';  // Ajax request
        }
        if (defined('STDIN')) {  // Cli request
            $this->request = 'cli';
        }
        if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'worker') {  // Job Server request
            $this->request = 'worker';
            if ($this->isRegisteredAsWorker() == false) {
                $this->enabled = false;
            }
        }      
    }

    /**
     * Load defined log handler
     * 
     * @param string $name defined log handler name
     * 
     * @return object
     */
    public function load($name)
    {
        if (! isset($this->registeredHandlers[$name])) {
            throw new LogicException(
                sprintf(
                    'The push handler %s is not defined in your logger service.', 
                    $name
                )
            );
        }
        $this->addHandler($name);
        return $this;
    }

    /**
     * Add push handler
     * 
     * @param string $name name
     *
     * @return object Logger
     */
    protected function addHandler($name) 
    {
        $this->handlers[$name] = array('priority' => $this->registeredHandlers[$name]['priority']);
        $this->loadedHandlers[$this->hcount] = $name;
        $this->priorityQueue['handler.'.$name] = new PriorityQueue;
        $this->track[] = array('name' => $name);
        ++$this->hcount;
        return $this;
    }

    /**
     * Add writer
     * 
     * @param string $name handler key
     *
     * @return object
     */
    public function addWriter($name)
    {
        $this->priorityQueue[$name] = new PriorityQueue;
        $this->writers[$name] = array('priority' => $this->registeredHandlers[$name]['priority']);
        $this->track[] = array('name' => $name);
        return $this;
    }

    /**
     * Returns to primary writer name.
     * 
     * @return string returns to "xWriter"
     */
    public function getPrimaryWriter()
    {
        $writers = $this->getWriters();
        return array_keys($writers)[0];
    }

    /**
     * Returns to all writers
     * 
     * @return array
     */
    public function getWriters()
    {
        return $this->writers;
    }

    /**
     * Change channel
     * 
     * @param string $channel add a channel
     * 
     * @return object
     */
    public function channel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Reserve your filter to valid log handler
     * 
     * @param string $name   filter name
     * @param array  $params data
     * 
     * @return object
     */
    public function filter($name, $params = array())
    {
        $method = 'filter';
        if (strpos($name, '@') > 0) {
            list($name, $method) = explode('@', $name);
        }
        if (! isset($this->filterNames[$name])) {
            throw new LogicException(
                sprintf(
                    'The filter %s is not registered in your logger service. Please first register it with following command. <pre>%s</pre> .', 
                    $name,
                    '$logger->registerFilter(\'filtername\', \'Log\Filters\ClassNameFilter\');'
                )
            );
        }
        $end = end($this->track);
        $handler = $end['name'];
        $this->filters[$handler][] = array('class' => $this->filterNames[$name], 'method' => $method, 'params' => $params);
        return $this;
    }

    /**
     * Check handler has filters
     * 
     * @param string $handler name
     * 
     * @return boolean
     */
    public function hasFilter($handler)
    {
        if (isset($this->filters[$handler])) {
            return true;
        }
        return false;
    }

    /**
     * Store log data into array
     * 
     * @param string  $level    log level
     * @param string  $message  log message
     * @param array   $context  context data
     * @param integer $priority message priority
     * 
     * @return object Logger
     */
    public function log($level, $message, $context = array(), $priority = null)
    {
        if (! $this->isEnabled()) {
            return $this;
        }
        if (is_object($message) && $message instanceof Exception) {
            $this->logExceptionError($message);
            return $this;
        }
        if (count($this->loadedHandlers) > 0) {   // Start log capture and reset capture when we use push() method.
            $this->handlerRecords[$this->hrcount]['channel'] = $this->channel;
            $this->handlerRecords[$this->hrcount]['level']   = $level;
            $this->handlerRecords[$this->hrcount]['message'] = $message;
            $this->handlerRecords[$this->hrcount]['context'] = $context;
            $this->handlerRecords[$this->hrcount]['priority'] = $priority;
            ++$this->hrcount;
            return $this;
        }
        $recordUnformatted = array();
        if (isset(static::$priorities[$level])) {  // Is Allowed level ?
            $recordUnformatted['channel'] = $this->channel;
            $recordUnformatted['level']   = $level;
            $recordUnformatted['message'] = $message;
            $recordUnformatted['context'] = $context;
            $this->sendToWriterQueue($recordUnformatted, $priority);    // Send to Job queue
            $this->channel($this->config['default']['channel']);  // reset channel to default
        }
        return $this;
    }

    /**
     * Send logs to Queue for each log handler.
     *
     * $processor = new SplPriorityQueue;
     * $processor->insert($record, $priority = 0); 
     * 
     * @param array   $recordUnformatted unformated log data
     * @param integer $messagePriority   messagePriority
     * 
     * @return void
     */
    protected function sendToWriterQueue($recordUnformatted, $messagePriority = 0)
    {
        if ($messagePriority == 0) {
            $this->p = $this->p - 1;  // Default priority
        }
        foreach (array_keys($this->writers) as $name) {
            $filteredRecords = $this->getFilteredRecords($name, $recordUnformatted);

            if (is_array($filteredRecords) && count($filteredRecords) > 0) {  // If we have records.
                $this->connect(true);  // Lazy connections ...
                $this->priorityQueue[$name]->insert($filteredRecords, (empty($messagePriority)) ? $this->p : $messagePriority);
            }
        }
    }

    /**
     * Send handler's log to Queue
     * 
     * @param string  $name              handler
     * @param array   $recordUnformatted records 
     * @param integer $messagePriority   
     * 
     * @return void
     */
    protected function sendToHandlerQueue($name, $recordUnformatted, $messagePriority = null)
    {
        $filteredRecords = $this->getFilteredRecords($name, $recordUnformatted);

        if (is_array($filteredRecords) && count($filteredRecords) > 0) {  // If we have records.
            $this->connect(true);  // Lazy connections ...
            $this->priorityQueue['handler.'.$name]->insert($filteredRecords, $messagePriority);
        }
    }

    /**
     * Get splPriority object of valid handler
     * 
     * @param string $handler name
     * @param string $prefix  name
     * 
     * @return object of handler
     */
    protected function getQueue($handler = 'file', $prefix = '')
    {
        if (! isset($this->priorityQueue[$prefix.$handler])) {
            throw new LogicException(
                sprintf(
                    'The log handler %s is not defined.', 
                    $handler
                )
            );
        }
        return $this->priorityQueue[$prefix.$handler];
    }

    /**
     * Get filtered records
     * 
     * @param string $handler           handler name
     * @param array  $recordUnformatted record array
     * 
     * @return array data
     */
    protected function getFilteredRecords($handler, $recordUnformatted)
    {
        if (! $this->hasFilter($handler)) {
            return $recordUnformatted;
        }
        foreach ($this->filters[$handler] as $value) {
            $Class = '\\'.$value['class'];
            $method = $value['method'];

            $filter = new $Class($this->c, $value['params']); // Inject filter parameters into filter class.
            if (count($recordUnformatted) > 0) {
                $recordUnformatted = $filter->$method($recordUnformatted);
            }
        }
        return $recordUnformatted;
    }

    /**
     * Log exceptional messages
     * 
     * @param object $e ErrorException
     * 
     * @return void
     */
    public function logExceptionError($e)
    {
        $errorReporting = error_reporting();
        $records = array();
        $errorPriorities = $this->getErrorPriorities();

        do {
            $priority = $this->getPriorities()['error'];
            if ($e instanceof ErrorException && isset($errorPriorities[$e->getSeverity()])) {
                $priority = $errorPriorities[$e->getSeverity()];
            }
            $extra = [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ];
            if (isset($e->xdebug_message)) {
                $extra['xdebug'] = $e->xdebug_message;
            }
            $records[] = [
                'priority' => $priority,
                'message'  => $e->getMessage(),
                'extra'    => $extra,
            ];
            $e = $e->getPrevious();
        } while ($e && $errorReporting);

        foreach (array_reverse($records) as $record) {
            $this->error($record['message'], $record['extra'], $record['priority']);
        }
    }

    /**
     * Push ( Write log handlers data )
     * 
     * @return void
     */
    public function push()
    {
        if ($this->isEnabled() == false) {
            return;
        }
        $name = end($this->loadedHandlers);
        foreach ($this->handlerRecords as $recordUnformatted) {
            $priority = $recordUnformatted['priority'];
            unset($recordUnformatted['priority']);
            $this->sendToHandlerQueue($name, $recordUnformatted, $priority);  // Send to priority queue
        }
        $this->channel($this->config['default']['channel']);    // Reset channel to default
        $this->loadedHandlers = array();  // Reset loaded handler.
         array_pop($this->track);         // Remove last track to reset handler filters
    }

    /**
     * Extract log data
     * 
     * @param object $name PriorityQueue name
     * 
     * @return array records
     */
    public function extract($name)
    {
        $pQ = $this->getQueue($name);
        $pQ->setExtractFlags(PriorityQueue::EXTR_DATA); // Queue mode of extraction
        $records = array();
        if ($pQ->count() > 0) {
            $pQ->top();  // Go to Top
            $i = 0;
            while ($pQ->valid()) {         // Prepare Lines
                $records[$i] = $pQ->current();
                $pQ->next();
                ++$i;
            }
        }
        return $records;
    }

    /**
     * Assing Logger class name to records
     * 
     * @return void
     */
    protected function assignLogger()
    {
        $exp = explode('\\', __CLASS__);
        $this->payload['logger'] = end($exp);
    }

     /**
     * Extract queued log handlers data store them into one array
     * 
     * @return void
     */
    protected function execWriters()
    {
        $this->assignLogger();
        $this->payload['primary'] = $this->writers[$this->getPrimaryWriter()]['priority'];

        foreach ($this->writers as $name => $val) {  // Write log data to foreach handlers
            $records = $this->extract($name);
            if (empty($records)) {
                continue;
            }
            $priority = $val['priority'];
            $this->payload['writers'][$priority]['request'] = $this->request;
            $this->payload['writers'][$priority]['handler'] = $name;
            $this->payload['writers'][$priority]['type'] = 'writer';
            $this->payload['writers'][$priority]['time'] = time();
            $this->payload['writers'][$priority]['record'] =  $records; // set record array
        }
    }

    /**
     * Execute handler
     * 
     * @return void
     */
    public function execHandlers()
    {
        if (count($this->handlerRecords) == 0) { // If we haven't got any handler record don't parse handlers
            return;
        }
        foreach ($this->handlers as $name => $val) {  // Write log data to foreach handlers
            $records = $this->extract('handler.'.$name);
            if (empty($records)) {
                continue;
            }
            $priority = $val['priority'];
            $this->payload['writers'][$priority]['request'] = $this->request;
            $this->payload['writers'][$priority]['handler'] = $name;
            $this->payload['writers'][$priority]['type'] = 'handler';
            $this->payload['writers'][$priority]['time'] = time();
            $this->payload['writers'][$priority]['record'] =  $records; // set record array
        }
    }

    /**
     * Returns to rendered log records
     * 
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * End of the logs and beginning of the handlers.
     *
     * @return void
     */
    public function close()
    {
        if ($this->isEnabled() && $this->isConnected()) {   // Lazy loading for Logger service
                                                            // if connect method executed one time then we open connections and load classes
                                                            // When connect booelan is true we execute standart worker or queue.

            try {   // We couldn't catch exceptions in register shutdown level

                $this->execWriters();
                $this->execHandlers();
                $payload = $this->getPayload();

                if (isset($this->params['queue']['enabled']) && $this->params['queue']['enabled']) { // Queue Logger

                    $this->c->get('queue')
                        ->channel($this->params['queue']['channel'])
                        ->push(
                            'Workers\Logger',
                            $this->params['queue']['route'],
                            $payload,
                            $this->params['queue']['delay']
                        );

                } else {  // Standart Logger

                    $worker = new \Workers\Logger($this->c);
                    $worker->fire(null, $payload);
                }

            } catch (Exception $e) {
                $this->c['exception']->show($e);  // Display exception errors
            }
        }
    }

}

// END Logger class

/* End of file Logger.php */
/* Location: .Obullo/Log/Logger.php */