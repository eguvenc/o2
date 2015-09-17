<?php

namespace Obullo\Application;

use Closure;
use Exception;
use Controller;
use ErrorException;
use ReflectionClass;
use RuntimeException;
use ReflectionFunction;
use Obullo\Error\Debug;

/**
 * Run Application
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
 */
class Application
{
    const VERSION = 'alpha-2.4';

    protected $env = null;         // Current environment
    protected $envArray = array(); // Environments config
    protected $config;             // Config object
    protected $exceptions = array();
    protected $fatalError;

    /**
     * Dependency map
     * 
     * @var array
     */
    protected static $dependencies = [
            'app',
            'uri',
            'config',
            'router',
            'logger',
            'session',
            'request',
            'response',
            'translator',
        ];
        
    /**
     * Detects application environment using "app/environments.php" file.
     * 
     * @return void or die if fail
     */
    protected function detectEnvironment()
    {
        $hostname = gethostname();
        if ($this->env != null) {
            return;
        }
        $this->envArray = include ROOT .'app'. DS .'environments.php';
        foreach ($this->environments() as $current) {
            if (in_array($hostname, $this->envArray[$current])) {
                $this->env = $current;
                break;
            }
        }
        if ($this->env == null) {
            die('We could not detect your application environment, please correct your app/environments.php hostnames.');
        }
        $this->c->setEnv($this->env);
    }
    
    /**
     * Enable / Disable php error reporting
     *
     * @return void
     */
    public function setErrorReporting()
    {
        if ($this->c['config']['error']['debug'] == false) {
            error_reporting(E_ALL | E_STRICT | E_NOTICE);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
        }
    }

    /**
     * Set php date default timezone
     *
     * @return void
     */
    protected function setDefaultTimezone()
    {
        date_default_timezone_set($this->c['config']['locale']['date']['php_date_default_timezone']);   //  Set Default Time Zone Identifer. 
    }

    /**
     * Set framework debugger
     *
     * @return void
     */
    protected function setPhpDebugger()
    {
        if ($this->c['config']['error']['debug']) {  // If framework debug feature enabled we register error & exception handlers.
            Debug::enable(E_ALL | E_NOTICE | E_STRICT);
        }        
    }
    
    /**
     * Sets application exception errors
     * 
     * @param Closure $closure function
     * 
     * @return void
     */
    public function error(Closure $closure)
    {
        $reflection = new ReflectionFunction($closure);
        $parameters = $reflection->getParameters();
        if (isset($parameters[0])) {
            $this->exceptions[] = array('closure' => $closure, 'exception' => $parameters[0]->getClass());
        }
    }

    /**
     * Sets application fatal errors
     * 
     * @param Closure $closure function
     * 
     * @return void
     */
    public function fatal(Closure $closure)
    {
        $this->fatalError = $closure;
    }

    /**
     * Error handler, convert all errors to exceptions
     * 
     * @param integer $level   name
     * @param string  $message error message
     * @param string  $file    file
     * @param integer $line    line
     * 
     * @return boolean whether to continue displaying php errors
     */
    public function handleError($level, $message, $file = '', $line = 0)
    {
        return $this->handleException(new ErrorException($message, $level, 0, $file, $line));
    }

    /**
     * Exception error handler
     * 
     * @param Exception $e exception class
     * 
     * @return boolean
     */
    public function handleException(Exception $e)
    {
        $return = false;
        foreach ($this->exceptions as $val) {
            if ($val['exception']->isInstance($e)) {
                $return = $val['closure']($e);
            }
        }
        return $return;
    }

    /**
     * Set error handlers
     *
     * @return void
     */
    public function registerErrorHandlers()
    {
        if ($this->c['config']['error']['debug'] == false) {  // If debug "disabled" from config use app handler otherwise use Error/Debug package.
            set_error_handler(array($this, 'handleError'));
            set_exception_handler(array($this, 'handleException'));
        }
    }

