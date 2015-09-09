<?php

namespace Obullo\Application;

use Controller;
use Obullo\Config\Config;
use Obullo\Config\Variable;
use Obullo\Container\Container;

/**
 * Container
 * 
 * @var object
 */
$c = new Container(scandir(APP .'classes'. DS . 'Service'));

$c['var'] = function () use ($c) {
    return new Variable($c);
};
$c['config'] = function () use ($c) {
    return new Config($c);
};
$c['app'] = function () {
    return new Cli;
};
/**
 * Run Cli Application ( Warning : Http middlewares & Layers disabled in Cli mode.)
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
    protected $router;      // Cli router
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

        include APP .'errors.php';
        $this->registerErrorHandlers();
        include OBULLO .'Controller'. DS .'Controller.php';
        include APP .'components.php';
        unset($c['uri'], $c['router']);   // Replace Uri & Router components

        $c['uri'] = function () use ($c) {
            return new \Obullo\Cli\Uri($c['logger']);
        };
        $c['router'] = function () use ($c) {
            return new \Obullo\Cli\Router($c['uri'], $c['logger']);
        };
        include APP .'providers.php';
        include APP .'events.php';

        $this->c['translator']->setLocale($this->c['translator']->getDefault());  // Set default translation
        
        register_shutdown_function(array($this, 'close'));
    }

    /**
     * Check class exists
     * 
     * @return void
     */
    protected function dispatchClass()
    {
        if (! class_exists($this->className, false)) {
            $this->router->classNotFound();
        }
    }

    /**
     * Check method exists
     * 
     * @return void
     */
    protected function dispatchMethod()
    {
        if (! method_exists($this->class, $this->router->fetchMethod())
            || $this->router->fetchMethod() == '__extend'
        ) {
            $this->router->methodNotFound();
        }
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
        $this->router = $this->c['router'];
        $this->router->init();
        $this->className = $this->router->fetchNameSpace();
        $this->dispatchClass();
        $this->class = new $this->className;  // Call the controller
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
        $this->dispatchMethod();  // Display 404 error if method doest not exist
        $arguments = array_slice($this->c['uri']->segmentArray(), 2);

        call_user_func_array(array($this->class, $this->router->fetchMethod()), $arguments);

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