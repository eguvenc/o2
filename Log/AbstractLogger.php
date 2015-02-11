<?php

namespace Obullo\Log;

/**
 * Abstract Logger
 * 
 * @category  Log
 * @package   Debug
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Abstract Class AbstractLogger
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
     * Load defined log handler
     * 
     * @param string $name defined log handler name
     * 
     * @return object
     */
    abstract public function load($name);

    /**
     * Change channel
     * 
     * @param string $channel add a channel
     * 
     * @return object
     */
    abstract public function channel($channel);

    /**
     * Reserve your filter to valid log handler
     * 
     * @param string $name   filter name
     * @param array  $params data
     * 
     * @return object
     */
    abstract public function filter($name, $params = array());

    /**
     * Push to another handler
     * 
     * @return object
     */
    abstract public function push();
    
    /**
     * Add writer
     * 
     * @param string $name handler key
     * @param string $type writer/handler
     *
     * @return object
     */
    abstract public function addWriter($name, $type = 'writer');

    /**
     * Returns to primary writer name.
     * 
     * @return string returns to "handler" e.g. "file"
     */
    abstract public function getWriterName();

    /**
     * Returns to all writers
     * 
     * @return array
     */
    abstract public function getWriters();

    /**
     * Enable html debugger
     * 
     * @return void
     */
    abstract public function printDebugger();

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
    abstract public function log($level, $message, $context = array(), $priority = null);

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
     * Register filter alias
     * 
     * @param string $name      name of filter
     * @param string $namespace filename and path of filter
     *
     * @return object
     */
    public function registerFilter($name, $namespace)
    {
        $this->filterNames[$name] = ltrim($namespace, '\\');
        return $this;
    }

    /**
     * Register handler
     * 
     * @param string $priority global priority
     * @param string $name     handler name which is defined in constants
     * 
     * @return object
     */
    public function registerHandler($priority, $name)
    {
        $this->registeredHandlers[$name] = array('priority' => $priority);
        $this->track[] = array('type' => 'handlers', 'name' => $name);
        return $this;
    }
    
}

// END AbstractLogger class
/* End of file AbstractLogger.php */

/* Location: .Obullo/Log/AbstractLogger.php */