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
     * Get instance of the class without 
     * register it into Controller
     * 
     * @param string $cid class id
     * 
     * @return object
     */
    public function get($cid);
    
    /**
     * Checks if a parameter or an object is registered.
     *
     * @param string $cid The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function has($cid);
}