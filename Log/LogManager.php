<?php

namespace Obullo\Log;

use Obullo\Container\ContainerInterface;
use Obullo\Service\ServiceProviderInterface;

/**
 * LogManager Class
 * 
 * @category  Provider
 * @package   LogManager
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class LogManager
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Logger instance
     * 
     * @var object
     */
    protected $logger;

    /**
     * Create classes
     * 
     * @param object $c container
     * 
     * @return object
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Returns to logger instance
     *
     * @param array $params provider parameters
     * 
     * @return void
     */
    public function setConfiguration($params = array())
    {
        if (! $this->c['config']['log']['enabled']) {
            $this->logger = new NullLogger;  // Use null handler if config disabled.
            return;
        }
        $this->logger = new Logger($this->c, $params);
        return;
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

}

// END LogManager class
/* End of file LogManager.php */

/* Location: .Obullo/Log/LogManager.php */