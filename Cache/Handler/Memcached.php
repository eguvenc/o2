<?php

namespace Obullo\Cache\Handler;

use ReflectionClass;
use RunTimeException;
use Obullo\Container\Container;
    
/**
 * Memcached Caching Class
 *
 * @category  Cache
 * @package   Memcached
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cache
 */
class Memcached implements CacheHandlerInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Memcached client
     * 
     * @var object
     */
    public $memcached;

    /**
     * Service provider parameters
     * 
     * @var array
     */
    protected $options = array();

    /**
     * Available serializers
     * 
     * @var array
     */
    public $serializers = array(
        0 => 'none',
        1 => 'php',         // Memcached::SERIALIZER_PHP
        2 => 'igbinary',    // Memcached::SERIALIZER_IGBINARY
        3 => 'json',        // Memcached::SERIALIZER_JSON
    );
    
    /**
     * Constructor
     * 
     * @param array $c       container
     * @param array $options options
     */
    public function __construct(Container $c, $options = array())
    {
        $this->c = $c;
        $this->options = $options;
        $this->params = $c['config']['cache']['memcached'];

        $this->connect();
    }

    /**
     * Connect to Memcached
     * 
     * @return boolean
     */
    public function connect()
    {
        $this->config = $this->c['config']['cache/memcached'];
        $this->memcached = $this->c['service provider memcached']->get($this->options);

        $this->openNodeConnections();
        return true;
    }

    /**
     * Connect to memcached nodes
     * 
     * @return void
     */
    protected function openNodeConnections()
    {
        if (empty($this->config['nodes'][0]['host']) OR empty($this->config['nodes'][0]['port'])) {  // If we have no slave servers
            return;
        }
        foreach ($this->config['nodes'] as $servers) {
            if ( empty($servers['host']) OR empty($servers['port'])) {
                throw new RunTimeException(
                    sprintf(
                        ' %s node configuration error, host or port can\'t be empty.',
                        get_class()
                    )
                );
            }
            $this->memcached->addServer($servers['host'], $servers['port'], $servers['weight']);
        }
    }

    /**
     * Get current serializer name
     * 
     * @return string serializer name
     */
    public function getSerializer()
    {
        $number = $this->getOption('OPT_SERIALIZER');

        return $this->serializers[$number];
    }

    /**
     * Get client option.
     * http://www.php.net/manual/en/memcached.constants.php
     * 
     * @param string $option option constant
     * 
     * @return string value
     */
    public function getOption($option = 'OPT_SERIALIZER')
    {
        $obj      = new ReflectionClass('Memcached');
        $constant = $obj->getconstant($option);
        return $this->memcached->getOption($constant);
    }

    /**
     * Get cache data.
     * 
     * @param string $key cache key
     * 
     * @return object
     */
    public function get($key)
    {
        return $this->memcached->get($key);
    }

    /**
     * Get multiple
     * 
     * @param array $data key => value
     * 
     * @return void
     */
    public function getMulti(array $data)
    {
        return $this->memcached->getMulti($data);
    }

    /**
     * Returns the keys that match a certain pattern.
     * 
     * @return array the keys that match a certain pattern.
     */
    public function getAllKeys()
    {
        return $this->memcached->getAllKeys();
    }

    /**
     * Get All Data
     * 
     * @return array return all the key and data
     */
    public function getAllData()
    {
        return $this->memcached->fetchAll();
    }

    /**
     * Verify if the specified key exists.
     * 
     * @param string $key cache key.
     * 
     * @return boolean true or false
     */
    public function keyExists($key)
    {
        if ($this->memcached->get($key)) {
            return true;
        }
        return false;
    }

    /**
     * Set Array
     * 
     * @param array $data cache data
     * @param int   $ttl  expiration time
     * 
     * @return void
     */
    public function setArray($data, $ttl = 60)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $this->memcached->set($k, $v, time() + $ttl);
            }
            return true;
        }
        return false;
    }

    /**
     * Save
     * 
     * @param mix $key  cache key.
     * @param mix $data cache data.
     * @param int $ttl  expiration time
     * 
     * @return boolean
     */
    public function set($key = '', $data = 60, $ttl = 60)
    {
        if ( ! is_array($key)) {
            return $this->memcached->set($key, $data, time() + $ttl);
        }
        return $this->setArray($key, $data);
    }

    /**
     * Save Multiple
     * 
     * @param mix $data cache data.
     * @param int $ttl  expiration time
     * 
     * @return boolean
     */
    public function setMulti($data, $ttl = 60)
    {
        return $this->memcached->setMulti($data, time() + $ttl);
    }   

    /**
     * Remove specified keys.
     * 
     * @param string $key cache key.
     * 
     * @return boolean
     */
    public function delete($key)
    {
        return $this->memcached->delete($key);
    }

    /**
     * Remove multiple
     * 
     * @param string $data cache data key => value
     * 
     * @return boolean
     */
    public function deleteMulti($data)
    {
        return $this->memcached->deleteMulti($data);
    }

    /**
     * Replace key value
     * 
     * @param mix $key  cache key.
     * @param mix $data cache data.
     * @param int $ttl  expiration time
     * 
     * @return boolean
     */
    public function replace($key = '', $data = 60, $ttl = 60)
    {
        if ( ! is_array($key)) {
            $this->memcached->replace($key, $data, time() + $ttl);
        }
        return $this->setArray($key, $data);
    }

    /**
     * Flush all items in 1 seconds (default)
     * 
     * @param int $expiration expiration time
     * 
     * @return boolean
     */
    public function flushAll($expiration = 1)
    {
        $this->memcached->flush($expiration);
        return $this->memcached->getResultCode();
    }

    /**
     * Get software information installed on your server.
     * 
     * @return object
     */
    public function info()
    {
        return $this->memcached->getStats();
    }

    /**
     * Get Meta Data
     * 
     * @param string $key cache key.
     * 
     * @return object
     */
    public function getMetaData($key)
    {
        $stored = $this->memcached->get($key);
        if (count($stored) !== 3) {
            return false;
        }
        list($data, $time, $ttl) = $stored;
        return array(
            'expire' => $time + $ttl,
            'mtime'  => $time,
            'data'   => $data
        );
    }

    /**
     * Close the connection
     * 
     * @return void
     */
    public function close()
    {
        return;
    }

}

// END Memcached Class

/* End of file Memcached.php */
/* Location: .Obullo/Cache/Handler/Memcached.php */