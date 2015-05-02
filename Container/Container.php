<?php

namespace Obullo\Container;

use Closure;
use stdClass;
use Controller;
use ArrayAccess;
use SplObjectStorage;
use RuntimeException;
use InvalidArgumentException;

/*
 * Container for Obullo (c) 2015
 * 
 * This file after modeled Pimple Software ( Dependency Container ).
 * 
 * http://pimple.sensiolabs.org/
 */

/**
 * Container class.
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
 */
class Container implements ArrayAccess
{
    protected $values = array();
    protected $frozen = array();
    protected $raw = array();
    protected $keys = array();
    protected $aliases = array();
    protected $unset = array();            // Stores classes we want to remove
    protected $unRegistered = array();     // Whether to stored in controller instance
    protected $return = array();           // Stores return requests
    protected $resolved = array();         // Stores resolved class names
    protected $resolvedCommand = array();  // Stores resolved commands to prevent preg match loops.
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
        $this->services = array_flip(scandir(APP .'classes'. DS . 'Service'));  // Scan service folder
        unset($this->services['Providers']);
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $cid The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function exists($cid) 
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
        if ( ! is_callable($value) AND is_object($value)) {
            return $this->bind($cid, $value);
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
        $key = isset($matches['key']) ? $matches['key'] : $cid;
        $noReturn = empty($matches['return']);
        $controllerExists = class_exists('Controller', false);
        $isCoreFile = in_array($key, ['app','router','uri','config','logger','exception','error','env']);

        // If controller not available mark classes as unregistered, especially in "router" (routes.php) level some libraries not loaded.
        // Forexample when we call the "view" class at router level ( routes.php ) and if controller instance is not available 
        // We mark them as unregistered classes ( view, session, url .. ) then we assign back into controller when they available.
        if ($noReturn
            AND $controllerExists
            AND Controller::$instance == null
            AND $isCoreFile == false
        ) {
            $this->unRegistered[$cid] = $cid;   // Mark them as unregistered then we will assign back into controller.
        }
        if ( ! isset($this->values[$cid])) {    // If does not exist in container we load it directly.
            return $this->load($cid);           //  Load services and none component libraries like cookie, url ..
        }
        if (isset($this->raw[$cid])             // Returns to instance of class or raw closure.
            || ! is_object($this->values[$cid])
            || ! method_exists($this->values[$cid], '__invoke')
        ) {
            if ($noReturn AND $controllerExists AND Controller::$instance != null) {
                
                $value = ! empty($matches['new']) ?  $this->runClosure($this->raw[$cid], $params) : $this->values[$cid];
                if ( ! isset(Controller::$instance->{$key})) {      // If user use $this->c['uri'] in load method 
                    return Controller::$instance->{$key} = $value;  // it overrides instance of the current controller uri and effect to layers
                }
            }
            if ( ! empty($matches['new'])) {
                return $this->runClosure($this->raw[$cid], $params);  // Return to new instance if Controller::$instance == null.
            }
            return $this->values[$cid];
        }
        $this->frozen[$cid] = true;
        $this->raw[$cid] = $this->values[$cid];

        // Below the side If container value does not exist in the controller instance
        // then we assign container object into controller instance.
    
        // Also this side assign libraries to all Layers. 
        // In Layers sometimes we call $c['view'] service in the current sub layer but when we call $this->view then 
        // it says "$this->view" undefined object that's why we need to assign libraries also for sub layers.
        
        if ($controllerExists
            AND $noReturn  //  Store class into controller instance if return not used.
            AND Controller::$instance != null  // Sometimes in router level controller instance comes null.
            AND $isCoreFile == false  // Ignore core classes which they have already loaded
        ) {
            return Controller::$instance->{$key} = $this->values[$cid] = $this->runClosure($this->values[$cid], $params);
        }
        return $this->values[$cid] = $this->runClosure($this->values[$cid], $params);
    }

    /**
    * Run closure with params
    * 
    * @param object $closure callable
    * @param array  $params  parameters
    * 
    * @return object closure
    */
    protected function runClosure(Closure $closure, $params = array())
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
        $class = strtolower($matches['class']);
        $serviceName = ucfirst($matches['class']);

        $isService = false;
        $isDirectory = (isset($this->services[$serviceName])) ? true : false;

