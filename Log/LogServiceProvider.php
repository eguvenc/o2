<?php

namespace Obullo\Log;

use Obullo\Container\Container;

/**
 * LogServiceProviderProvider Class
 * 
 * @category  Log
 * @package   Debug
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Class LogServiceProvider
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Logger class
     * 
     * @var object
     */
    protected $logger;

    /**
     * Config parameters
     * 
     * @var object
     */
    protected $config;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        if ($this->disabled()) {
            $this->logger = new NullLogger;  // Use null handler if config disabled.
            return;
        }
        $this->logger = new Logger($this->c, $this->c['return queue'], $this->config);
    }

    /**
     * Returns to logger instance
     * 
     * @return object
     */
    public function getLogger()
    {
        return $this->logger;
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

// END LogServiceProvider class
/* End of file LogServiceProvider.php */

/* Location: .Obullo/Log/LogServiceProvider.php */