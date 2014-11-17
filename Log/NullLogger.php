<?php

namespace Obullo\Log;

/**
 * Disable Logger Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class NullLogger extends AbstractLogger
{
    /**
     * Load defined log handler
     * 
     * @param string $name defined log handler name
     * 
     * @return object
     */
    public function load($name)
    {
        $name = null;
        return $this;
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
        $priority = null;
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
        $channel = null;
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
        $name = null;
        $params = array();
        return $this;
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
        $handler = null;
        return $this;
    }

    /**
     * If logger disabled all logger methods returns to null.
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
        return $level = $message = $context = $priority = null;
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
        $name = null;
        return $this;
    }

    /**
     * Returns to primary writer name.
     * 
     * @return string returns to "handler" e.g. "file"
     */
    public function getWriterName()
    {
        return 'null';
    }

    /**
     * Returns to all writers
     * 
     * @return array
     */
    public function getWriters()
    {
        return array('null');
    }

    /**
     * Enable html debugger
     * 
     * @return void
     */
    public function printDebugger()
    {
        return;
    }

}

// END NullLogger

/* End of file NullLogger.php */
/* Location: .Obullo/Log/NullLogger.php */