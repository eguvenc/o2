<?php

namespace Obullo\Service\Providers;

use RuntimeException;
use Obullo\Container\ContainerInterface;
use Obullo\Cache\Handler\HandlerInterface;
use Obullo\Service\ServiceProviderInterface;

/**
 * Cache Service Provider
 * 
 * @category  Connections
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class CacheServiceProvider extends AbstractConnectionProvider implements ServiceProviderInterface
{
    protected $c; // Container

    /**
     * Constructor
     * 
     * @param string $c container
     */
    public function __construct(ContainerInterface $c)
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
    public function get($params = array())
    {
        $connection = $this->factory($params);
        return $connection;
    }

    /**
     * Create a new cache connection
     * 
     * If you don't want to add it to config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object mongo client
     */
    public function factory($params = array())
    {
        if (empty($params['driver']) || empty($params['connection'])) {
            throw new RuntimeException(
                sprintf(
                    "Cache provider requires driver and connection parameters. <pre>%s</pre>",
                    "\$c['app']->provider('cache')->get(['driver' => 'redis', 'connection' => 'default']);"
                )
            );
        }
        $cid = $this->getKey($this->getConnectionId($params));

        // Create shared connections if not exists

        if (! $this->c->has($cid)) {   
            $this->c[$cid] = function () use ($params) { 
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

        if ($driver == 'file' || $driver == 'apc') {
            $connection = new $Class($this->c['config']);
        } else {
            $connection = new $Class(
                $this->c['config'],
                $this->c['app']->provider($driver)->get($options)
            );  //  Store objects to container
        }
        return $connection;
    }

}