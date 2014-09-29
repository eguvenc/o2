<?php

namespace Obullo\Cache\Handler;

use Obullo\Cache\ArrayContainer,
    RunTimeException;

/**
 * Apc Caching Class
 *
 * @category  Cache
 * @package   Apc
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/cache
 */
Class Apc implements HandlerInterface
{
    const SERIALIZER_NONE = 'SERIALIZER_NONE';

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
        $params = null;
        $this->container = new ArrayContainer;
        
        if ( ! extension_loaded('apc') OR ini_get('apc.enabled') != '1') {
            throw new RunTimeException(
                sprintf(
                    ' %s driver is not installed.', get_class()
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
     * Get cache data.
     * 
     * @param string $key cache key.
     * 
     * @return array
     */
    public function get($key)
    {
        if ($value = $this->container->get($key)) {
            return $value;
        }
        $value = apc_fetch($key);
        if (is_array($value) AND isset($value[0])) {
            $value = $value[0];
            $this->container->set($key, $value); // Set to array container
            return $value;
        }
        return false;
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
        return apc_exists($key);
    }

    /**
     * Set array
     * 
     * @param array $data cache data.
     * @param int   $ttl  expiration time.
     * 
     * @return boolean
     */
    public function setArray($data, $ttl)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $this->set($k, $v, $ttl);
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
     * @return array
     */
    public function set($key = '', $data = 60, $ttl = 60) 
    {
        if ( ! is_array($key)) {
            return apc_store($key, array($data, time(), $ttl), $ttl);
        }
        return $this->setArray($key, $data);
    }

    /**
     * Remove specified key.
     * 
     * @param string $key cache key.
     * 
     * @return boolean
     */
    public function delete($key)
    {
        return apc_delete($key);
    }

    /**
     * Replace data
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
            $this->delete($key);
            return apc_store($key, array($data, time(), $ttl), $ttl);
        }
        return $this->setArray($key, $data);
    }

    /**
     * Clean all data
     * 
     * @param string $type clean type
     * 
     * @return object
     */
    public function flushAll($type = 'user')
    {
        return apc_clear_cache($type);
    }

    /**
     * Cache Info
     * 
     * @param string $type info type
     * 
     * Types:
     *     "user"
     *     "filehits"
     * 
     * @return array
     */
    public function info($type = null)
    {
        return apc_cache_info($type);
    }

    /**
     * Get meta data
     * 
     * @param string $key cache key.
     * 
     * @return array
     */
    public function getMetaData($key)
    {
        $stored = apc_fetch($key);
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
     * Connect
     * 
     * @return void
     */
    public function connect()
    {
        return;
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

// END Apc Class

/* End of file Apc.php */
/* Location: .Obullo/Cache/Handler/Apc.php */