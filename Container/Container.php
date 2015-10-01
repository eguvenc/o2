<?php

namespace Obullo\Container;

use Closure;
use ArrayAccess;
use RuntimeException;
use InvalidArgumentException;

/**
 * Obullo DI
 * 
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Container implements ArrayAccess, ContainerInterface
{
    protected $env;
    protected $raw = array();
    protected $frozen = array();
    protected $values = array();
    protected $keys = array();
    protected $unset = array();     // Stores classes we want to remove
    protected $loader = array();    // Service loader object
    protected $services = array();  // Service array

    /**
     * Register service classes if required
     * 
     * @param array $loader service loader object
     * 
     * @return void
     */
    public function __construct($loader = null)
    {
        if (is_object($loader)) {
            $this->loader = $loader->scan();
            $this->services = $loader();
        }
    }

    /**
     * Sets application environment
     * 
     * @param string $env value
     * 
     * @return object
     */
    public function setEnv($env)
    {
        $this->env = $env;
        return $this;
    }

    /**
     * Returns to application environment
     * 
     * @return string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $cid The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function has($cid) 
    {
        return $this->offsetExists($cid);   // Is it component ?
    }

    /**
     * Checks package is old / loaded before
     * 
     * @param string $cid package id
     * 
     * @return boolean
     */
    public function active($cid)
    {
        return isset($this->frozen[$cid]);
    }    

    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same name as an existing parameter would break your container).
     *
     * @param string $cid   The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or a closure to define an object
     * 
     * @return void
     */
    public function offsetSet($cid, $value)
    {   
        if (isset($this->frozen[$cid])) {
            return;
        }
        $this->values[$cid] = $value;
        $this->keys[$cid] = true;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $cid    The unique identifier for the parameter or object
     * @param array  $params Parameters
     * 
     * @return mixed The value of the parameter or an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($cid, $params = array())
    {
        if (! isset($this->values[$cid])) {     // If does not exist in container we load it directly.
            return $this->load($cid);           // Load services and none component libraries like cookie, url ..
        }
        if (isset($this->raw[$cid])             // Returns to instance of class or raw closure.
            || ! is_object($this->values[$cid])
            || ! method_exists($this->values[$cid], '__invoke')
        ) {
            return $this->values[$cid];
        }
        $this->frozen[$cid] = true;
        $this->raw[$cid] = $this->values[$cid];
        return $this->values[$cid] = $this->closure($this->values[$cid], $params);
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $cid The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function offsetExists($cid)
    {
        return isset($this->keys[$cid]);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $cid The unique identifier for the parameter or object
     *
     * @return void
     */
    public function offsetUnset($cid)
    {
        if (isset($this->keys[$cid])) {
            if (is_object($this->values[$cid])) {
                unset($this->protected[$this->values[$cid]]);
            }
            unset($this->values[$cid], $this->frozen[$cid], $this->raw[$cid], $this->keys[$cid]);
        }
        $this->unset[$cid] = true;
    }

    /**
    * Run closure with params
    * 
    * @param object $closure callable
    * @param array  $params  parameters
    * 
    * @return object closure
    */
    protected function closure(Closure $closure, $params = array())
    {
        if (count($params) > 0) {
            return $closure($params);
        }
        return $closure();
    }

    /**
     * Class and Service loader
     *
     * @param string $classString class command
     * @param array  $params      closure params
     * 
     * @return void
     */
    public function load($classString, $params = array())
    {
        $class = trim($classString);
        $cid = strtolower($class);

        $isService = false;
        if (count($this->services) > 0) {
            if ($this->loader->resolve($this, $class, $this->services)) {
                $isService = true;
            }
        }
        if (! $this->has($cid) && ! $isService) {
            throw new RuntimeException(
                sprintf(
                    'The class "%s" is not available. Please register it as a component or service.',
                    $cid
                )
            );
        }
        return $this->offsetGet($cid, $params);
    }

    /**
     * Alias of array access $c['key'];
     * 
     * @param string $cid class id
     * 
     * @return object
     */
    public function get($cid)
    {
        return $this[$cid];
    }

    /**
     * Gets a parameter or the closure defining an object.
     *
     * @param string $cid The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or the closure defining an object
     */
    public function raw($cid)
    {
        if (! isset($this->keys[$cid])) {
            return null;
        }
        if (isset($this->raw[$cid])) {
            return $this->raw[$cid];
        }
        return $this->values[$cid];
    }

    /**
     * Returns all defined value names.
     *
     * @return array An array of value names
     */
    public function keys()
    {
        return array_keys($this->values);
    }
    
    /**
     * Magic method var_dump($c) wrapper ( for PHP 5.6.0 and newer versions )
     * 
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'classes' => $this->keys()
        ];
    }

}
