<?php

namespace Obullo\Log;

/**
 * LogService Class
 * 
 * @category  Log
 * @package   Debug
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class LogService
{
    /**
     * Container class
     * 
     * @var object
     */
    public $c;

    /**
     * Config parameters
     * 
     * @var object
     */
    public $params;

    /**
     * Logger class
     * 
     * @var object
     */
    public $logger;

    /**
     * Constructor
     *
     * @param object $c      container
     * @param array  $params configuration
     */
    public function __construct($c, $params = array())
    {
        $this->c = $c;
        $this->params = $params;

        if ($this->disabled()) {
            $this->logger = new NullLogger;  // Use null handler if config disabled.
            return;
        }
        $this->logger = new Logger($this->c, $this->c->load('service/queue'), $this->params);
        $this->logger->registerFilterPath($params['log']['service']['filters']);
    }

    /**
     * Returns to true if logger disabled from config
     * 
     * @return boolean
     */
    public function disabled()
    {
        return ($this->params['log']['control']['enabled']) ? false : true;
    }
}

// END LogService class
/* End of file LogService.php */

/* Location: .Obullo/Log/LogService.php */