<?php

namespace Obullo\Cache\Handler;

use Obullo\Cache\ArrayContainer,
    RunTimeException,
    ReflectionClass;
    
/**
 * Memcached Caching Class
 *
 * @category  Cache
 * @package   Memcached
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/cache
 */
Class Memcached implements HandlerInterface
{
    const SERIALIZER_NONE     = 'SERIALIZER_NONE';
    const SERIALIZER_PHP      = 'SERIALIZER_PHP';
    const SERIALIZER_JSON     = 'SERIALIZER_JSON';
    const SERIALIZER_IGBINARY = 'SERIALIZER_IGBINARY';
    const OPTION_SERIALIZER   = -1003;  // Memcached::OPT_COMPRESSION
    
    /**
     * Serializer types
     * 
     * @var array
     */
    public $serializerTypes = array(
        self::SERIALIZER_PHP      => 1, // Memcached::SERIALIZER_PHP
        self::SERIALIZER_IGBINARY => 2, // Memcached::SERIALIZER_IGBINARY
        self::SERIALIZER_JSON     => 3  // Memcached::SERIALIZER_JSON
    );

    /**
     * Memcache object
     * 
     * @var object
     */
    public $memcached;

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
     * @param array $c          container
     * @param array $serializer serializer type
     */
    public function __construct($c, $serializer = null)
    {
        $this->params = $c->load('config')['cache']['memcached'];
        $this->container = new ArrayContainer;

        if ( ! extension_loaded('memcached')) {
            throw new RunTimeException(
                sprintf(
                    ' %s driver is not installed.', get_class()
                )
            );
        }
        if ( ! $this->connect()) {
            throw new RunTimeException(
                sprintf(
                    ' %s cache connection failed.', get_class()
                )
            );
        }
        $this->serializer = empty($serializer) ? $this->params['serializer'] : $serializer;
    }

    /**
     * Connect to Memcached
     * 
     * @return boolean
     */
    public function connect()
    {
        $this->memcached = new \Memcached;

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
                if ( ! $this->memcached->addServer($servers['hostname'], $servers['port'], $servers['weight'])) {
                    return false;
                }
            } else {
                if ( ! $this->memcached->addServer($this->params['servers']['hostname'], $this->params['servers']['port'], $this->params['servers']['weight'])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Set client option.
     * 
     * @param string $serializer
     * 
     * You can use this options:
     *     'serializer_php'
     *     'serializer_igbinary'
     *     'serializer_json'
     * 
     * @return boolean true or false
     */
    public function setOption($serializer)
    {
        switch ($serializer) {
        case static::SERIALIZER_PHP: // The default PHP serializer.
            $this->serializer = $serializer;
            $this->memcached->setOption(static::OPTION_SERIALIZER, $this->serializerTypes[static::SERIALIZER_PHP]);
            return true;
            break;
        case static::SERIALIZER_JSON: // The JSON serializer.
            $this->serializer = $serializer;
            return $this->memcached->setOption(static::OPTION_SERIALIZER, $this->serializerTypes[static::SERIALIZER_JSON]);
            break;
        case static::SERIALIZER_IGBINARY: // The » igbinary serializer.
                                          // Instead of textual representation it stores PHP data structures in a compact binary form, resulting in space and time gains.
                                          // https://github.com/igbinary/igbinary
            $this->serializer = $serializer;
            return $this->memcached->setOption(static::OPTION_SERIALIZER, $this->serializerTypes[static::SERIALIZER_IGBINARY]);
            break;
        default:
            $this->serializer = $serializer;
            return false;
            break;
        }
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
     * Get client option.
     * http://www.php.net/manual/en/memcached.constants.php
     * 
     * @param string $option option constant
     * 
     * @return string value
     */
    public function getOption($option = 'OPTION_SERIALIZER')
    {
        $obj      = new ReflectionClass('Memcached');
        $constant = $obj->getconstant($option);
        return $this->memcached->getOption($constant);
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
        if ( ! is_array($key) AND strpos($key, ':') !== false) {
            $key = explode(':', $key);
        }
        if (is_array($key)) {
            return $this->memcached->getMulti($key);
        }
        if ($value = $this->container->get($key)) {
            return $value;
        }
        $value = $this->memcached->get($key);
        $this->container->set($key, $value); // Set to array container
        return $value;
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
        if (strpos($key, ':') !== false) {
            $value    = array();
            $explode  = explode(':', $key);
            $firstVal = array_pop($explode);
            $key      = $this->buildKeys($explode, $value, $data, $firstVal);
        }
        if (is_array($key)) {
            return $this->memcached->setMulti($key, time() + $ttl);
        }
        return $this->memcached->set($key, $data, time() + $ttl);
    }

    /**
     * Build keys
     * 
     * @param array   &$arr     array
     * @param array   &$value   value
     * @param boolean $data     data
     * @param boolean $firstVal first value
     * 
     * @return array
     */
    public function buildKeys(&$arr, &$value, $data = array(), $firstVal = false)
    {
        if (count($arr) == 0) {
            return;
        }
        $v = array();
        if ($firstVal) {
            $v[array_pop($arr)] = array($firstVal => $data);
        } else {
            $v[array_pop($arr)] = $value;
        }
        $value = $v;
        $this->buildKeys($arr, $value);
        return $value;
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
        if (is_array($key)) {
            return $this->memcached->deleteMulti($key);
        }
        return $this->memcached->delete($key);
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
     * Array
     * (
     *     [localhost:11211] => Array
     *     (
     *         [pid] => 4933
     *         [uptime] => 786123
     *         [threads] => 1
     *         [time] => 1233868010
     *         [pointer_size] => 32
     *         [rusage_user_seconds] => 0
     *         [rusage_user_microseconds] => 140000
     *         [rusage_system_seconds] => 23
     *     )
     * )
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