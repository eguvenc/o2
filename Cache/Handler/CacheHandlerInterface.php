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
interface CacheHandlerInterface
{
    /**
     * Constructor
     * 
     * @param array $c       container
     * @param array $options options
     */
    public function __construct(Container $c, $options = array());

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

// END CacheHandlerInterface class

/* End of file CacheHandlerInterface.php */
/* Location: .Obullo/Cache/CacheHandlerInterface.php */