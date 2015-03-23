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
class LoggerServiceProvider
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
     * @param array $params driver ( Logger or QueueLogger )
     * 
     * @return object
     */
    public function get($params = array('driver' => 'Logger'))
    {
        if ($this->disabled()) {
            return new NullLogger;  // Use null handler if config disabled.
        }
        if ($params['driver'] == 'Logger') return new Logger($this->c);
        if ($params['driver'] == 'QueueLogger') return new QueueLogger($this->c);
    }

    /**
     * Returns to true if logger disabled from config
     * 
     * @return boolean
     */
    public function disabled()
    {
        return ($this->c['config']['log']['enabled']) ? false : true;
    }
}

// END LoggerServiceProvider class
/* End of file LoggerServiceProvider.php */

/* Location: .Obullo/Log/LoggerServiceProvider.php */