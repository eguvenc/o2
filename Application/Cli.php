<?php

namespace Obullo\Application;

/**
 * Run Cli Application ( Warning : Http middlewares & Layers disabled in Cli mode.)
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Cli extends Application
{
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

        $c = $this->c;
        $this->setErrorReporting();
        $this->setPhpDebugger();
        
        include APP .'errors.php';
        $this->registerErrorHandlers();
        unset($c['router']);   // Replace Uri & Router components

        $c['router'] = function () use ($c) {
            return new \Obullo\Cli\Router($c['uri'], $c['logger']);
        };
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
        if (! method_exists($this->class, $this->router->getMethod())) {
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
        $this->className = $this->router->getNamespace();
        $this->dispatchClass();
        $this->class = new $this->className;  // Call the controller
        $this->call();
    }

    /**
     * Call the controller
     * 
     * @return void
     */
    public function call()
    {
        $this->dispatchMethod();  // Display 404 error if method doest not exist
        $arguments = array_slice($this->c['uri']->getSegments(), 2);

        call_user_func_array(array($this->class, $this->router->getMethod()), $arguments);

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