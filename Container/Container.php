<?php

namespace Obullo\Container;

use Closure;
use stdClass;
use RuntimeException;
use InvalidArgumentException;

/**
 * Container class
 *
 * This file modeled after Pimple Software 
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
 */
class Container implements ContainerInterface
{
    protected $values = array();
    protected $frozen = array();
    protected $raw = array();
    protected $keys = array();
    protected $unset = array();            // Stores classes we want to remove
    protected $get = array();              // Stores get() (return) requests
    protected $services = array();         // Defined services
    protected $registeredServices = array();  // Lazy loading for service wrapper class
    protected $registeredProviders = array();  // Stack data for service provider wrapper class
    protected $registeredConnections = array(); // Lazy loading for service provider register method
    protected $getParams = array();         // Stores get() method parameters
    protected $valuesWithParams = array();  // Stores object values which they used with with get(, $params) 

    /**
     * Constructor
     */
    public function __construct() 
    {
        $services = scandir(APP .'classes'. DS . 'Service'); // Scan service folder
        $this->services = array_flip($services);             // Normalize service array
        unset($this->services['Providers']);
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
        return $this->offsetExists($cid);
    }

    /**
     * Checks package is old / loaded before
     * 
     * @param string $cid package id
     * 
     * @return boolean
     */
    public function used($cid) 
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
        $isService = false;
        $cid = strtolower($class);
        $serviceName = ucfirst($class);
        $isDirectory = (isset($this->services[$serviceName])) ? true : false;

        if ($isDirectory || isset($this->services[$serviceName.'.php'])) {  // Resolve services
            $isService = true;
            $serviceClass = $this->resolveService($serviceName, $isDirectory);

            if (! isset($this->registeredServices[$serviceName])) {

                $service = new $serviceClass($this);
                $service->register($this);

                if (! $this->has($cid)) {
                    throw new RuntimeException(
                        sprintf(
                            "%s service configuration error service class name must be same with container key.",
                            $serviceName
                        )
                    );
                }
                $this->registeredServices[$serviceName] = true;
            }
        }
        if (! $this->has($cid) && ! $isService) {   // Don't register service again.
            throw new RuntimeException(
                sprintf(
                    'The class "%s" is not available. Please register it in components.php or create a service.',
                    $cid
                )
            );
        }
        return $this->offsetGet($cid, $params);
    }
    
    /**
     * Returns to provider instance
     * 
     * @param string $name provider name
     * 
     * @return object \Obullo\Service\Providers\ServiceProviderInterface
     */
    public function resolveProvider($name)
    {
        $name = strtolower($name);
        if (! isset($this->registeredProviders[$name])) {
            throw new RuntimeException(
                sprintf(
                    "%s provider is not registered, please register it in providers.php",
                    ucfirst($name)
                )
            );
        }
        $Class = $this->registeredProviders[$name];
        if (! isset($this->registeredConnections[$name])) {
            $provider = new $Class;
            $provider->register($this);
            $this->registeredConnections[$name] = $provider;
        }
        return $this->registeredConnections[$name];
    }

    /**
     * Get instance of the class without 
     * register it into Controller object
     * 
     * @param string  $cid       class id
     * @param boolean $params    if array params not empty execute the closure() method
     * @param boolean $singleton on / off singleton
     * 
     * @return object
     */
    public function get($cid, $params = null, $singleton = true)
    {
        if ($singleton == false) {
            return $this->getClosure($cid, array());   // Create new object wihout params
        }
        if (is_array($params) && count($params) > 0) {
            $pid = self::getParamsId($cid, $params);  // Get parameter id of object

            if (isset($this->getParams[$pid])) {
                return $this->valuesWithParams[$cid];  // Return cached serialized object
            } else {
                $this->get[$cid] = $cid;
                $this->getParams[$pid] = $pid;
                return $this->valuesWithParams[$cid] = $this->getClosure($cid, $params);  // Create and store object with params
            }
            return $this->getClosure($cid, $params);
        }
        $this->get[$cid] = $cid;
        return $this[$cid];
    }

    /**
     * Serialize class parameters
     * 
     * @param string $cid    class key
     * @param array  $params parameters
     * 
     * @return string
     */
    protected static function getParamsId($cid, array $params)
    {
        return sprintf("%u", crc32(serialize($params).$cid));
    }

    /**
     * Get closure function of object
     * 
     * @param string $cid    class id
     * @param array  $params closure params
     * 
     * @return object Closure
     */
    protected function getClosure($cid, $params = array())
    {
        if (! isset($this->keys[$cid])) {  // First load class to container if its not loaded
            return $this->load($cid, $params);
        }
        if (isset($this->raw[$cid])) {  // Check is it available if yes return to closure()
            return $this->raw[$cid]($params);
        }
        return $this->values[$cid]($params);     // else return to object
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
     * Resolve environment based services or service providers
     * 
     * @param string $serviceClass namespace
     * @param string $isDirectory  is directory
     * 
     * @return string class namespace
     */
    protected function resolveService($serviceClass, $isDirectory = false)
    {
        if ($isDirectory) {
            return '\\Service\\'.$serviceClass.'\Env\\'. ucfirst($this['app']->env());
        }
        return '\Service\\'.$serviceClass;
    }

    /**
     * Registers a service provider.
     *
     * @param array $providers provider name and namespace array
     *
     * @return static
     */
    public function register($providers)
    {
        foreach ((array)$providers as $name => $namespace) {
            $this->registeredProviders[$name] = $namespace;
        }
        return $this;
    }

    /**
     * Check provider is registered
     * 
     * @param string $name provider key like cache, redis, memcache
     * 
     * @return boolean
     */
    public function isRegistered($name)
    {
        return isset($this->registeredProviders[$name]);
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