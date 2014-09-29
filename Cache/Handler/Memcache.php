<?php

namespace Obullo\Cache\Handler;

use Obullo\Cache\ArrayContainer,
    RunTimeException;

/**
 * Memcache Caching Class
 *
 * @category  Cache
 * @package   Memcache
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/cache
 */
Class Memcache implements HandlerInterface
{
    const SERIALIZER_NONE = 'SERIALIZER_NONE';

    /**
     * Memcache object
     * 
     * @var object
     */
    public $memcache;

    /**
     * Connection settings
     * 
     * @var array
     */
    public $params = array();

    /**
     * Current serializer name
     * 
     * @var string
     */
    public $serializer;

    /**
     * Array container
     * 
     * @var object
     */
    protected $container;

    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params connection parameters
     */
    public function __construct($c, $params = array())
    {
        $c = null;
        $this->container = new ArrayContainer;
        
        if ( ! extension_loaded('memcache')) {
            throw new RunTimeException(
                sprintf(
                    ' %s driver is not installed.', get_class()
                )
            );
        }
        $this->params = $params;

        if ( ! $this->connect()) {
            throw new RunTimeException(
                sprintf(
                    ' %s cache connection failed.', get_class()
                )
            );
        }
    }

    /**
     * Set options fake function
     * 
     * @param array $params config
     *
     * @return void
     */
    public function setOption($params = array()) 
    {
        $params = null;
        $this->serializer = static::SERIALIZER_NONE;
    }

    /**
     * Get current serializer name
     * 
     * @return string serializer name
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Connect to Memcache
     * 
     * @return boolean
     */
    public function connect()
    {
        $this->memcache = new \Memcache;
        foreach ($this->params['servers'] as $servers) {
            if ( ! isset($servers['hostname']) AND ! isset($servers['port'])) {
                throw new RunTimeException(
                    sprintf(
                        ' %s connection configuration items hostname or port can\'t be empty.', get_class()
                    )
                );
            }
            if ( ! isset($servers['weight'])) {
                $servers['weight'] = 1;
            }
            if (is_array($servers)) {
                if ( ! $this->memcache->addServer($servers['hostname'], $servers['port'], $servers['weight'])) {
                    return false;
                }
            } else {
                if ( ! $this->memcache->addServer($this->params['servers']['hostname'], $this->params['servers']['port'], $this->params['servers']['weight'])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Get cache data.
     * 
     * @param string $key cache key.
     * 
     * @return object
     */
    public function get($key)
    {
        if ($value = $this->container->get($key)) {
            return $value;
        }
        $value = $this->memcache->get($key);
        if (is_array($value) AND isset($value[0])) {
            $value = $value[0];
        }
        $this->container->set($key, $value); // Set to array container
        return $value;
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
        if ($this->memcache->get($key)) {
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
                $this->memcache->set($k, $v, 0, $ttl);
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
            return $this->memcache->set($key, array($data, time(), $ttl), 0, $ttl);
        }
        return $this->setArray($key, $data);
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
        return $this->memcache->delete($key);
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
            $this->memcache->replace($key, array($data, time(), $ttl), 0, $ttl);
        }
        return $this->setArray($key, $data);
    }

    /**
     * Remove all keys and data from the cache.
     * 
     * @return boolean
     */
    public function flushAll()
    {
        return $this->memcache->flush();
    }

    /**
     * Get software information installed on your server.
     * 
     * @return object
     */
    public function info()
    {
        return $this->memcache->getStats();
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
        $stored = $this->memcache->get($key);
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
        $this->memcache->close();
        return;
    }

}

// END Memcache Class

/* End of file Memcache.php */
/* Location: .Obullo/Cache/Handler/Memcache.php */