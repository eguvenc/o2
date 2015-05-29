<?php

namespace Obullo\Service\Providers\Connections;

use Memcache;
use RuntimeException;
use UnexpectedValueException;
use Obullo\Container\Container;

/**
 * Memcache Connection Provider
 * 
 * @category  Connections
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class MemcacheConnectionProvider extends AbstractConnectionProvider
{
    protected $c;          // Container
    protected $config;     // Database configuration items
    protected $memcache;   // Memcache extension

    /**
     * Constructor
     * 
     * Automatically check if the Memcache extension has been installed.
     * 
     * @param string $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('cache/memcache');  // Load memcache configuration file

        $this->setKey('memcache.connection.');

        if ( ! extension_loaded('memcache')) {
            throw new RuntimeException(
                'The memcache extension has not been installed or enabled.'
            );
        }
    }

    /**
     * Register all connections as shared services ( It should be run one time )
     * 
     * @return void
     */
    public function register()
    {
        foreach ($this->config['connections'] as $key => $val) {
            $this->c[$this->getKey($key)] = function () use ($val) {  // create shared connections
                if (empty($val['host']) OR empty($val['port'])) {
                    throw new RuntimeException(
                        'Check your memcache configuration, "host" or "port" key seems empty.'
                    );
                }
                return $this->createConnection($val);
            };
        }
    }

    /**
     * Creates Memcache connections
     * 
     * @param array $value current connection array
     * 
     * @return object
     */
    protected function createConnection(array $value)
    {
        $this->memcache = new Memcache;

        // http://php.net/manual/tr/memcache.connect.php
        // If you have pool of memcache servers, do not use the connect() function. 
        // If you have only single memcache server then there is no need to use the addServer() function.

        // Check single server connection

        if (empty($this->config['nodes'][0]['host'])) {  // If we haven't got any nodes use connect() method

            $connect = true;
            if ($value['options']['persistent']) {
                $connect = $this->memcache->pconnect($value['host'], $value['port'], $value['options']['timeout']);
            } else {
                $connect = $this->memcache->connect($value['host'], $value['port'], $value['options']['timeout']);
            }
            if ( ! $connect) {
                throw new RuntimeException(
                    sprintf(
                        "Memcache connection error could not connect to host: %s.",
                        $value['host']
                    )
                );
            }
        }
        return $this->memcache;
    }

    /**
     * Retrieve shared Memcache connection instance from connection pool
     *
     * @param array $params provider parameters
     * 
     * @return object Memcache
     */
    public function getConnection($params = array())
    {
        if (empty($params['connection'])) {
            throw new RuntimeException(
                sprintf(
                    "Memcache provider requires connection parameter. <pre>%s</pre>",
                    "\$c['app']->provider('memcache')->get(['connection' => 'default']);"
                )
            );
        }
        if ( ! isset($this->config['connections'][$params['connection']])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Connection key %s does not exist in your memcache configuration.',
                    $params['connection']
                )
            );
        }
        return $this->c[$this->getKey($params['connection'])];  // return to shared connection
    }

    /**
     * Create a new Memcache connection
     * 
     * if you don't want to add it to config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object Memcache client
     */
    public function factory($params = array())
    {
        $cid = $this->getKey($this->getConnectionId($params));

        if ( ! $this->c->has($cid)) {    //  create shared connection if not exists
            $this->c[$cid] = function () use ($params) {  //  create shared connections
                return $this->createConnection($params);
            };
        }
        return $this->c[$cid];
    }

    /**
     * Close all connections
     */
    public function __destruct()
    {
        foreach (array_keys($this->config['connections']) as $key) {
            $key = $this->getKey($key);
            if ($this->c->loaded($key)) {   // Close any open connections
                $this->c[$key]->close();    // http://php.net/manual/tr/memcache.close.php
            }
        }
    }
}

// END MemcacheConnectionProvider.php class
/* End of file MemcacheConnectionProvider.php */

/* Location: .Obullo/Service/Providers/Connections/MemcacheConnectionProvider.php */