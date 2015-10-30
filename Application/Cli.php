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
            return new \Obullo\Cli\Router($c['request']->getUri(), $c['logger']);
        };
        include APP .'events.php';

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

        $router = $this->c['router'];
        $router->init();

        $className = $router->getNamespace();

        if (! class_exists($className, false)) {
            $this->router->classNotFound();
        }
        $controller = new $className;  // Call the controller
        $controller->__setContainer($this->c);
        
        if (! method_exists($className, $this->router->getMethod())) {
            $this->router->methodNotFound();
        }
        $arguments = array_slice($this->c['request']->getUri()->getSegments(), 2);

        call_user_func_array(array($controller, $router->getMethod()), $arguments);

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