<?php

namespace Obullo\Application;

use Controller;

/**
 * Obullo bootstrap
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
 */
class Obullo
{
    /**
     * Current environment
     * 
     * @var null
     */
    public static $env = null;

    /**
     * Detects application environment using "app/environments.php" file.
     * 
     * @return void or die if fail
     */
    public function detectEnvironment()
    {
        $hostname = gethostname();
        if (self::$env != null) {
            return;
        }
        foreach ($this->getEnvironments() as $current) {
            if (in_array($hostname, $this->envArray[$current])) {
                self::$env = $current;
                break;
            }
        }
        if (self::$env == null) {
            die('We could not detect your application environment, please correct your <b>app/environments.php</b> hostname array.');
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
        if ($fileName == 'Model') {  // Reserved class model
            include_once OBULLO .'Model'. DS .'Model.php';
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
        spl_autoload_register(__NAMESPACE__ . "\\Obullo::autoload");
    }

    /**
     * Add middleware
     *
     * This method prepends new middleware to the application middleware stack.
     * The argument must be an instance that subclasses Slim_Middleware.
     *
     * @param mixed $middleware class name or \Http\Middlewares\Middleware object
     * @param array $params     parameters
     *
     * @return void
     */
    public function middleware($middleware, $params = array())
    {
        if (is_string($middleware)) {
            $Class = '\\Http\\Middlewares\\'.ucfirst($middleware);
            $middleware = new $Class;
        }
        $middleware->params = $params;  //  Inject Parameters
        $middleware->setContainer($this->c);
        $middleware->setApplication($this);
        $middleware->setNextMiddleware(current($this->middleware));
        array_unshift($this->middleware, $middleware);
    }

    /**
     * Parse annotations
     * 
     * @return void
     */
    protected function parseDocComments()
    {
        if ($this->c['config']['annotations']['enabled']) {
            $docs = new \Obullo\Annotations\Controller($this->c, $this->class, $this->method);
            $docs->parse();
        }
    }

    /**
     * Call the controller load method
     * 
     * @return void
     */
    public function load()
    {
        if (method_exists($this->class, 'load')) {        // Check load method is available
            $this->class->load();
        }
    }
    
    /**
     * Check method exists
     * 
     * @return void
     */
    protected function dispatchMethods()
    {
        if ( ! method_exists($this->class, $this->method) OR $this->method == 'load' OR $this->method == 'extend') { // load method reserved
            $this->c['response']->show404($this->notFoundUri);
        }
        if (method_exists($this->class, 'extend')) {      // View traits must be run at the top level otherwise layout view file
            $this->class->extend();                       // could not load view variables.
        }
    }

    /**
     * Returns to detected environment
     * 
     * @return string
     */
    public function getEnv()
    {
        return self::$env;
    }

    /**
     * Returns to all environment names
     * 
     * @return array
     */
    public function getEnvironments()
    {
        return array_keys($this->envArray);
    }

    /**
     * Returns to all environments data
     * 
     * @return array
     */
    public function getEnvArray()
    {
        return $this->envArray;
    }

    /**
     * Returns to valid environment path
     * 
     * @return string
     */
    public function getEnvPath()
    {
        return ENV_PATH;
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