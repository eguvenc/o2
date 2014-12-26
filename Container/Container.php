<?php

namespace Obullo\Container;

use Controller,
    ArrayAccess,
    SplObjectStorage,
    InvalidArgumentException,
    Exception;
/*
 * Container for Obullo Ersin Guvenc (c) 2014
 * 
 * This file after modeled Pimple Software Dependency Container.
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
Class Container implements ArrayAccess
{
    protected $values = array();
    protected $frozen = array();
    protected $raw = array();
    protected $keys = array();
    protected $aliases = array();
    protected $unset = array();         // Stores classes we want to remove
    protected $registered = array();    // Stores registered services
    protected $unRegistered = array();  // Whether to stored in controller instance
    protected $return = array();        // Stores return requests
    protected $resolved = array();      // Stores resolved class names
    protected $resolvedCommand = array();  // Stores resolved commands to prevent preg match loops.

    protected $envArray = array();      // $c->detectEnvironment() use this configuration
    protected static $env;

    const PROVIDER_SIGN = ':';  // To protect providers we use a sign.

    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->envArray = include ROOT .'app'. DS .'config'. DS .'environments.php';
        $this->aliases = new SplObjectStorage;
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
        if (strpos($cid, 'provider/') === 0) {  //  If we have provider alias replace provider path with sign ":".
            $cid = str_replace('provider/', 'provider'.static::PROVIDER_SIGN, $cid);
        }
        return $this->offsetExists($cid);
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
     * @param string $cid       The unique identifier for the parameter or object
     * @param string $params    The construct arguments
     * @param string $matches   Registry Command requests
     * @param string $isService class is service
     *
     * @return mixed The value of the parameter or an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($cid, $params = array(), $matches = array(), $isService = false)
    {
        $key = isset($matches['key']) ? $matches['key'] : $cid;
        $noReturn = empty($matches['return']);
        $controllerExists = class_exists('Controller', false);
        $isCoreFile = in_array($key, array('router','uri','config','logger','exception','error','httpSanitizer'));

        $keyExists = false;
        if ($controllerExists
            AND Controller::$instance != null
            AND in_array($key, array_keys(get_object_vars(Controller::$instance)))
        ) {
            $keyExists = true;
        }
        // If controller not available mark classes as unregistered, especially in "router" (routes.php) level some libraries not loaded.
        // Forexample when we call the "view" class at router level ( routes.php ) and if controller instance is not available 
        // We mark them as unregistered classes ( view, session, url .. ) then we assign back into controller when they are available.
        if ($noReturn
            AND $controllerExists
            AND Controller::$instance == null
            AND $isCoreFile == false
        ) {
            $this->unRegistered[$cid] = $cid; // Mark them as unregistered then we will assign back into controller.
        }

        if (isset($this->raw[$cid])         // Returns to instance of class or raw closure.
            || ! is_object($this->values[$cid])
            || ! method_exists($this->values[$cid], '__invoke')
        ) {
            if ( ! empty($matches['new'])) {  //  If we have new class request ?
                return $this->runClosure($this->raw[$cid], $params);
            }
            if ($noReturn AND $controllerExists AND $keyExists == false AND Controller::$instance != null) {
                return Controller::$instance->{$key} = $this->values[$cid];
            }
            return $this->values[$cid];
        }             

        $this->frozen[$cid] = true;
        $this->raw[$cid] = $this->values[$cid];

        // This is assign loaded object container instance into controler instance.
        // Also assign libraries to all Layers. In Layers sometimes we call $c['view'] service in the current sub layer but when we call $this->view then 
        // it says "$this->view" undefined object that's why we need to assign libraries also for sub layers.
        if ($controllerExists
            AND $noReturn  //  Store class into controller instance if return not used.
            AND $keyExists == false
            AND Controller::$instance != null  // Sometimes in router level controller instance comes null.
            AND $isCoreFile == false  // Ignore core classes which they have already loaded
        ) {
            $instance = Controller::$instance->{$key} = $this->values[$cid] = $this->runClosure($this->values[$cid], $params);
            $instance = ($isService) ? null : $instance;
            return $instance;
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
    protected function runClosure($closure, $params = array())
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
     * Class loader
     * use func_get_args() string $arguments 0 = > class , 
     * others arguments arg1, arg1.
     *
     * @param string $classString class command
     * @param mixed  $params      array
     * 
     * @return void
     */
    public function load($classString, $params = array())
    {
        $matches = $this->resolveCommand(trim($classString));
        $class = $matches['class'];
        
        $isService = false;
        $provider = strpos($class, 'service/provider');
        $data = $this->getClassInfo($matches['class'], ($provider !== false) ? true : false);

        if (strpos($class, 'service/') === 0) {  // Resolve services & service providers

            $isService = true;
            $exp = explode('/', $class);
            $map = $this->mapName($exp);  // Converts "utils/uri" to "utilsUri"
            $serviceClass = '\\'.implode('\\', $map);

            $data['key'] = end($exp);
            $data['cid'] = substr($class, 8); // Remove "service/" word

            $serviceClass = $this->resolveServiceClass($serviceClass, $data['key'], 'service');
            $serviceClass = $this->resolveServiceClass($serviceClass, $data['key'], 'provider');

            if (isset($this->registered[$serviceClass])) {  // If service registered before don't register it again.
                $service = $this->registered[$serviceClass];
            } else {
                $service = new $serviceClass;
                $service->register($this);
                $this->registered[$serviceClass] = $service;
            }
            $implements = key(class_implements($service));

            $key = $this->getAlias($data['cid'], $data['key'], $matches);

            if ($implements == 'Service\Provider\ProviderInterface') {
                $cid = strtolower(str_replace('\\', static::PROVIDER_SIGN, substr($data['class'], 8))); // Protect providers from services
                $key = $this->getAlias($data['cid'], lcfirst(substr(implode('', $map), 7)), $matches);  // Converts "serviceProviderCache" to "providerCache"

                $newMatches['return'] = 'return'; // Provider must be return to closure.
                $newMatches['new'] = $matches['new'];
                $newMatches['key'] = $key;
                $instance = $this->offsetGet($cid, $params, $newMatches);

                if ( ! empty($matches['return'])) {
                    return $instance;
                }
                if (Controller::$instance != null) {
                    Controller::$instance->{$key} = $instance;
                } 
                return;
            }
        }
        if ($isService == false) {  // Load none service libraries
            $key = $this->getAlias($data['cid'], $data['key'], $matches);
        }
        $matches['key'] = $key;
        if ( ! $this->exists($data['cid']) AND ! $isService) {   // Don't register service again.
            $this->register($data['cid'], $key, $matches, $data['class'], $params);
        }
        return $this->offsetGet($data['cid'], $params, $matches, $isService);
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
        if (strpos($cid, 'provider/') === 0) {  //  If we have provider alias replace provider path with sign ":".
            $cid = str_replace('provider/', 'provider'.static::PROVIDER_SIGN, $cid);
        }
        $callable = $this->raw($cid);

        if ( ! is_null($callable) AND $this->aliases->contains($callable)) {
            $key = $this->aliases[$callable];
        }
        if ( ! empty($matches['last'])) {  // Replace key with alias if we have it
            $key = preg_replace('#(as)\b#i', '', trim($matches['last']));  // as "name"
        }
        return trim($key);
    }

    /**
     * Map the class names
     * 
     * @param array $exp names
     * 
     * @return void
     */
    protected function mapName($exp)
    {
        return array_map(
            function ($value) {
                return ucfirst($value);  // Converts "utils/uri" to "utilsUri"
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
    protected function register($cid, $key, $matches, $ClassName, $params)
    {
        if ( ! isset($this->keys[$cid]) AND class_exists('Controller', false) AND ! isset($this->unset[$cid])) {
            $this[$cid] = function ($params = array()) use ($key, $matches, $ClassName) {
                if (Controller::$instance != null AND empty($matches['return'])) {  // Let's sure controller instance available and is not null
                    return Controller::$instance->{$key} = new $ClassName($this, $params);
                }
                return new $ClassName($this, $params);
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
        if (isset($this->resolvedCommand[$class])) {
            return $this->resolvedCommand[$class];
        }
        $matches = array(
            'return' => '',
            'new' => '',
            'class' => trim($class),
            'last' => ''
        );
        if (strrpos($class, ' ')) {  // If we have command request
            preg_match('#^(?<return>(?:)return|)\s*(?<new>(?:)new|)\s*(?<class>[a-zA-Z_\/.:]+)(?<last>.*?)$#', $class, $matches);
        }
        $this->resolvedCommand[$class] = $matches;
        return $matches;
    }

    /**
     * Get class name and key
     * 
     * @param string  $cid      identifier
     * @param boolean $provider is it provider
     *
     * @return array data
     */
    public function getClassInfo($cid, $provider = false)
    {
        if (isset($this->resolved[$cid])) {
            return $this->resolved[$cid];
        }
        $Class = ucfirst($cid);
        $Class = str_replace('/', '\\', trim($Class));  // Replace  all forward slashes "/" backslashes to "\\".

        if ($this->exists($cid) || $this->exists(strtolower($cid)) AND strpos($cid, '/') > 0) {  // If its a provider "/"
            $Class = $cid;
            return $this->resolveNamespace($Class, $cid, '/', true, $provider);
        }
        if (strpos($Class, '\\') > 0) {
            return $this->resolveNamespace($Class, $cid, '\\', true, $provider);
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
     * @param bool   $provider  is it provider
     * 
     * @return string
     */
    protected function resolveNamespace($Class, $key, $separator = '\\', $implode = true, $provider = false)
    {
        $exp = explode($separator, $Class);
        $ClassName = end($exp);
        if ($implode) {
            $exp = $this->mapName($exp);   // Converts "utils/uri" to "utilsUri"
            $key = lcfirst(implode('', $exp));  // First letter must be lowercase
        }
        if ($separator == '/') { // Shared type service
            return $this->resolved[$key] = array('key' => $key, 'cid' => $Class, 'class' => $Class);
        }
        array_pop($exp);
        $nameSpace = 'Obullo' .$separator. implode($separator, $exp).$separator. ucfirst($ClassName);
        if ($provider) {   // Remove Obullo from namepspaces e.g. "Obullo/Service/Provider/Class" => "Service/Provider/Class"
            $nameSpace = substr($nameSpace, 7);
        }
        return $this->resolved[$key] = array('key' => $key, 'cid' => $key, 'class' => $nameSpace);
    }

    /**
     * Gets a parameter or the closure defining an object.
     *
     * @param string $cid The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or the closure defining an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
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
     *
     * @throws InvalidArgumentException if the identifier is not defined or not a service definition
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
     * @param string $class        class key
     * @param string $service      service or provider
     * 
     * @return string class namespace
     */
    protected function resolveServiceClass($serviceClass, $class, $service = 'service')
    {
        if (isset($this->values['config']->env['environment'][$service][$class])) {   // Check environment based services

            $attributes = $this->values['config']->env['environment'][$service][$class];
            $serviceClass = '\\'.str_replace('/', '\\', $attributes['http']);

            if (isset($attributes['cli']) AND defined('STDIN')) {
                $serviceClass = '\\'.str_replace('/', '\\', $attributes['cli']);
            }
        };
        return $serviceClass;
    }

    /**
     * Detects application environment using "app/config/env/environments.php" file.
     * 
     * @return string environment or die if fail
     */
    public function detectEnvironment()
    {
        $hostname = gethostname();
        if (self::$env != null) {
            return self::$env;
        }
        if (in_array($hostname, $this->envArray['env']['production']['server']['hostname'])) {
            return self::$env = 'production';
        }
        if (in_array($hostname, $this->envArray['env']['test']['server']['hostname'])) {
            return self::$env = 'test';
        }
        if (in_array($hostname, $this->envArray['env']['local']['server']['hostname'])) {
            return self::$env = 'local';
        }
        die('We could not detect your application environment, please correct your <b>app/config/environments.php</b> hostname array.');
    }

}

// END Container class

/* End of file Container.php */
/* Location: .Obullo/Container/Container.php */