    /**
     * Register fatal error handler
     * 
     * @return mixed
     */
    public function registerFatalError()
    {
        $closure = $this->fatalError;
        if (null != $error = error_get_last()) {  // If we have a fatal error
            $closure(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
            $this->c['logger']->shutdown();       // Close the logger if have fatal error otherwise log writers not write.
        }
    }

    /**
     * Parse annotations
     * 
     * @return void
     */
    protected function dispatchAnnotations()
    {
        if ($this->c['config']['controller']['annotations']) {
            $docs = new \Obullo\Annotations\Controller($this->c, $this->c['response'], $this->class, $this->c['router']->fetchMethod());
            $docs->parse();
        }
    }

    /**
     * Is Cli ?
     *
     * Test to see if a request was made from the command line.
     *
     * @return bool
     */
    public function isCli()
    {
        return (PHP_SAPI === 'cli' || defined('STDIN'));
    }

    /**
     * Returns to detected environment
     * 
     * @return string
     */
    public function env()
    {
        return $this->env;
    }

    /**
     * Returns to all environment names
     * 
     * @return array
     */
    public function environments()
    {
        return array_keys($this->envArray);
    }

    /**
     * Returns to all environments data
     * 
     * @return array
     */
    public function envArray()
    {
        return $this->envArray;
    }

    /**
     * Returns to valid environment path
     * 
     * @return string
     */
    public function envPath()
    {
        return CONFIG . $this->env() . DS;
    }

    /**
     * Register components & resolve dependencies
     *
     * @param array $namespaces component class name & namespaces
     * 
     * @return void
     */
    public function component(array $namespaces)
    {   
        foreach ($namespaces as $name => $Class) {
            $this->c[$name] = function () use ($Class, $name) {
                $Class = '\\'.ltrim($Class, '\\');
                $reflector = new ReflectionClass($Class);
                if (! $reflector->hasMethod('__construct')) {
                    return $reflector->newInstance();
                } else {
                    return $reflector->newInstanceArgs($this->getDependencies($reflector, $name));
                }
            };
        }
    }

    /**
     * Parse dependency parameters
     * 
     * @param array  $reflector ReflectionClass
     * @param string $component name
     * 
     * @return array
     */
    protected function getDependencies(ReflectionClass $reflector, $component)
    {
        $parameters = $reflector->getConstructor()->getParameters();
        $params = array();
        foreach ($parameters as $parameter) {
            $d = $parameter->getName();
            if ($d == 'c') {
                $params[] = $this->c;
            } else {
                if (in_array($d, static::$dependencies)) {
                    $params[] = $this->c[$d];
                } else {
                    throw new RuntimeException(
                        sprintf(
                            'Dependency is missing for "%s" package. <pre>%s $%s</pre>',
                            $component,
                            $parameter->getClass()->name,
                            $d
                        )
                    );
                }
            }
        }
        return $params;
    }
    
    /**
     * Register provider
     * 
     * @param string $provider name
     * 
     * @return Obullo\Container\Contaiiner
     */
    public function register($provider)
    {
        return $this->c->register($provider);
    }

    /**
     * Check class is registered as service
     * 
     * @param string $cid class key
     * 
     * @return boolean
     */
    public function hasService($cid)
    {
        return $this->c->hasService($cid);
    }

    /**
     * Check provider is registered
     * 
     * @param string $name provider key like cache, redis, memcache
     * 
     * @return boolean
     */
    public function hasProvider($name)
    {
        return $this->c->hasProvider($name);
    }

    /**
     * Load service provider
     * 
     * @param string $name provider name
     * 
     * @return object
     */
    public function provider($name)
    {
        return $this->c->provider($name);
    }

    /**
     * Returns current version of Obullo
     * 
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }
    
    /**
     * Call controller methods from view files
     *
     * View files $this->method(); support.
     * 
     * @param string $method    called method
     * @param array  $arguments called arguments
     * 
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if ($method == 'extend') {
            return;
        }
        return Controller::$instance->$method($arguments);
    }

    /**
     * Returns 
     *
     * This function similar with Codeigniter getInstance(); 
     * instead of getInstance()->class->method() we use $this->c['app']->class->metod();
     * 
     * @param string $key application object
     * 
     * @return object
     */
    public function __get($key)
    {
        $cid = 'app.'.$key;
        if ($this->c->has($cid) ) {
            return $this->c[$cid];
        }
        if (class_exists('Controller', false) && Controller::$instance != null) {
            return Controller::$instance->{$key};
        }
        return $this->c[$key];
    }

}