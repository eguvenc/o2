<?php

namespace Obullo\Log;

use Closure;
use Exception;
use LogicException;
use ErrorException;
use RuntimeException;
use Obullo\Queue\Queue;
use Obullo\Container\Container;
use Obullo\Error\ErrorHandler;

/**
 * Main logger trait
 */
trait LoggerTrait
{
    /**
     * Initialize config parameters
     * 
     * @return void
     */
    public function initialize()
    {
        $this->c['config']->load('logger');  // Load logger package configuration

        $this->channel = $this->c['config']['logger']['default']['channel'];
        $this->queries = $this->c['config']['logger']['extra']['queries'];
        $this->benchmark = $this->c['config']['logger']['extra']['benchmark'];

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
     * Detect logger type
     * 
     * @return void
     */
    protected function detectRequest()
    {
        $this->request = 'http'; // Http request
        if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->request ='ajax';  // Ajax request
        }
        if (defined('STDIN')) {  // Cli request
            $this->request = 'cli';
        }
        if (isset($_SERVER['argv'][1]) AND $_SERVER['argv'][1] == 'worker') {  // Job Server request
            $this->request = 'worker';
            if ($this->c['config']['logger']['queue']['workers']['logging'] == false) {
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
        if ( ! isset($this->registeredHandlers[$name])) {
            throw new LogicException(
                sprintf(
                    'The push handler %s is not defined in your logger service.', 
                    $name
                )
            );
        }
        $this->addWriter($name, 'handler');
        $this->loadedHandlers[$name] = $name;
        return $this;
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
     * Add writer
     * 
     * @param string $name handler key
     * @param string $type writer/handler
     *
     * @return object
     */
    public function addWriter($name, $type = 'writer')
    {
        $this->priorityQueue[$name] = new PriorityQueue;
        $this->writers[$name] = array('priority' => $this->registeredHandlers[$name]['priority'], 'type' => $type);
        $this->track[] = array('type' => 'writers', 'name' => $name);
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
        if ( ! isset($this->filterNames[$name])) {
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
     * @return void
     */
    public function log($level, $message, $context = array(), $priority = null)
    {
        if ( ! $this->enabled) {
            return $this;
        }
        if (is_object($message) && $message instanceof Exception) {
            $this->logExceptionError($message);
            return;
        }
        $recordUnformatted = array();
        if (isset(static::$priorities[$level])) { // is Allowed level ?
            $recordUnformatted['channel'] = $this->channel;
            $recordUnformatted['level']   = $level;
            $recordUnformatted['message'] = $message;
            $recordUnformatted['context'] = $context;
            $this->sendToQueue($recordUnformatted, $priority); // Send to Job queue
            $this->channel($this->c['config']['logger']['default']['channel']);          // reset channel to default
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
    public function sendToQueue($recordUnformatted, $messagePriority = null)
    {
        foreach ($this->writers as $name => $val) {
            $filteredRecords = $this->getFilteredRecords($name, $recordUnformatted);
            if (is_array($filteredRecords) AND count($filteredRecords) > 0) {  // If we have records.
                $this->connect(true);  // Lazy connections ...
                $this->priorityQueue[$name]->insert($filteredRecords, (empty($messagePriority)) ? $val['priority'] : $messagePriority);
            }
        }
    }

    /**
     * Get splPriority object of valid handler
     * 
     * @param string $handler name
     * 
     * @return object of handler
     */
    public function getQueue($handler = 'file')
    {
        if ( ! isset($this->priorityQueue[$handler])) {
            throw new LogicException(
                sprintf(
                    'The log handler %s is not defined.', 
                    $handler
                )
            );
        }
        return $this->priorityQueue[$handler];
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
        if ( ! $this->hasFilter($handler)) {
            return $recordUnformatted;
        }
        foreach ($this->filters[$handler] as $value) {
            $Class = '\\'.$value['class'];
            $method = $value['method'];

            $filter = new $Class($this->c);
            $filter->params = $value['params'];  // Inject filter parameters into filter class.

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
        if ($this->enabled == false) {
            return;
        }
        foreach ($this->writers as $name => $val) {
            if ($val['type'] == 'handler') {
                if ( ! isset($this->writers[$name])) {
                    throw new LogicException(
                        sprintf(
                            'The push handler %s not available in log writers please first load it using below the command. <pre>%s</pre>', 
                            $name,
                            '$this->logger->load("handler");'
                        )
                    );
                }
                $this->push[$name] = 1; // Allow push data to valid push handler.
            }
        }
    }

    /**
     * Extract log data
     * 
     * @param object $name PriorityQueue name
     * 
     * @return array records
     */
    protected function extract($name)
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
     * Extract queued log handlers data store them into one array
     * 
     * @return void
     */
    protected function exec()
    {
        $exp = explode('\\', __CLASS__);

        $this->payload['logger'] = end($exp);
        $this->payload['primary'] = $this->writers[$this->getPrimaryWriter()]['priority'];
        foreach ($this->writers as $name => $val) {  // Write log data to foreach handlers
            if ( ! isset($this->push[$name]) && isset($this->loadedHandlers[$name])) {     // If handler available in push data.
                return;
            }
            $priority = $val['priority'];
            $records = $this->extract($name);
            if (empty($records)) {
                continue;
            }
            $this->payload[$priority]['request'] = $this->request;
            $this->payload[$priority]['handler'] = $name;
            $this->payload[$priority]['type'] = $val['type'];
            $this->payload[$priority]['time'] = time();
            $this->payload[$priority]['record'] =  $records; // set record array
        }
        asort($this->payload);
    }

}

// END LoggerTrait File
/* End of file LoggerTrait.php

/* Location: .Obullo/Log/LoggerTrait.php */