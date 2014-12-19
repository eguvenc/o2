<?php

namespace Obullo\Cache;

/**
 * ArrayContainer
 * 
 * Stores cache->get() method values in application memory 
 * to more fast applications.
 *
 * @category  Cache
 * @package   ArrayContainer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cache
 */
Class ArrayContainer
{
    /**
     * Container
     * 
     * @var container
     */
    protected $container = array();

    /**
     * Connect
     *
     * @param string $key   key
     * @param string $value value
     * 
     * @return void
     */
    public function set($key, $value)
    {
        $this->container[$key] = $value;
    }

    /**
     * Returns to key of value
     *
     * @param string $key key
     * 
     * @return mixed
     */
    public function get($key)
    {
        if (isset($this->container[$key])) {
            return $this->container[$key];
        }
        return false;
    }

    /**
     * Returns to all data
     * 
     * @return array
     */
    public function getArray()
    {
        return $this->container;
    }
}

// END ArrayContainer Class

/* End of file ArrayContainer php */
/* Location: .Obullo/Cache/ArrayContainer.php */