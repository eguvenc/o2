<?php

namespace Obullo\Cache\Handler;

use Obullo\Container\Container;

/**
 * Cache Handler Interface
 * 
 * @category  Cache
 * @package   Log
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cache
 */
interface HandlerInterface
{
    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params connection parameters
     */
    public function __construct(Container $c, $params = array());

    /**
     * Sets serializer options 
     * 
     * @param array $params options
     * 
     * @return boolean true or false
     */
    public function setOption($params);

    /**
     * Get current serializer name
     * 
     * @return string serializer name
     */
    public function getSerializer();

    /**
     * Connection the cache..
     * 
     * @return boolean
     */
    public function connect();

    /**
     * Set cache data.
     *
     * @param mix $key  cache key
     * @param mix $data cache data
     * @param int $ttl  expiration time
     * 
     * @return boolean
     */
    public function set($key = '', $data = 60, $ttl = 60);

    /**
     * Get cache data.
     *
     * @param string $key cache key
     * 
     * @return mix
     */
    public function get($key);

    /**
     * Replace cache data
     * 
     * @param mix $key  cache key
     * @param mix $data cache data
     * @param int $ttl  expiration time
     * 
     * @return boolean
     */
    public function replace($key = '', $data = 60, $ttl = 60);

    /**
     * Remove specified keys.
     * 
     * @param string $key cache key.
     * 
     * @return boolean
     */
    public function delete($key);

    /**
     * Verify if the specified key exists.
     * 
     * @param string $key cache key.
     * 
     * @return boolean true or false
     */
    public function keyExists($key);

}

// END HandlerInterface class

/* End of file HandlerInterface.php */
/* Location: .Obullo/Cache/HandlerInterface.php */