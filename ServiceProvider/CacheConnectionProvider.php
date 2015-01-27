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
    protected $c;                          // Container
    protected static $instance = null;     // Presence of a static member variable
    protected static $connected = array(); // Multiton connections

    /**
     * Returns the singleton instance of this class.
     *
     * @param object $c Container
     * 
     * @return singleton instance.
     */
    public static function getInstance($c)
    {
        if (null === self::$instance) {
            self::$instance = new static($c);
        }
        return self::$instance;
    }

    /**
     * Constructor
     * 
     * @param string $c container
     */
    protected function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Creates cache connections ( Works one time foreach drivers )
     * 
     * @param object $connection driver object
     * @param string $name       driver name   ( redis, memcached )
     * 
     * @return void
     */
    public static function connect(HandlerInterface $connection, $name)
    {  
        if ( ! self::isConnected($name)) {  // Just one time run connect method

            if ( ! $connection->connect()) {
                throw new RuntimeException(
                    sprintf(
                        ' %s cache connection failed.', $name
                    )
                );
            }
            self::$connected[$name] = true;
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

    /**
     * Get shared driver object
     * 
     * @param array $params parameters
     * 
     * @return object
     */
    public function getConnection($params)
    {
        $connection = $this->factory($params);
        self::connect($connection, $params['driver']); // We just one time open the connection for each drivers.

        return $connection;
    }

    /**
     * Create a new mongo connection if you don't want to add config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object mongo client
     */
    public function factory($params = array())
    {
        if ( ! isset($params['driver'])) {
            throw new UnexpectedValueException("Cache connection provider requires driver parameter.");
        }
        $cid = 'cache.connection.'.self::getConnectionId($params);

        if ( ! $this->c->exists($cid)) { //  create shared connection if not exists
            $self = $this;
            $this->c[$cid] = function () use ($self, $params) {  //  create shared connections
                return $self->createConnection($params['driver'], $params);
            };
        }
        return $this->c[$cid];
    }

    /**
     * Creates cache connections
     * 
     * @param string $class  name
     * @param array  $params connection parameters
     * 
     * @return void
     */
    protected function createConnection($class, $params)
    {
        $options = isset($params['options']) ? $params['options'] : array('serializer' => $this->c['config']['cache']['default']['serializer']);
        $driver = '\Obullo\Cache\Handler\\'.ucfirst($class);
        $connection = new $driver($this->c);  //  Store objects to container
        $connection->setParameters($options);
        return $connection;
    }

    /**
     * Returns to connection id
     * 
     * @param string $string serialized parameters
     * 
     * @return integer
     */
    protected static function getConnectionId($string)
    {
        return sprintf("%u", crc32(serialize($string)));
    }

}

// END CacheConnectionProvider.php class
/* End of file CacheConnectionProvider.php */

/* Location: .Obullo/ServiceProvider/CacheConnectionProvider.php */