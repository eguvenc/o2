<?php

namespace Obullo\Log;

use Obullo\Container\Container;
use Obullo\Log\Queue\QueueLogger;

/**
 * LoggerServiceProviderProvider Class
 * 
 * @category  Log
 * @package   Debug
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Class LoggerServiceProvider
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Returns to logger instance
     * 
     * @return object
     */
    public function getLogger()
    {
        if ($this->disabled()) {
            return new NullLogger;  // Use null handler if config disabled.
        }
        return new Logger($this->c);
    }

    /**
     * Returns to queue logger instance
     * 
     * @return object
     */
    public function getQueueLogger()
    {
        if ($this->disabled()) {
            return new NullLogger;  // Use null handler if config disabled.
        }
        return new QueueLogger($this->c, $this->c['return queue']);
    }

    /**
     * Returns to true if logger disabled from config
     * 
     * @return boolean
     */
    public function disabled()
    {
        return ($this->c['config']['log']['control']['enabled']) ? false : true;
    }
}

// END LoggerServiceProvider class
/* End of file LoggerServiceProvider.php */

/* Location: .Obullo/Log/LoggerServiceProvider.php */