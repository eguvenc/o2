<?php

namespace Obullo\ServiceProvider;

use RuntimeException,
    UnexpectedValueException,
    Obullo\Container\Container,
    Obullo\Cache\Handler\HandlerInterface;

/**
 * Cache Connection Provider
 * 
 * @category  Cache
 * @package   Connector
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/provider
 */
Class CacheConnectionProvider
{
    protected $c;       // Container
    protected $config;  // Configuration items
    protected static $instance = null;     // Presence of a static member variable
    protected static $connected = array(); // Multiton connection instances

    /**
     * Returns the singleton instance of this class.
     * 
     * @return singleton instance.
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Register driver
     * 
     * @param array $params parameters
     * 
     * @return void
     */
    public function register($params)
    {
        $key = strtolower($params['driver']);
        $driver = ucfirst($key);

        $this->c['cache.driver'] = 
    }

    /**
     * Creates cache connections
     * 
     * @param object $driver driver object
     * @param string $class  driver name
     * 
     * @return void
     */
    public function connect(HandlerInterface $driver, $class)
    {  
        if ( ! self::isConnected($class)) {

            if ( ! $driver->connect()) {
                throw new RunTimeException(
                    sprintf(
                        ' %s cache connection failed.', $class
                    )
                );
            }
            self::$connected[$class] = true;
        }
    }

    /**
     * Check is connected
     * 
     * @param string $name driver
     * 
     * @return boolean
     */
    protected static function isConnected($name)
    {
        if (isset(self::$connected[$name])) {
            return true;
        }
        return false;
    }

    public function getConnection()
    {

    }

}

// END CacheConnectionProvider.php class
/* End of file CacheConnectionProvider.php */

/* Location: .Obullo/ServiceProvider/CacheConnectionProvider.php */