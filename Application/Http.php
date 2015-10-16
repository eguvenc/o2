<?php

namespace Obullo\Application;

use Controller;
use Obullo\Debugger\WebSocket;

/*
|--------------------------------------------------------------------------
| Php startup error handler
|--------------------------------------------------------------------------
*/
if (error_get_last() != null) {
    include TEMPLATES .'errors/startup.php';
}
/**
 * Http Application
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Http extends Application
{
    protected $class;                 // Current controller
    protected $method;                // Current method
    protected $response;              // App response
    protected $websocket;             // Debugger websocket
    protected $className;             // Current controller name
    protected $middlewares = array(); // Middleware names

    /**
     * Constructor
     *
     * @return void
     */
    public function init()
    {
        $c = $this->c;  // make c global
        $this->setErrorReporting();
        $this->setPhpDebugger();

        include APP .'errors.php';
        $this->registerErrorHandlers();
        include OBULLO .'Controller/Controller.php';
        include APP .'events.php';
        include APP .'routes.php';

        $c['router']->init();
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
            $this->c['router']->clear();
            if ($error404 = $this->c['router']->get404Class()) {
                $this->includeClass();
                echo $this->className = $error404;
            } else {
                $this->c['response']->error404();
            }
        }
    }

    /**
     * Include controller file
     * 
     * @return void
     */
    protected function includeClass()
    {
        include MODULES .$this->c['router']->fetchModule('/').$this->c['router']->fetchDirectory().'/'.$this->c['router']->fetchClass().'.php';
    }

    /**
     * Check method exists
     * 
     * @return void
     */
    protected function dispatchMethod()
    {
        $method = $this->c['router']->fetchMethod();
        if (! method_exists($this->class, $method)
            || substr($method, 0, 1) == '_'
        ) {
            $this->c['response']->error404();
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
        if ($this->c['config']['http']['debugger']['enabled']) {
            $this->websocket = new WebSocket(
                $this->c['app'],
                $this->c['request'],
                $this->c['config']
            );
            $this->websocket->connect();
        }
        $this->includeClass();
        $this->className = '\\'.$this->c['router']->fetchNamespace().'\\'.$this->c['router']->fetchClass();
        $this->dispatchClass();
        $this->class = new $this->className;  // Call the controller
        $this->dispatchMethod();

        // WARNING !:  Read annotations after the attaching middlewares otherwise @middleware->remove() does not work
        // 
        if ($this->c['config']['controller']['annotations']) {
            $docs = new \Obullo\Annotations\Controller($this->c, $this->c['response'], $this->class, $this->c['router']->fetchMethod());
            $docs->parse();
        }
        if (method_exists($this->class, '__invoke')) {    // View traits must be run at the top level
            $invoke = $this->class;
            $invoke();
        }
        $this->dispatchMiddlewares();
    }

    /**
     * Register assigned middlewares
     * 
     * @return void
     */
    protected function dispatchMiddlewares()
    {
        $c = $this->c; // Make available container in middleware.php
        $currentRoute = $this->uri->getUriString();

        include APP .'middlewares.php';

        foreach ($c['router']->getAttachedMiddlewares() as $value) {
            $attachedRoute = str_replace('#', '\#', $value['attachedRoute']);  // Ignore delimiter
            if ($value['route'] == $currentRoute) {     // if we have natural route match
                $object = $c['middleware']->add($value['name']);
            } elseif ($attachedRoute == '.*' || preg_match('#'. $attachedRoute .'#', $currentRoute)) {
                $object = $c['middleware']->add($value['name']);
            }
            if (method_exists($object, 'inject') && ! empty($value['options'])) {  // Inject parameters
                $object->inject($value['options']);
            }
        }
    }

    /**
     * Execute the controller
     * 
     * @return void
     */
    public function call()
    {
        call_user_func_array(
            array(
                $this->class,
                $this->c['router']->fetchMethod()),
            array_slice($this->class->uri->getRoutedSegments(), 3)
        );
    }

    /**
     * Register shutdown
     *
     * 1 . Write cookies if package loaded and we have queued cookies.
     * 2 . Check debugger module
     * 3 . Register fatal error handler
     * 
     * @return void
     */
    public function close()
    {
        if ($this->c->active('cookie') 
            && count($cookies = $this->c['cookie']->getQueuedCookies()) > 0
        ) {
            foreach ($cookies as $cookie) {
                $this->c['cookie']->write($cookie);
            }
        }
        $this->closeDebugger();
        $this->registerFatalError();
    }

    /**
     * Check debugger module is enabled ?
     * 
     * @return void
     */
    public function closeDebugger()
    {
        if ($this->c['config']['http']['debugger']['enabled'] 
            && $this->c['uri']->segment(0) != 'debugger'
        ) {
            $this->websocket->emit(
                (string)$this->c['response']->getBody(),
                $this->c['logger']->getPayload()
            );
        }
    }
}