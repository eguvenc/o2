<?php

namespace Obullo\ServiceProviders;

use RuntimeException,
    UnexpectedValueException,
    Obullo\Container\Container,
    Obullo\Utils\SingletonTrait,
    Obullo\Cache\Handler\HandlerInterface;

/**
 * Cache Connection Provider
 * 
 * @category  ConnectionProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/provider
 */
Class CacheConnectionProvider
{
    protected $c;                // Container
    protected $config = array(); // Cache config

    use SingletonTrait, ConnectionTrait;

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
        $cid = 'cache.connection.'.static::getConnectionId($params);

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
        $options = isset($params['serializer']) ? array('serializer' => $params['serializer']) : array('serializer' => $this->config['default']['serializer']);
        $driver = ucfirst(strtolower($class));

        $Class = '\\Obullo\Cache\Handler\\'.$driver;
        $connection = new $Class($this->c, $options);  //  Store objects to container
        return $connection;
    }

}

// END CacheConnectionProvider.php class
/* End of file CacheConnectionProvider.php */

/* Location: .Obullo/ServiceProviders/CacheConnectionProvider.php */