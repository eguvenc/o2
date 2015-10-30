<?php

namespace Obullo\Application;

use Obullo\Http\Middleware\ParamsAwareInterface;
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
        include APP .'events.php';

        $this->boot();

        register_shutdown_function(array($this, 'close'));
    }

    /**
     * Register assigned middlewares
     * 
     * @return void
     */
    protected function boot()
    {
        $c = $this->c; // Make global
        $middleware = $c['middleware'];
        $uriString = $this->c['request']->getUri()->getUriString();

        foreach ($this->c['router']->getAttachedMiddlewares() as $value) {

            $attachedRoute = str_replace('#', '\#', $value['attachedRoute']);  // Ignore delimiter

            if ($value['route'] == $uriString) {     // if we have natural route match
                $object = $middleware->queue($value['name']);
            } elseif ($attachedRoute == '.*' || preg_match('#'. $attachedRoute .'#', $uriString)) {
                $object = $middleware->queue($value['name']);
            }
            if ($object instanceof ParamsAwareInterface && ! empty($value['options'])) {  // Inject parameters
                $object->setParams($value['options']);
            }
        }

        foreach ($middleware->getNames() as $name) {
            echo $name;    
            // if ($middleware->get($name) instanceof ControllerAwareInterface) {
            //     $middleware->get($name)->setController($controller);
            // }
        }


        if ($this->c['config']['http']['debugger']['enabled']) {  // Boot debugger
            $middleware->queue('Debugger');
        }
    }

    /**
     * Execute the controller
     *
     * @param Psr\Http\Message\ResponseInterface $response response
     * 
     * @return mixed
     */
    public function call($controller, $method, Response $response)
    {

    }

    /**
     * Dispatch controller
     * 
     * @param string $file   full path
     * @param object $router 
     * 
     * @return void
     */
    protected function dispatchController($file, $router)
    {
        if (! class_exists($file)) {
            $router->clear();  //  fix layer errors
            return false;
        }
        return true;
    }

    /**
     * Dispatch method
     * 
     * @param object $controller controller
     * @param string $method     method
     * 
     * @return void
     */
    protected function dispatchMethod($controller, $method)
    {
        if (! method_exists($controller, $method)
            || substr($method, 0, 1) == '_'
        ) {
            return false;
        }
        return true;
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
        // $this->closeDebugger();
        $this->registerFatalError();
    }

}