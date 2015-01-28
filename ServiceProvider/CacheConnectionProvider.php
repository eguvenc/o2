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
    protected $config = array();           // Cache config
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
        $this->config = $this->c['config']->load('cache');
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
        $handler = strtolower($class);

        if ( ! isset($this->config['handlers'][$handler])) {
            throw new RuntimeException(
                sprintf(
                    'Undefined handler %s in your cache.php config file.', $params['driver']
                )
            );
        }
        $connection = new $this->config['handlers'][$handler]($this->c, $options);  //  Store objects to container
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