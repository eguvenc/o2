<?php

namespace Obullo\Application;

use Closure;
use Exception;
use Controller;
use ErrorException;
use ReflectionFunction;
use Obullo\Error\Debug;

/**
 * Run Application
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
 */
class Application
{
    const VERSION = '2.0@alpha-2';

    protected $c;                  // Container
    protected $env = null;         // Current environment
    protected $envArray = array(); // Environments config
    protected $class;              // Current controller
    protected $method;             // Current method
    protected $className;          // Current controller name
    protected $websocket;          // Debugger websocket
    protected $exceptions = array();
    protected $fatalError;

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
            die('We could not detect your application environment, please correct your <b>app/environments.php</b> hostnames.');
        }
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
            $this->c['logger']->close();          // Close the logger if have fatal error otherwise log writers not write to drivers
        }
    }

    /**
     * PSR-0 Autoloader
     * 
     * @param string $realname classname 
     *
     * @see http://www.php-fig.org/psr/psr-0/
     * 
     * @return void
     */
    public static function autoload($realname)
    {
        if (class_exists($realname, false)) {  // Don't use autoloader
            return;
        }
        $className = ltrim($realname, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className);

        if (strpos($fileName, 'Obullo') === 0) {     // Check is it Obullo Package ?
            include_once OBULLO .substr($fileName, 7). '.php';
            return;
        }
        include_once CLASSES .$fileName. '.php'; // Otherwise load it from user directory
    }

    /**
     * Register Slim's PSR-0 autoloader
     * 
     * @return void
     */
    public static function registerAutoloader()
    {
        spl_autoload_register(__NAMESPACE__ . "\\Application::autoload");
    }

    /**
     * Parse annotations
     * 
     * @return void
     */
    protected function dispatchAnnotations()
    {
        if ($this->c['config']['annotations']['enabled']) {
            $docs = new \Obullo\Annotations\Controller($this->c, $this->class, $this->method);
            $docs->parse();
        }
    }

    /**
     * Check class exists
     * 
     * @return void
     */
    protected function dispatchClass()
    {
        if ( ! class_exists($this->className, false)) {
            $this->c['response']->show404($this->getCurrentRoute());
        }
    }

    /**
     * Check method exists
     * 
     * @return void
     */
    protected function dispatchMethod()
    {
        if ( ! method_exists($this->class, $this->method) OR $this->method == 'load' OR $this->method == 'extend') { // load method reserved
            $this->c['response']->show404($this->getCurrentRoute());
        }
    }

    /**
     * Returns to valid site route
     * 
     * @return string
     */
    protected function getCurrentRoute()
    {
        $route = $this->c['uri']->getUriString();       // Get current uri
        if ($this->c->has('app.uri')) {                 // If layer ( hmvc ) used, use global request uri object instead of current.
            $route = $this->c['app']->uri->getUriString();                             
        }
        return $route;
    }

    /**
     * Is Cli ?
     *
     * Test to see if a request was made from the command line.
     *
     * @return  bool
     */
    public function isCli()
    {
        return (PHP_SAPI === 'cli' OR defined('STDIN'));
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
        return APP .'config'. DS . $this->env() . DS;
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
     * Load service provider
     * 
     * @param string $name provider name
     * 
     * @return object
     */
    public function provider($name)
    {
        return $this->c->resolveProvider($name);
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
        if ( ($key == 'uri' OR $key == 'router') AND $this->c->has($cid) ) {
            return $this->c[$cid];
        }
        if (class_exists('Controller', false) && Controller::$instance != null) {
            return Controller::$instance->{$key};
        }
        return $this->c[$cid];
    }

}

// END Obullo.php File
/* End of file Obullo.php

/* Location: .Obullo/Application/Obullo.php */