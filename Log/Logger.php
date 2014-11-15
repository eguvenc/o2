<?php

namespace Obullo\Log;

use Closure, 
    LogicException, 
    ErrorException,
    RuntimeException,
    Obullo\Log\PriorityQueue,
    Obullo\Error\ErrorHandler;

/**
 * Logger Class
 *
 * Modeled after Zend Log package.
 * 
 * @category  Log
 * @package   Logger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class Logger extends AbstractLogger
{
    /**
     * Log priorities
     * 
     * @var array
     */
    public static $priorities = array(
        'emergency' => LOG_EMERG,
        'alert'     => LOG_ALERT,
        'critical'  => LOG_CRIT,
        'error'     => LOG_ERR,
        'warning'   => LOG_WARNING,
        'notice'    => LOG_NOTICE,
        'info'      => LOG_INFO,
        'debug'     => LOG_DEBUG,
    );

    /**
     * Map native PHP errors to priority
     *
     * @var array
     */
    public static $errorPriorities = array(
        E_NOTICE            => LOG_NOTICE,
        E_USER_NOTICE       => LOG_NOTICE,
        E_WARNING           => LOG_WARNING,
        E_CORE_WARNING      => LOG_WARNING,
        E_USER_WARNING      => LOG_WARNING,
        E_ERROR             => LOG_ERR,
        E_USER_ERROR        => LOG_ERR,
        E_CORE_ERROR        => LOG_ERR,
        E_RECOVERABLE_ERROR => LOG_ERR,
        E_STRICT            => LOG_DEBUG,
        E_DEPRECATED        => LOG_DEBUG,
        E_USER_DEPRECATED   => LOG_DEBUG,
    );

    /**
     * Queue object
     * 
     * @var object
     */
    public $queue;

    /**
     * Config object
     * 
     * @var object
     */
    public $config;

    /**
     * Write all outputs to end of the page
     * 
     * @var boolean
     */
    public $debug = false;

    /**
     * Debug handler
     * 
     * @var string
     */
    public $debugHandler;

    /**
     * On / Off Logging
     * 
     * @var boolean
     */
    public $enabled = true;

    /**
     * Whether to log sql queries
     * 
     * @var boolean
     */
    public $queries = false;

    /**
     * Whether to log benchmark, Memory usage ..
     * 
     * @var boolean
     */
    public $benchmark = false;

    /**
     * Available  writers: file, mongo, syslog & so on ..
     * 
     * @var array
     */
    public $writers = array();

    /**
     * Default log channel
     * 
     * @var string
     */
    public $channel = 'system';

    /**
     * Filter classes folder
     * 
     * @var string
     */
    protected $filterPath;

    /**
     * Registered log handlers
     * 
     * @var array
     */
    protected $registeredHandlers = array();

    /**
     * Log priority queue objects
     * 
     * @var array
     */
    protected $priorityQueue = array();

    /**
     * Priority values
     * 
     * @var array
     */
    public static $priorityValues = array();

    /**
     * Push data
     * 
     * @var array
     */
    protected $push = array();

    /**
     * Track data for handlers and writers
     * 
     * @var array
     */
    public $track = array();

    /**
     * Payload
     * 
     * @var array
     */
    protected $payload = array();

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
     * @param object $queue  queue service object
     * @param array  $params configuration
     */
    public function __construct($c, $queue, $params = array())
    {
        $this->c = $c;
        $this->queue = $queue;
        $this->config = $params;
        $this->enabled = $this->config['log']['control']['enabled'];
        $this->debug = $this->config['log']['control']['output'];
        $this->channel = $this->config['log']['default']['channel'];
        $this->queries = $this->config['log']['extra']['queries'];
        $this->benchmark = $this->config['log']['extra']['benchmark'];

        $errorDebug = $c->load('config')['error']['debug'];
        $errorReporting = $c->load('config')['error']['reporting'];

        if ($errorDebug == false) {                   // If debug "disabled" from config use logger class handlers and send all errors to log.
            static::registerExceptionHandler($this); 
            static::registerErrorHandler($this);
            static::registerFatalErrorHandler($this);
        }
        if ($errorReporting == true AND $errorDebug == false) { // If "Php Native Error Reporting" "enabled" from config restore handlers and use native errors.
            static::unregisterErrorHandler();                   // Also write errors to log file. Especially designed for "local" environment.
            static::unregisterExceptionHandler();
        }
        $this->type = 'http';   // Default Http requests
        if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { // Ajax requests
            $this->type ='ajax';
        }
        if (defined('STDIN')) {  // Cli requests
            $this->type = 'cli';
        }
        if (isset($_SERVER['argv'][1]) AND $_SERVER['argv'][1] == 'worker') {  // Job Server
            $this->type = 'worker';
            if ($this->c['config']['log']['queue']['workers']['logging'] == false) {
                $this->enabled == false;
            }
        }
        register_shutdown_function(array($this, 'close'));
    }

    /**
     * Handlers full path
     * 
     * @param string $path path
     * 
     * @return void
     */
    public function registerFilterPath($path)
    {
        $this->filterPath = $path;
        return $this;
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
                    'The push handler %s is not defined in your log service.', 
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
    public function getWriterName()
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
        if (strpos($name, '.') > 0) {
            list($name, $method) = explode('.', $name);
        }
        if ( ! isset($this->filterNames[$name])) {
            throw new LogicException(
                sprintf(
                    'The filter %s is not registered in your logger service. Please first register it with following command. <pre>%s</pre> .', 
                    $name,
                    '$log->registerFilter(\'class.method\', \'Log\Filters\ClassNameFilter\');'
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
        $recordUnformatted = array();
        if (isset(static::$priorities[$level])) { // is Allowed level ?
            $recordUnformatted['channel'] = $this->channel;
            $recordUnformatted['level']   = $level;
            $recordUnformatted['message'] = $message;
            $recordUnformatted['context'] = $context;
            $this->sendToQueue($recordUnformatted, $priority); // Send to Job queue
            $this->channel($this->config['log']['default']['channel']);          // reset channel to default
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
            $filter = new $Class($this->c, $value['params']);
            if (count($recordUnformatted) > 0) {
                $recordUnformatted = $filter->$method($recordUnformatted);
            }
        }
        return $recordUnformatted;
    }

    /**
     * Register logging system as an error handler to log PHP errors
     *
     * @param object  $logger                class
     * @param boolean $continueNativeHandler native handler switch
     * 
     * @return mixed Returns result of set_error_handler
     */
    public static function registerErrorHandler(Logger $logger, $continueNativeHandler = false)
    {
        if (static::$registeredErrorHandler) {  // Only register once per instance
            return false;
        }
        $errorPriorities = static::$errorPriorities;
        $previous = set_error_handler(
            function ($level, $message, $file, $line) use ($logger, $errorPriorities, $continueNativeHandler) {
                $iniLevel = error_reporting();
                if ($iniLevel & $level) {
                    $priority = Logger::$priorities['error'];
                    if (isset($errorPriorities[$level])) {
                        $priority = $errorPriorities[$level];
                    } 
                    $logger->log(
                        'error', 
                        $message, 
                        array(
                        'level' => $level,
                        'file'  => $file,
                        'line'  => $line,
                        ),
                        $priority
                    );
                }
                return ! $continueNativeHandler;
            }
        );
        static::$registeredErrorHandler = true;
        return $previous;
    }

    /**
     * Register logging system as an exception handler to log PHP exceptions
     *
     * @param object $logger class
     * 
     * @return boolean
     */
    public function registerExceptionHandler(Logger $logger)
    {
        if (static::$registeredExceptionHandler) {  // Only register once per instance
            return false;
        }
        $errorPriorities = static::$errorPriorities;
        set_exception_handler(
            function ($exception) use ($logger, $errorPriorities) {
                $logMessages = array();  // @see http://www.php.net/manual/tr/errorexception.getseverity.php
                do {
                    $priority = Logger::$priorities['error'];
                    if ($exception instanceof ErrorException AND isset($errorPriorities[$exception->getSeverity()])) {
                        $priority = $errorPriorities[$exception->getSeverity()];
                    }
                    $extra = array(
                        'file'  => $exception->getFile(),
                        'line'  => $exception->getLine(),
                        // 'trace' => $exception->getTrace(),
                    );
                    if (isset($exception->xdebug_message)) {
                        $extra['xdebug'] = $exception->xdebug_message;
                    }
                    $logMessages[] = array(
                        'priority' => $priority,
                        'message'  => $exception->getMessage(),
                        'extra'    => $extra,
                    );
                    $exception = $exception->getPrevious();
                } while ($exception);

                foreach (array_reverse($logMessages) as $logMessage) {
                    $logger->log('error', $logMessage['message'], $logMessage['extra'], $logMessage['priority']);
                }
            }
        );
        static::$registeredExceptionHandler = true;
        return true;
    }

    /**
     * Register a shutdown handler to log fatal errors
     *
     * @param object $logger class
     * 
     * @return bool
     */
    public static function registerFatalErrorHandler(Logger $logger)
    {
        if (static::$registeredFatalErrorShutdownFunction) {  // Only register once per instance
            return false;
        }
        register_shutdown_function(
            function () use ($logger) {
                if (null != $error = error_get_last()) {
                    $logger->log(
                        'error', 
                        $error['message'], 
                        array(
                            'level' => $error['type'],
                            'file' => $error['file'], 
                            'line' => $error['line']
                            )
                    );
                    $logger->close();
                }
            }
        );
        static::$registeredFatalErrorShutdownFunction = true;
        return true;
    }

    /**
     * Unregister error handler
     *
     * @return void
     */
    public static function unregisterErrorHandler()
    {
        restore_error_handler();
        static::$registeredErrorHandler = false;
    }

    /**
     * Unregister exception handler
     *
     * @return void
     */
    public static function unregisterExceptionHandler()
    {
        restore_exception_handler();
        static::$registeredExceptionHandler = false;
    }

    /**
     * Enable html debugger
     * 
     * @return void
     */
    public function printDebugger()
    {
        $this->debug = true;
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
     * Execute writer process
     * 
     * @return void
     */
    protected function exec()
    {
        foreach ($this->writers as $name => $val) {  // Write log data to foreach handlers
            if ( ! isset($this->push[$name]) AND isset($this->loadedHandlers[$name])) {     // If handler available in push data.
                return;
            }
            $this->payload[$name]['type'] = $this->type;
            $this->payload[$name]['priority'] = $val['priority'];
            $this->payload[$name]['record'] = $this->extract($name);
            $this->payload[$name]['batch'] = true;
        }
    }

    /**
     * Push ( Write log handlers data )
     * 
     * @return void
     */
    public function push()
    {        
        if ($this->debug OR $this->enabled == false) {
            return;
        }
        foreach ($this->writers as $name => $val) {
            if ($val['type'] == 'handler') {
                if ( ! isset($this->writers[$name])) {
                    throw new LogicException(
                        sprintf(
                            'The push handler %s not available in log writers please first load it using below the command.
                            <pre>$this->logger->load(handler);</pre>', 
                            $name
                        )
                    );
                }
                $this->push[$name] = 1; // Allow push data to valid push handler.
            }
        }
    }

    /**
     * End of the logs and beginning of the handlers.
     *
     * @return void
     */
    public function close()
    {
        if ($this->debug) {         // Debug output for log data if enabled
            $primaryWriter = $this->getWriterName();
            $debug = new Debug($this->c, $this, $primaryWriter);
            $pQ = $this->getQueue($primaryWriter);
            echo $debug->printDebugger($pQ);
            return;
        }
        if ($this->enabled == false) {  // Check logger is disabled.
            return;
        }
        $this->exec('writer');

        // print_r($this->payload);

        $this->queue->channel($this->config['queue']['channel']); // Set channel at top
        $this->queue->push(
            $this->config['queue']['job'],
            $this->config['queue']['route'],
            $this->payload,
            $this->config['queue']['delay']
        );
    }

}

// END Logger class

/* End of file Logger.php */
/* Location: .Obullo/Log/Logger.php */