<?php

namespace Obullo\Container;

use Closure;
use stdClass;
use Controller;
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
    protected $unRegistered = array();     // Whether to stored in controller instance
    protected $get = array();              // Stores get() (return) requests
    protected $services = array();         // Defined services
    protected $registeredServices = array();  // Lazy loading for service wrapper class
    protected $registeredProviders = array();  // Stack data for service provider wrapper class
    protected $registeredConnections = array(); // Lazy loading for service provider register method

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
     * @param string $cid     The unique identifier for the parameter or object
     * @param string $matches Registry Command requests
     * @param array  $params  Parameters
     * 
     * @return mixed The value of the parameter or an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($cid, $matches = array(), $params = array())
    {
        list($key, $noReturn, $controllerExists, $isCoreFile) = $this->resolveLoaderParts($cid, $matches);

        if ( ! isset($this->values[$cid])) {    // If does not exist in container we load it directly.
            return $this->load($cid);           //  Load services and none component libraries like cookie, url ..
        }
        $isAllowedToStore = static::isSuitable($key, $noReturn, $controllerExists, $isCoreFile);

        if (isset($this->raw[$cid])             // Returns to instance of class or raw closure.
            || ! is_object($this->values[$cid])
            || ! method_exists($this->values[$cid], '__invoke')
        ) {
            return ($isAllowedToStore) ? Controller::$instance->{$key} = $this->values[$cid] : $this->values[$cid];
        }
        $this->frozen[$cid] = true;
        $this->raw[$cid] = $this->values[$cid];

        // Below the side If container value does not exist in the controller instance
        // then we assign container object into controller instance.
    
        // Also this side assign libraries to all Layers. 
        // In Layers sometimes we call $c['view'] service in the current sub layer but when we call $this->view then 
        // it says "$this->view" undefined object that's why we need to assign libraries also for sub layers.
        
        if ($isAllowedToStore) {
            return Controller::$instance->{$key} = $this->values[$cid] = $this->closure($this->values[$cid], $params);
        }
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
     * Check class is suitable to store Controller
     * 
     * @param string  $key              class key
     * @param boolean $noReturn         whether to return used
     * @param boolean $controllerExists whether to controller file is included
     * @param boolean $isCoreFile       prevent core files
     * 
     * @return boolean
     */
    protected static function isSuitable($key, $noReturn, $controllerExists, $isCoreFile)
    {
        if ($noReturn
            && $controllerExists
            && Controller::$instance != null
            && $isCoreFile == false
            && ! isset(Controller::$instance->{$key})    // Warning: If user use $this->c['uri'] in load method 
        ) {                                              // it overrides instance of the current controller uri and effect to layers
            return true;                                 // we need to check the key using isset
        }
        return false;
    }

    /**
     * Resolve loader items
     * 
     * @param string $cid     class id
     * @param array  $matches loader matches
     * 
     * @return array list
     */
    protected function resolveLoaderParts($cid, $matches)
    {
        $key = isset($matches['key']) ? $matches['key'] : $cid;
        $noReturn = empty($matches['return']);
        $controllerExists = class_exists('Controller', false);
        $isCoreFile = $this->isCore($key);

        $this->markUnregisteredObjects($cid, $noReturn, $controllerExists, $isCoreFile);

        return [$key, $noReturn, $controllerExists, $isCoreFile];
    }

    /**
     * Mark unregistered libraries
     * 
     * @param string  $cid              class id
     * @param boolean $noReturn         whether to return used
     * @param boolean $controllerExists whether to controller file is included
     * @param boolean $isCoreFile       prevent core files
     * 
     * @return boolean
     */
    protected function markUnregisteredObjects($cid, $noReturn, $controllerExists, $isCoreFile)
    {
        // If controller not available mark classes as unregistered, especially in "router" (routes.php) level some libraries not loaded.
        // Forexample when we call the "view" class at router level ( routes.php ) and if controller instance is not available 
        // We mark them as unregistered classes ( view, session, url .. ) then we assign back into controller when they availabl

        if ($noReturn
            && $controllerExists
            && Controller::$instance == null
            && $isCoreFile == false
        ) {
            $this->unRegistered[$cid] = $cid;   // Mark them as unregistered then we will assign back into controller.
        }
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
        $matches = $this->resolveCommand($classString);

        $isService = false;
        $cid = strtolower($matches['class']);
        $serviceName = ucfirst($matches['class']);
        $isDirectory = (isset($this->services[$serviceName])) ? true : false;

        if ($isDirectory || isset($this->services[$serviceName.'.php'])) {  // Resolve services
            $isService = true;
            $serviceClass = $this->resolveService($serviceName, $isDirectory);

            if ( ! isset($this->registeredServices[$serviceName])) {

                $service = new $serviceClass($this);
                $service->register($this);

                if ( ! $this->has($cid)) {
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
        $data = [
            'key' => $matches['class'],
            'cid' => $cid,
            'class' => 'Obullo\\' .ucfirst($matches['class']).'\\'. ucfirst($matches['class'])
        ];
        $matches['key'] = $data['key'];
        
        if ( ! $this->has($cid) && ! $isService) {   // Don't register service again.
            $this->registerClass($cid, $data['key'], $matches, $data['class']);
        }
        return $this->offsetGet($cid, $matches, $params);
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
        if ( ! isset($this->registeredProviders[$name])) {
            throw new RuntimeException(
                sprintf(
                    "%s provider is not registered, please register it in providers.php",
                    ucfirst($name)
                )
            );
        }
        $Class = $this->registeredProviders[$name];
        if ( ! isset($this->registeredConnections[$name])) {
            $provider = new $Class;
            $provider->register($this);
            $this->registeredConnections[$name] = $provider;
        }
        return $this->registeredConnections[$name];
    }

    /**
     * Register unregistered class to container
     * 
     * @param string $cid       identifier
     * @param string $key       alias
     * @param string $matches   commands
     * @param string $ClassName classname
     * 
     * @return mixed object or null
     */
    protected function registerClass($cid, $key, $matches, $ClassName)
    {
        if ( ! isset($this->keys[$cid]) && class_exists('Controller', false) && ! isset($this->unset[$cid])) {

            $this[$cid] = function () use ($key, $matches, $ClassName) {

                $Object = is_string($ClassName) ? new $ClassName($this) : $ClassName;

                if (Controller::$instance != null && empty($matches['return'])) {  // Let's sure controller instance available and not null
                    return Controller::$instance->{$key} = $Object;
                }
                return $Object;
            };
        }
        return null;
    }

    /**
     * Get instance of the class without 
     * register it into Controller object
     * 
     * @param string  $cid    class id
     * @param boolean $params if array params not empty execute the closure() method
     * 
     * @return object
     */
    public function get($cid, $params = null)
    {
        if ($params === false) {
            $params = array();
        }
        if (is_array($params)) {
            return $this->getClosure($cid, $params);
        }
        $this->get[$cid] = $cid;
        return $this[$cid];
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
        if ( ! isset($this->keys[$cid])) {  // First load class to container if its not loaded
            return $this->load($cid, $params);
        }
        if (isset($this->raw[$cid])) {  // Check is it available if yes return to closure()
            return $this->raw[$cid]($params);
        }
        return $this->values[$cid]($params);     // else return to object
    }

    /**
     * Resolve loader commands
     * 
     * @param string $class name
     * 
     * @return array $matches
     */
    protected function resolveCommand($class)
    {
        $class = trim($class);
        $matches = array(
            'return' => '',   // fill return if get() function used.
            'new' => '',
            'class' => $class,
        );
        if (isset($this->get[$class])) {
            $matches['return'] = 'return';
        }
        return $matches;
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
        if ( ! isset($this->keys[$cid])) {
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
     * Track unregistered classes
     * then we register them into Controller instance.
     * Used in the Controller.
     * 
     * @return array
     */
    public function unRegisteredKeys()
    {
        return array_keys($this->unRegistered);
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
     * Check class is core
     * 
     * @param string $cid class id
     * 
     * @return boolean
     */
    public function isCore($cid)
    {
        return in_array($cid, ['app','router','uri','config','logger','exception','error','env']);
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

}

// END Container class

/* End of file Container.php */
/* Location: .Obullo/Container/Container.php */