        if ($isDirectory OR isset($this->services[$serviceName.'.php'])) {  // Resolve services
            $isService = true;
            $data['cid'] = $data['key'] = $class;
            $serviceClass = $this->resolveService($serviceName, $isDirectory);

            if ( ! isset($this->registeredServices[$serviceName])) {
                $service = new $serviceClass($this);
                $service->register($this, $params, $matches);

                if ( ! $this->exists($data['cid'])) {
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
        // $data = $this->getClassInfo($matches['class']);
        $data = [
            'key' => $matches['class'],
            'cid' => $class,
            'class' => 'Obullo\\' .ucfirst($matches['class']).'\\'. ucfirst($matches['class'])
        ];
        $matches['key'] = $key = $this->getAlias($data['cid'], $data['key'], $matches);
        
        if ( ! $this->exists($data['cid']) AND ! $isService) {   // Don't register service again.
            $this->registerClass($data['cid'], $key, $matches, $data['class'], $params);
        }
        return $this->offsetGet($data['cid'], $params, $matches);
    }
    
    /**
     * Returns to provider instance
     * 
     * @param string $class provider name
     * 
     * @return object \Obullo\ServiceProviders\ServiceProviderInterface
     */
    public function resolveProvider($class)
    {
        $class = strtolower($class);
        if ( ! isset($this->registeredProviders[$class])) {
            throw new RuntimeException(
                sprintf(
                    "%s provider is not registered, please register it in providers.php",
                    ucfirst($class)
                )
            );
        }
        $providerName = $this->registeredProviders[$class];
        if ( ! isset($this->registeredConnections[$class])) {
            $provider = new $providerName;
            $provider->register($this);
            $this->registeredConnections[$class] = $provider;
        }
        return $this->registeredConnections[$class];
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
    protected function getAlias($cid, $key, $matches) 
    {   
        $callable = $this->raw($cid);

        if ( ! is_null($callable) AND $this->aliases->contains($callable)) {
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
        if ( ! empty($matches['last'])) {  // Replace key with alias if we have it
            $key = substr(trim($matches['last']), 3);
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
        if ( ! isset($this->keys[$cid]) AND class_exists('Controller', false) AND ! isset($this->unset[$cid])) {

            $this[$cid] = function ($params = array()) use ($key, $matches, $ClassName) {

                $Object = is_string($ClassName) ? new $ClassName($this, $params) : $ClassName;

                if (Controller::$instance != null AND empty($matches['return'])) {  // Let's sure controller instance available and not null           
                    return Controller::$instance->{$key} = $Object;
                }
                return $Object;
            };
        }
        return null;
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
        if (isset($this->resolvedCommand[$class])) {
            return $this->resolvedCommand[$class];
        }
        $matches = array(
            'return' => '',
            'new' => '',
            'class' => $class,
            'last' => '',
            'as' => ''
        );
        if (strrpos($class, ' ')) {  // If we have command request
            $regex = "^(?<return>(?:)return|)\s*(?<new>(?:)new|)\s*(?<class>[a-zA-Z_\/.:]+)(?<last>.*?)$";
            preg_match('#'.$regex.'#', $class, $matches);
            if ( ! empty($matches['last'])) {
                $matches['as'] = substr(trim($matches['last']), 3);
            }
        }
        $this->resolvedCommand[$class] = $matches;
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
     * Bind classes into the container if its not exists.
     * 
     * @param string $cid       class id
     * @param string $namespace class
     * 
     * @return void
     */
    protected function bind($cid, $namespace = null)
    {
        if ( ! is_object($namespace)) {
            throw new InvalidArgumentException('Bind method second parameter must be object.');
        }
        if ( ! $this->exists($cid)) {   // Don't register service again.
            $this->registerClass($cid, null, array('return' => 'return'), $namespace);
        }
        if (isset($this->frozen[$cid])) {
            return $this->values[$cid];
        }
        $this->frozen[$cid] = true;
        $this->raw[$cid] = $this->values[$cid];
        return $this->values[$cid] = $this->runClosure($this->values[$cid]);
    }

    /**
     * Registers a service provider.
     *
     * @param object $provider A Service Provider instance
     *
     * @return static
     */
    public function register($provider)
    {
        $classname = explode('\\', $provider);
        $cid = strtolower(str_replace('ServiceProvider', '', end($classname)));

        $this->registeredProviders[$cid] = $provider;
        return $this;
    }

    /**
     * Check provider is registered
     * 
     * @param string $provider key like cache, redis, memcache
     * 
     * @return boolean
     */
    public function isRegistered($provider)
    {
        return isset($this->registeredProviders[$provider]);
    }

}

// END Container class

/* End of file Container.php */
/* Location: .Obullo/Container/Container.php */