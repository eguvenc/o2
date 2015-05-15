<?php

namespace Obullo\Container;

use Closure;
use stdClass;
use Controller;
use ArrayAccess;
use SplObjectStorage;
use RuntimeException;
use InvalidArgumentException;

/**
 * Container class
 *
 * This file after modeled Pimple Software ( Dependency Container )
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
 */
class Container implements ArrayAccess
{
    protected $values = array();
    protected $frozen = array();
    protected $raw = array();
    protected $keys = array();
    protected $aliases = array();          // Stores define() object aliases
    protected $unset = array();            // Stores classes we want to remove
    protected $unRegistered = array();     // Whether to stored in controller instance
    protected $get = array();              // Stores get() (return) requests
    protected $alias = array();            // Stores alias() requests
    protected $services = array();         // Defined services
    protected $registeredServices = array();  // Lazy loading for service wrapper class
    protected $registeredProviders = array();  // Stack data for service provider wrapper class
    protected $registeredConnections = array(); // Lazy loading for service provider register method

    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->aliases = new SplObjectStorage;
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
     * Checks package is on hand
     * 
     * @param string $cid package id
     * 
     * @return boolean
     */
    public function loaded($cid) 
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
     * @param string $params  The construct arguments
     * @param string $matches Registry Command requests
     *
     * @return mixed The value of the parameter or an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($cid, $params = array(), $matches = array())
    {
        list($key, $noReturn, $controllerExists, $isCoreFile) = $this->resolveLoaderParts($cid, $matches);

        if ( ! isset($this->values[$cid])) {    // If does not exist in container we load it directly.
            return $this->load($cid, $params);  //  Load services and none component libraries like cookie, url ..
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
        $isCoreFile = in_array($key, ['app','router','uri','config','logger','exception','error','env']);

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
     * Class and Sercice loader
     *
     * @param string $classString class command
     * @param mixed  $params      array
     * 
     * @return void
     */
    public function load($classString, $params = array())
    {
        $matches = $this->resolveCommand($classString);

        $isService = false;
        $class = strtolower($matches['class']);
        $serviceName = ucfirst($matches['class']);
        $isDirectory = (isset($this->services[$serviceName])) ? true : false;

        if ($isDirectory || isset($this->services[$serviceName.'.php'])) {  // Resolve services
            $isService = true;
            $data['cid'] = $data['key'] = $class;
            $serviceClass = $this->resolveService($serviceName, $isDirectory);

            if ( ! isset($this->registeredServices[$serviceName])) {
                $service = new $serviceClass($this);
                $service->register($this, $params, $matches);

                if ( ! $this->has($data['cid'])) {
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
            'cid' => $class,
            'class' => 'Obullo\\' .ucfirst($matches['class']).'\\'. ucfirst($matches['class'])
        ];
        $matches['key'] = $key = $this->fetchAlias($data['cid'], $data['key'], $matches);
        
        if ( ! $this->has($data['cid']) && ! $isService) {   // Don't register service again.
            $this->registerClass($data['cid'], $key, $matches, $data['class'], $params);
        }
        return $this->offsetGet($data['cid'], $params, $matches);
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
     * Define service with an alias
     * 
     * @param string  $alias    alias
     * @param closure $callable object
     * 
     * @return void
     */
    public function alias($alias, $callable)
    {
        $this->aliases->attach($callable, $alias);
        return $callable;
    }

    /**
     * Get alias name if we have "as" match
     * 
     * @param string $cid     identifier
     * @param string $key     default key
     * @param array  $matches array data
     * 
     * @return string
     */
    protected function fetchAlias($cid, $key, $matches) 
    {   
        $callable = $this->raw($cid);
        if ( ! is_null($callable) && $this->aliases->contains($callable)) {
            $key = $this->aliases[$callable];
        }
        return $this->searchAs($key, $matches);
    }

    /**
     * Search "as" keyword
     * 
     * @param string $key     class key
     * @param array  $matches loader command matches
     * 
     * @return string
     */
    protected function searchAs($key, $matches)
    {
        if ( ! empty($matches['as'])) {  // Replace key with alias if we have it
            $key = $matches['as'];
        }
        return trim($key);
    }

    /**
     * Register unregistered class to container
     * 
     * @param string $cid       identifier
     * @param string $key       alias
     * @param string $matches   commands
     * @param string $ClassName classname
     * @param string $params    array
     * 
     * @return mixed object or null
     */
    protected function registerClass($cid, $key, $matches, $ClassName, $params = array())
    {
        if ( ! isset($this->keys[$cid]) && class_exists('Controller', false) && ! isset($this->unset[$cid])) {

            $this[$cid] = function ($params = array()) use ($key, $matches, $ClassName) {

                $Object = is_string($ClassName) ? new $ClassName($this, $params) : $ClassName;

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
     * @param string  $alias  alias of class
     * @param boolean $shared if false returns to closure() method
     * 
     * @return object
     */
    public function get($cid, $alias = null, $shared = true)
    {
        if ( ! empty($alias)) {
            return $this->getAlias($cid, $alias);
        }
        if ( ! $shared) {
            return $this->getClosure($cid);
        }
        $this->get[$cid] = $cid;
        return $this[$cid];
    }

    /**
     * Create alias of the class for Controller
     * 
     * @param string $cid   class id
     * @param string $alias name
     * 
     * @return object
     */
    protected function getAlias($cid, $alias)
    {
        $this->as[$cid] = $alias;
        return $this[$cid];
    }

    /**
     * Get closure function of object
     * 
     * @param string $cid class id
     * 
     * @return object Closure
     */
    protected function getClosure($cid)
    {
        if ( ! isset($this->keys[$cid])) {  // First load class to container if its not loaded
            $this[$cid];
        }
        if (isset($this->raw[$cid])) {  // Check is it available if yes return to closure()
            return $this->raw[$cid];
        }
        return $this->values[$cid];     // else return to object
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
            'as' => ''
        );
        if (isset($this->as[$class])) {
            $matches['as'] = $this->as[$class];
        }
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