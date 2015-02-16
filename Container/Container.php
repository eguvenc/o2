<?php

namespace Obullo\Container;

use Closure;
use stdClass;
use Exception;
use Controller;
use ArrayAccess;
use SplObjectStorage;
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
    protected $unset = array();         // Stores classes we want to remove
    protected $unRegistered = array();  // Whether to stored in controller instance
    protected $return = array();        // Stores return requests
    protected $resolved = array();         // Stores resolved class names
    protected $resolvedCommand = array();  // Stores resolved commands to prevent preg match loops.
    protected $bindNamespaces = array();   // Stores bind method namespaces
    protected $services = array();         // Defined services
    protected $registeredService = array();  // Lazy loading for service wrapper class

    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->aliases = new SplObjectStorage;
        $this->services = array_flip(scandir(APP .'classes'. DS . 'Service'));  // Scan service folder
        unset($this->services['Providers']);
    }

    // public function setController()
    // {

    // }

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
     * Checks package is loaded
     * 
     * @param string $cid package id
     * 
     * @return boolean
     */
    public function isCalled($cid) 
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
        $isCoreFile = in_array($key, array('router','uri','config','logger','exception','error'));

        // If controller not available mark classes as unregistered, especially in "router" (routes.php) level some libraries not loaded.
        // Forexample when we call the "view" class at router level ( routes.php ) and if controller instance is not available 
        // We mark them as unregistered classes ( view, session, url .. ) then we assign back into controller when they available.
        if ($noReturn
            AND $controllerExists
            AND Controller::$instance == null
            AND $isCoreFile == false
        ) {
            $this->unRegistered[$cid] = $cid; // Mark them as unregistered then we will assign back into controller.
        }

        if ( ! isset($this->values[$cid])) {  // If not exists in container we load it directly.
            return $this->load($cid);        //  Load service providers, services and none component libraries like cookie, url ..
        }

        if (isset($this->raw[$cid])         // Returns to instance of class or raw closure.
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

        // Below the side If container value not exists in the controller instance
        // then we assign container object into controler instance.
    
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
        $class = $matches['class'];
        $serviceName = ucfirst($class);

        if ( ! empty($matches['provider']) AND strpos($classString, 'service provider') === 0) {
            $this->calledProviders[] = $matches['class'];
            return $this;
        }
        $isService = false;
        $isDirectory = (isset($this->services[$serviceName])) ? true : false;

        if ($isDirectory OR isset($this->services[$serviceName.'.php'])) {  // Resolve services
            $isService = true;
            $data['cid'] = $data['key'] = strtolower($class);
            $serviceClass = $this->resolveServiceClass($serviceName, $isDirectory);

            if ( ! isset($this->registeredService[$serviceName])) {
                $service = new $serviceClass($this);
                $service->register($this, $params, $matches);
                $this->registeredService[$serviceName] = true;
            }
        }
        $data = $this->getClassInfo($class);

        $matches['key'] = $key = $this->getAlias($data['cid'], $data['key'], $matches);
        if ( ! $this->exists($data['cid']) AND ! $isService) {   // Don't register service again.
            $this->registerClass($data['cid'], $key, $matches, $data['class'], $params);
        }
        return $this->offsetGet($data['cid'], $params, $matches);
    }
    
    /**
     * Execute service providers
     * 
     * @param array $params parameters
     * 
     * @return object closure
     */
    public function get($params = array())
    {
        $lastCalled = end($this->calledProviders);
        return $this->registeredProviders[$lastCalled]->register($this, $params);
    }

    /**
     * Creates new service provider connection
     * 
     * @param array $params array
     * 
     * @return void
     */
    public function factory($params = array())
    {
        return $this->get($params);
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
     * Map the class names
     * 
     * @param array  $exp   names
     * @param string $slash whetherto add slash
     * 
     * @return void
     */
    protected function mapName($exp, $slash = '')
    {
        return array_map(
            function ($value) use ($slash) {
                return $slash.ucfirst($value);  // Converts "utils/uri" to "utilsUri"
            },
            $exp
        );
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
            $regex = "^(?<return>(?:)return|)\s*(?<new>(?:)new|)\s*(?<provider>(?:)service provider|)\s*(?<class>[a-zA-Z_\/.:]+)(?<last>.*?)$";
            preg_match('#'.$regex.'#', $class, $matches);
            if ( ! empty($matches['last'])) {
                $matches['as'] = substr(trim($matches['last']), 3);
            }
        }
        $this->resolvedCommand[$class] = $matches;
        return $matches;
    }

    /**
     * Get class name and key
     * 
     * @param string $cid identifier
     *
     * @return array data
     */
    public function getClassInfo($cid)
    {
        if (isset($this->resolved[$cid])) {
            return $this->resolved[$cid];
        }
        $Class = ucfirst($cid);
        $Class = str_replace('/', '\\', trim($Class));  // Replace  all forward slashes "/" to backslashes "\\".

        if (strpos($Class, '\\') > 0) {
            return $this->resolveNamespace($Class, $cid, '\\', true);
        }
        return $this->resolveNamespace($Class.'\\'.$Class, strtolower($cid), '\\', false);
    }

    /**
     * Resolve PSR-0 Namespaces
     * 
     * @param string $Class     class path
     * @param stirng $key       store key
     * @param string $separator path sign
     * @param string $implode   whether to implode namespaces
     * 
     * @return string
     */
    protected function resolveNamespace($Class, $key, $separator = '\\', $implode = true)
    {
        $exp = explode($separator, $Class);
        $ClassName = end($exp);
        if ($implode) {
            $exp = $this->mapName($exp);   // Converts "utils/uri" to "utilsUri"
            $key = lcfirst(implode('', $exp));  // First letter must be lowercase
        }
        array_pop($exp);
        $nameSpace = 'Obullo' .$separator. implode($separator, $exp).$separator. ucfirst($ClassName);
        return $this->resolved[$key] = array('key' => $key, 'cid' => $key, 'class' => $nameSpace);
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
     * Extends an object definition.
     *
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param string   $cid      The unique identifier for the object
     * @param callable $callable A service definition to extend the original
     *
     * @return callable The wrapped callable
     */
    public function extend($cid, $callable)
    {
        if ( ! isset($this->keys[$cid])) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $cid));
        }
        if ( ! is_object($this->values[$cid]) || ! method_exists($this->values[$cid], '__invoke')) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" does not contain an object definition.', $cid));
        }
        if ( ! is_object($callable) || ! method_exists($callable, '__invoke')) {
            throw new InvalidArgumentException('Extension service definition is not a Closure or invokable object.');
        }
        $factory = $this->values[$cid];
        $extended = function ($param) use ($callable, $factory) {
            return $callable($factory($param), $param);
        };
        return $this[$cid] = $extended;
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
    protected function resolveServiceClass($serviceClass, $isDirectory = false)
    {
        if ($isDirectory) {
            $namespace = '\\Service\\'.$serviceClass.'\Env\\'. ucfirst(ENV);
            if (is_dir(APP .'Service'. DS .$serviceClass. DS .'Cli') AND defined('STDIN')) {
                $namespace = '\\Service\\'.$serviceClass.'\Cli\Cli';
            }
            return $namespace;
        }
        return '\Service\\'.$serviceClass;
    }

    /**
     * Bind classes into the container if it not exists.
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
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                    $values   An array of values that customizes the provider
     *
     * @return static
     */
    public function register($provider, array $values = array())
    {
        $classname = explode('\\', get_class($provider));
        $cid = strtolower(str_replace('ServiceProvider', '', end($classname)));

        $this->registeredProviders[$cid] = $provider;

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
        return $this;
    }

}

// END Container class

/* End of file Container.php */
/* Location: .Obullo/Container/Container.php */