<?php

namespace Obullo\ServiceProviders\Connections;

use RuntimeException;
use Obullo\Container\Container;
use Obullo\Cache\Handler\HandlerInterface;

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
Class CacheConnectionProvider extends AbstractConnectionProvider
{
    protected $c; // Container

    /**
     * Constructor
     * 
     * @param string $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->setKey('cache.connection.');  // Set container key
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
     * Create a new mongo connection
     * 
     * if you don't want to add it to config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object mongo client
     */
    public function factory($params = array())
    {
        if (empty($params['driver']) OR empty($params['connection'])) {
            throw new RuntimeException(
                sprintf(
                    "Cache provider requires driver and connection parameters. <pre>%s</pre>",
                    "\$c['service provider cache']->get(['driver' => 'redis', 'connection' => 'default']);"
                )
            );
        }
        $cid = $this->getKey($this->getConnectionId($params));

        if ( ! $this->c->exists($cid)) {    //  create shared connection if not exists
            $this->c[$cid] = function () use ($params) {  //  create shared connections
                $driver = $params['driver'];
                unset($params['driver']);
                return $this->createClass($driver, $params);
            };
        }
        return $this->c[$cid];  // Get registered connection
    }

    /**
     * Creates cache connections
     * 
     * @param string $class   name
     * @param array  $options connection options
     * 
     * @return void
     */
    protected function createClass($class, $options)
    {
        $driver = strtolower($class);
        $Class = '\\Obullo\Cache\Handler\\'.ucfirst($driver);

        if ($driver == 'file' OR $driver == 'apc') {
            $connection = new $Class($this->c);
        } else {
            $connection = new $Class($this->c, $this->c['service provider '.$driver]->get($options));  //  Store objects to container
        }
        return $connection;
    }

}

// END CacheConnectionProvider.php class
/* End of file CacheConnectionProvider.php */

/* Location: .Obullo/ServiceProviders/Connections/CacheConnectionProvider.php */