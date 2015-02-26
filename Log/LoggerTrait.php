<?php

namespace Obullo\Log;

use Closure;
use LogicException;
use ErrorException;
use RuntimeException;
use Obullo\Queue\Queue;
use Obullo\Container\Container;
use Obullo\Error\ErrorHandler;

/**
 * Main logger features
 */
trait LoggerTrait
{
    /**
     * Configure error features
     * 
     * @return void
     */
    protected function configureErrorHandlers()
    {
        $errorDebug = $this->c['config']['error']['debug'];
        $errorReporting = $this->c['config']['error']['reporting'];

        if ($errorDebug == false) {                   // If debug "disabled" from config use logger class handlers and send all errors to log.
            static::registerExceptionHandler($this); 
            static::registerErrorHandler($this);
            static::registerFatalErrorHandler($this);
        }
        if ($errorReporting == true AND $errorDebug == false) { // If "Php Native Error Reporting" "enabled" from config restore handlers and use native errors.
            static::unregisterErrorHandler();                   // Also write errors to log file. Especially designed for "local" environment.
            static::unregisterExceptionHandler();
        }
    }

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
     * Register logging system as an error handler to log PHP errors
     *
     * @param object  $logger                class
     * @param boolean $continueNativeHandler native handler switch
     * 
     * @return mixed Returns result of set_error_handler
     */
    public static function registerErrorHandler(AbstractLogger $logger, $continueNativeHandler = false)
    {
        if (static::$registeredErrorHandler) {  // Only register once per instance
            return false;
        }
        $errorPriorities = static::$errorPriorities;
        $previous = set_error_handler(
            function ($level, $message, $file, $line) use ($logger, $errorPriorities, $continueNativeHandler) {
                $iniLevel = error_reporting();
                if ($iniLevel & $level) {
                    $priority = static::$priorities['error'];
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
    public function registerExceptionHandler(AbstractLogger $logger)
    {
        if (static::$registeredExceptionHandler) {  // Only register once per instance
            return false;
        }
        $errorPriorities = static::$errorPriorities;
        set_exception_handler(
            function ($exception) use ($logger, $errorPriorities) {
                $logMessages = array();  // @see http://www.php.net/manual/tr/errorexception.getseverity.php
                do {
                    $priority = static::$priorities['error'];
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
    public static function registerFatalErrorHandler(AbstractLogger $logger)
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
     * Returns log priorities
     * 
     * @return array
     */
    public function getPriorities()
    {
        return static::$priorities;
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
                            <pre>$this->logger->load("handler");</pre>', 
                            $name
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
            if ( ! isset($this->push[$name]) AND isset($this->loadedHandlers[$name])) {     // If handler available in push data.
                return;
            }
            $priority = $val['priority'];
            $this->payload[$priority]['request'] = $this->request;
            $this->payload[$priority]['handler'] = $name;
            $this->payload[$priority]['type'] = $val['type'];
            $this->payload[$priority]['time'] = time();
            $this->payload[$priority]['record'] = $this->extract($name); // set record array
        }
        asort($this->payload);
    }

}

// END LoggerTrait File
/* End of file LoggerTrait.php

/* Location: .Obullo/Log/LoggerTrait.php */