<?php

namespace Obullo\Application;

use Closure;
use Controller;
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
        $this->restoreErrors();
    }

    /**
     * Restore application error configuration
     * 
     * @return void
     */
    public function restoreErrors()
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
        $route = $this->c['uri']->getUriString();          // Get current uri
        if ($this->c->exists('app.uri')) {                 // If layer ( hmvc ) used, use global request uri object instead of current.
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
     * method.
     * 
     * @param string $key application object
     * 
     * @return object
     */
    public function __get($key)
    {
        $cid = 'app.'.$key;
        if ( ($key == 'uri' OR $key == 'router') AND $this->c->exists($cid) ) {
            return $this->c[$cid];
        }
        return Controller::$instance->{$key};
    }

}

// END Obullo.php File
/* End of file Obullo.php

/* Location: .Obullo/Application/Obullo.php */