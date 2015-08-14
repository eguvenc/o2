<?php

namespace Obullo\Application;

use Controller;
use Obullo\Config\Env;
use Obullo\Config\Config;
use Obullo\Container\Container;

/**
 * Container
 * 
 * @var object
 */
$c = new Container;

$c['env'] = function () use ($c) {
    return new Env($c);
};
$c['config'] = function () use ($c) {
    return new Config($c);
};
$c['app'] = function () {
    return new Cli;
};
/**
 * Run Cli Application
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
 */
class Cli extends Application
{
    protected $c;           // Container
    protected $class;       // Current controller
    protected $method;      // Current method
    protected $className;   // Current controller name

    /**
     * Constructor
     *
     * @return void
     */
    public function init()
    {
        if (isset($_SERVER['REMOTE_ADDR'])) die('Access denied');
        
        global $c;
        $this->c = $c;

        $this->detectEnvironment();
        $this->setErrorReporting();
        $this->setDefaultTimezone();
        $this->setPhpDebugger();

        // Warning : Http middlewares are disabled in Cli mode.

        include APP .'errors.php';
        $this->registerErrorHandlers();
        include OBULLO .'Controller'. DS .'Controller.php';
        
        include APP .'components.php';
        include APP .'providers.php';
        include APP .'events.php';
        include APP .'routes.php';
        
        $this->c['translator']->setLocale($this->c['translator']->getDefault());  // Set default translation
        
        register_shutdown_function(array($this, 'close'));
    }

    /**
     * Run
     *
     * This method invokes the middleware stack, including the core application;
     * the result is an array of HTTP status, header, and output.
     * 
     * @return void
     */
    public function run()
    {    
        $this->init();
        $this->c['router']->init();       // Initialize Routes

        $class = $this->c['router']->fetchClass();
        $method = $this->c['router']->fetchMethod();
        $namespace = $this->c['router']->fetchNamespace();

        include MODULES .$this->c['router']->fetchModule(DS).$this->c['router']->fetchDirectory(). DS .$this->c['router']->fetchClass().'.php';

        $this->className = '\\'.$namespace.'\\'.$class;
        $this->dispatchClass();

        $this->class = new $this->className;  // Call the controller
        $this->method = $method;

        if (method_exists($this->class, '__extend')) {      // View traits must be run at the top level otherwise layout view file
            $this->class->__extend();                       // could not load view variables.
        }
        $this->call();          
        $this->c['response']->flush();  // Send headers and echo output if output enabled
    }

    /**
     * Call the controller
     * 
     * @return void
     */
    public function call()
    {
        $argumentSlice = 3;
        if (! method_exists($this->class, $this->method) || $this->method == '__extend') { // reserved methods
            $argumentSlice = 2;
            $this->c['router']->setMethod('index');    // If we have index method run it in cli mode. This feature enables task functionality.
            $this->method = 'index';
        }
        $this->dispatchMethod();  // Display 404 error if method doest not exist also run extend() method.
        $arguments = array_slice($this->class->uri->routedSegments(), $argumentSlice);
        
        call_user_func_array(array($this->class, $this->c['router']->fetchMethod()), $arguments);

        if (isset($_SERVER['argv'])) {
            $this->c['logger']->debug('php '.implode(' ', $_SERVER['argv']));
        }
        $this->c['logger']->shutdown();  // Manually shutdown logger
    }

    /**
     * Register shutdown
     *
     * 1 . Check debugger module
     * 1 . Write fatal errors
     * 
     * @return void
     */
    public function close()
    {
        $this->registerFatalError();
    }

}