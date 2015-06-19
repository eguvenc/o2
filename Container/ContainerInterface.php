<?php

namespace Obullo\Container;

use ArrayAccess;

/**
 * Container class
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
 */
interface ContainerInterface extends ArrayAccess
{
    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $cid The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function has($cid);
    
    /**
     * Get instance of the class without 
     * register it into Controller object
     * 
     * @param string  $cid    class id
     * @param boolean $params if array params not empty execute the closure() method
     * 
     * @return object
     */
    public function get($cid, $params = null);

    /**
     * Checks package is old / loaded before
     * 
     * @param string $cid package id
     * 
     * @return boolean
     */
    public function used($cid);

    /**
     * Gets a parameter or the closure defining an object.
     *
     * @param string $cid The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or the closure defining an object
     */
    public function raw($cid);

    /**
     * Returns all defined value names.
     *
     * @return array An array of value names
     */
    public function keys();

    /**
     * Registers a service provider.
     *
     * @param array $providers provider name and namespace array
     *
     * @return static
     */
    public function register($providers)
    ;
    /**
     * Check provider is registered
     * 
     * @param string $name provider key like cache, redis, memcache
     * 
     * @return boolean
     */
    public function isRegistered($name);
}

// END Container class

/* End of file Container.php */
/* Location: .Obullo/Container/Container.php */