<?php

namespace Obullo\Log;

use Closure, 
    LogicException, 
    ErrorException,
    RuntimeException,
    Obullo\Error\ErrorHandler;

/**
 * Logger Class
 *
 * Modeled after Zend Log package.
 * 
 * http://www.php.net/manual/en/class.splpriorityqueue.php
 * 
 * @category  Log
 * @package   Logger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class Logger
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
     * Priority values
     * 
     * @var array
     */
    public static $priorityValues = array();

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
     * Date format
     * 
     * @var string
     */
    public $format = 'Y-m-d H:i:s';

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
     * Defined handlers in the container
     * 
     * @var array
     */
    protected $handlers = array();

    /**
     * Log priority queue objects
     * 
     * @var array
     */
    protected $priorityQueue = array();

    /**
     * Push data
     * 
     * @var array
     */
    public $push = array();

    /**
     * Track data for handlers and writers
     * 
     * @var array
     */
    public $track = array();

    /**
     * Namespaces of defined filters
     * 
     * @var array
     */
    public $filterNames = array();

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
     * @param array  $params configuration
     */
    public function __construct($c, $params = array())
    {
        $this->c = $c;
        $this->config = $params;
        $this->enabled = $this->config['enabled'];
        $this->debug = $this->config['output'];
        $this->channel = $this->config['channel'];
        $this->queries = $this->config['queries'];
        $this->benchmark = $this->config['benchmark'];
        $this->format = $this->config['format'];

        $errorDebug = $c->load('config')['error']['debug'];
        $errorReporting = $c->load('config')['error']['reporting'];

        if ($errorDebug == false) {  // If debug "disabled" from config use logger class handlers and send all errors to log.
            static::registerExceptionHandler($this);          // If debug "enabled" from config use debug class handlers also send all errors to log.
            static::registerErrorHandler($this);
            static::registerFatalErrorHandler($this);
        }
        if ($errorReporting == true AND $errorDebug == false) { // If "Php Native Error Reporting" "enabled" from config restore handlers and use native errors.
            static::unregisterErrorHandler();                   // Also write errors to log file. Especially designed for "local" environment.
            static::unregisterExceptionHandler();
        }
        register_shutdown_function(array($this, 'close'));

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
        if ( ! isset($this->handlers[$name])) {
            throw new LogicException(
                sprintf(
                    'The push handler %s is not defined in your log service.', 
                    $name
                )
            );
        }
        $val = $this->handlers[$name];
        $this->addWriter($name, $val['handler'], $val['priority']);
        return $this;
    }

    /**
     * Add Handler
     * 
     * @param string $name    handler name
     * @param object $handler closure object
     *
     * @return object
     */
    public function addHandler($name, Closure $handler)
    {
        $this->handlers[$name] = array('handler' => $handler);
        $this->track[] = array('type' => 'handlers', 'name' => $name);
        return $this;
    }

    /**
     * Remove Handler
     * 
     * @param string $name handler name
     * 
     * @return object
     */
    public function removeHandler($name)
    {
        unset($this->handlers[$name]);
        return $this;
    }

    /**
     * Add writer
     * 
     * @param string $name    handler key
     * @param object $handler handler object
     *
     * @return object
     */
    public function addWriter($name, Closure $handler)
    {
        $this->priorityQueue[$name] = new PriorityQueue;    // add processor
        $this->writers[$name] = array('handler' => $handler(), 'priority' => 1);
        $this->track[] = array('type' => 'writers', 'name' => $name);
        return $this;
    }

    /**
     * Remove Writer
     * removers handler from processors and writers
     * 
     * @param string $name handler name
     * 
     * @return object
     */
    public function removeWriter($name)
    {
        unset($this->priorityQueue[$name]);
        unset($this->writers[$name]);
        return $this;
    }

    /**
     * Returns to writer name of current handler.
     * 
     * @param integer $n order of handler
     * 
     * @return string returns to "xWriter"
     */
    public function getHandlerWriterName($n = 0)
    {
        $writer = $this->getHandlerWriter($n);
        $className = get_class($writer);
        $exp = explode('\\', $className);
        return end($exp);
    }

    /**
     * Returns to first object of writer.
     *
     * @param integer $n order of handler
     * 
     * @return object
     */
    public function getHandlerWriter($n = 0)
    {
        $writers = array_values($this->getWriters());
        if (empty($writers)) {
            throw new RuntimeException('At least one handler must be defined in your Logger Service.');
        }
        return $writers[$n]['handler']->writer;
    }

    /**
     * Returns to primary writer name.
     * 
     * @return string returns to "xWriter"
     */
    public function getWriterName()
    {
        $writers = $this->getWriters();
        return ucfirst(array_keys($writers)[0]).'Writer';
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
     * Set priority value for current handler 
     * or writer.
     * 
     * @param integer $priority level
     * 
     * @return object
     */
    public function priority($priority = 0)
    {
        $end = end($this->track);
        $type = $end['type'];
        $name = $end['name'];
        $this->{$type}[$name]['priority'] = $priority;
        return $this;
    }

    /**
     * Register filter alias
     * 
     * @param string $name      name of filter
     * @param string $namespace class path of filter
     *
     * @return object
     */
    public function registerFilter($name, $namespace)
    {
        $this->filterNames[$name] = $namespace;
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
    public function filter($name, array $params = array())
    {
        $method = 'filter';
        if (strpos($name, '.') > 0) {
            list($name, $method) = explode('.', $name);
        }
        if ( ! isset($this->filterNames[$name])) {
            throw new LogicException(
                sprintf(
                    'The filter %s is not defined in your logger service.', 
                    $name
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
     * Get property value from logger
     * 
     * @param string $key property of logger
     * 
     * @return mixed
     */
    public function getProperty($key)
    {
        return $this->{$key};
    }

    /**
     * Set property value to logger
     * 
     * @param string $key property to logger
     * @param mixed  $val value of property
     * 
     * @return void
     */
    public function setProperty($key, $val)
    {
        $this->{$key} = $val;
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
     * Emergency
     * 
     * @param string  $message  log message
     * @param array   $context  data
     * @param integer $priority message priority
     * 
     * @return void
     */
    public function emergency($message = '', $context = array(), $priority = null) 
    {
        $this->log(__FUNCTION__, $message, $context, $priority);
    }

    /**
     * Alert
     * 
     * @param string  $message  log message
     * @param array   $context  data
     * @param integer $priority message priority
     * 
     * @return void
     */
    public function alert($message = '', $context = array(), $priority = null)
    {
        $this->log(__FUNCTION__, $message, $context, $priority);
    }

    /**
     * Critical
     * 
     * @param string  $message  log message
     * @param array   $context  data
     * @param integer $priority message priority
     * 
     * @return void
     */
    public function critical($message = '', $context = array(), $priority = null) 
    {
        $this->log(__FUNCTION__, $message, $context, $priority);
    }

    /**
     * Error
     * 
     * @param string  $message  log message
     * @param array   $context  data
     * @param integer $priority message priority
     * 
     * @return void
     */
    public function error($message = '', $context = array(), $priority = null) 
    {
        $this->log(__FUNCTION__, $message, $context, $priority);
    }
    
    /**
     * Warning
     * 
     * @param string  $message  log message
     * @param array   $context  data
     * @param integer $priority message priority
     * 
     * @return void
     */
    public function warning($message = '', $context = array(), $priority = null) 
    {
        $this->log(__FUNCTION__, $message, $context, $priority);
    }
    
    /**
     * Notice
     * 
     * @param string  $message  log message
     * @param array   $context  data
     * @param integer $priority message priority
     * 
     * @return void
     */
    public function notice($message = '', $context = array(), $priority = null) 
    {
        $this->log(__FUNCTION__, $message, $context, $priority);
    }
    
    /**
     * Info
     * 
     * @param string  $message  log message
     * @param array   $context  data
     * @param integer $priority message priority
     * 
     * @return void
     */
    public function info($message = '', $context = array(), $priority = null) 
    {
        $this->log(__FUNCTION__, $message, $context, $priority);
    }

    /**
     * Debug
     * 
     * @param string  $message  log message
     * @param array   $context  data
     * @param integer $priority message priority
     * 
     * @return void
     */
    public function debug($message = '', $context = array(), $priority = null) 
    {
        $this->log(__FUNCTION__, $message, $context, $priority);
    }

    /**
     * Push to another handler
     * 
     * @param string $handler set log handler
     * 
     * @return void
     */
    public function push($handler)
    {
        if ( ! isset($this->handlers[$handler])) {
            throw new LogicException(
                sprintf(
                    'The push handler %s is not defined in your logger service file.', 
                    $handler
                )
            );
        }
        if ( ! isset($this->writers[$handler])) {
            throw new LogicException(
                sprintf(
                    'The push handler %s not available in log writers please first load it using below the command.
                    <pre>$this->logger->load(LOGGER_HANDLERNAME);</pre>', 
                    $handler
                )
            );
        }
        $this->push[$handler] = 1; // Allow push data to valid push handler.
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
        $recordUnformatted = array();
        if (isset(static::$priorities[$level])) { // is Allowed level ?
            $recordUnformatted['channel'] = $this->channel;
            $recordUnformatted['level']   = $level;
            $recordUnformatted['message'] = $message;
            $recordUnformatted['context'] = $context;
            $this->sendToQueue($recordUnformatted, $priority); // Send to Job queue
            $this->channel($this->config['channel']);          // reset channel to default
        }
        return $this;
    }
    
    /**
     * Get splPriority object of valid handler
     * 
     * @param string $handler name
     * 
     * @return object of handler
     */
    public function getQueue($handler = LOGGER_FILE)
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
        $data = $recordUnformatted;
        foreach ($this->filters[$handler] as $value) {
            $Class  = '\\'.$value['class'];
            $method = $value['method'];
            $filter = new $Class($this->c, $value['params']);
            $data = $filter->$method($recordUnformatted);
        }
        return $data;
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
                $formatted = $val['handler']->format($this->format, $filteredRecords);
                $priority = (empty($messagePriority)) ? $val['priority'] : $messagePriority;
                $this->priorityQueue[$name]->insert($formatted, $priority);
            }
        }
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
                        'level'   => $level,
                        'file'    => $file,
                        'line'    => $line,
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
     * End of the logs and beginning of the handlers.
     *
     * @return void
     */
    public function close()
    {
        if ($this->debug) {             // Debug output for log data if enabled
            $primaryWriter = strtolower(substr($this->getWriterName(), 0, -6));
            $debug = new Debug($this->c, $this, $primaryWriter);
            echo $debug->printDebugger($this->getQueue($primaryWriter));
            return;
        }
        if ($this->enabled == false) {  // Check logger is disabled.
            return;
        }
        foreach ($this->writers as $name => $val) {  // Write log data foreach handlers
            if ( ! isset($this->push[$name]) AND isset($this->handlers[$name])) {     // If handler available in push data.
                return;
            }
            $val['handler']->write($this->getQueue($name));
            $val['handler']->close();
        }
        $this->push = array(); // Reset push data
    }
}

// END Logger class

/* End of file Logger.php */
/* Location: .Obullo/Log/Logger.php */