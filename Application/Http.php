<?php

namespace Obullo\Application;

use Controller;
use Obullo\Debugger\WebSocket;

use Psr\Http\Message\ResponseInterface as Response;

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
    protected $websocket;             // Debugger websocket
    protected $className;             // Current controller name

    /**
     * Constructor
     *
     * @return void
     */
    public function init()
    {
        $c = $this->c;  // make global

        $this->setErrorReporting();
        $this->setPhpDebugger();

        include APP .'errors.php';
        include APP .'middlewares.php';

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
        include MODULES .$this->c['router']->getModule('/').$this->c['router']->getDirectory().'/'.$this->c['router']->getClass().'.php';

        $this->className = '\\'.$this->c['router']->getNamespace().'\\'.$this->c['router']->getClass();

        if (! class_exists($this->className, false)) {
            $this->c['router']->clear();
            if ($error404 = $this->c['router']->get404Class()) {
                $this->includeClass();
                $this->className = $error404;
            } else {
                $this->c['middleware']->add('NotFound');
            }
            return;
        }
        $this->class = new $this->className;  // Call the controller
    }

    /**
     * Check method exists
     * 
     * @return void
     */
    protected function dispatchMethod()
    {
        $method = $this->c['router']->getMethod();
        if (! method_exists($this->class, $method)
            || substr($method, 0, 1) == '_'
        ) {
            $this->c['middleware']->add('NotFound');
            // $this->c['response']->error404();
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
        $this->dispatchClass();
        $this->dispatchMethod();
        $this->dispatchMiddlewares();
    }

    /**
     * Register assigned middlewares
     * 
     * @return void
     */
    protected function dispatchMiddlewares()
    {
        $c = $this->c; // Make global
        $middleware = $c['middleware'];

        if ($middleware->has('Annotation')) {
            $middleware->get('Annotation')->inject($this->class, $this->c, $this->c['response']);
        }
        if ($middleware->has('View')) {
            $middleware->get('View')->inject($this->class);
        }
        $middleware->get('Begin')->inject($this->class);

        // Route middlewares

        $uriString = $this->uri->getUriString();
        foreach ($c['router']->getAttachedMiddlewares() as $value) {
            $attachedRoute = str_replace('#', '\#', $value['attachedRoute']);  // Ignore delimiter
            if ($value['route'] == $uriString) {     // if we have natural route match
                $object = $middleware->add($value['name']);
            } elseif ($attachedRoute == '.*' || preg_match('#'. $attachedRoute .'#', $uriString)) {
                $object = $middleware->add($value['name']);
            }
            if (method_exists($object, 'inject') && ! empty($value['options'])) {  // Inject parameters
                $object->inject($value['options']);
            }
        }
    }

    /**
     * Execute the controller
     *
     * @param Psr\Http\Message\ResponseInterface $response response
     * 
     * @return void
     */
    public function call(Response $response)
    {
        unset($this->c['response']);
        $this->c['response'] = function () use ($response) {
            return $response;
        };
        if (empty($this->class)) {
            return $response;
        }
        $result = call_user_func_array(
            array(
                $this->class,
                $this->c['router']->getMethod()
            ),
            array_slice($this->class->uri->getRoutedSegments(), 3)
        );
        if ($result instanceof Response) {
            $response = $result;
        }
        return $response;   
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
        // if ($this->c->active('cookie') 
        //     && count($cookies = $this->c['cookie']->getQueuedCookies()) > 0
        // ) {
        //     foreach ($cookies as $cookie) {
        //         $this->c['cookie']->write($cookie);
        //     }
        // }
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