<?php

namespace Obullo\Application;

use Obullo\Http\Middleware\ParamsAwareInterface;
use Obullo\Http\Middleware\ControllerAwareInterface;
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

        // $uriString = $this->c['uri']->getUriString(); // Assign route middlewares
        $uriString = $this->c['request']->getUri()->getUriString();

        foreach ($this->c['router']->getAttachedMiddlewares() as $value) {

            $attachedRoute = str_replace('#', '\#', $value['attachedRoute']);  // Ignore delimiter

            if ($value['route'] == $uriString) {     // if we have natural route match
                $object = $middleware->queue($value['name']);
            } elseif ($attachedRoute == '.*' || preg_match('#'. $attachedRoute .'#', $uriString)) {
                $object = $middleware->queue($value['name']);
            }
            if ($object instanceof ParamsAwareInterface && ! empty($value['options'])) {  // Inject parameters
                $object->inject($value['options']);
            }
        }
    }

    /**
     * Execute the controller
     *
     * @param Psr\Http\Message\ResponseInterface $response response
     * 
     * @return mixed
     */
    public function call(Response $response)
    {
        $router = $this->c['router'];
        $middleware = $this->c['middleware'];

        include MODULES .$router->getModule('/').$router->getDirectory().'/'.$router->getClass().'.php';
        $className = '\\'.$router->getNamespace().'\\'.$router->getClass();
        
        $check404Error = $this->dispatchController($className, $router);
        if (! $check404Error) {
            return false;
        }
        $controller = new $className;
        $method = $router->getMethod();
        $check404Error = $this->dispatchMethod($controller, $method);
        if (! $check404Error) {
            return false;
        }
        unset($this->c['response']);
        $this->c['response'] = function () use ($response) {
            return $response;
        };
        foreach ($middleware->getNames() as $name) {
            if ($middleware->has($name) && $middleware->get($name) instanceof ControllerAwareInterface) {
                $middleware->get($name)->inject($controller);
            } 
        }
        $result = call_user_func_array(
            array(
                $controller,
                $method
            ),
            array_slice($controller->request->getUri()->getRoutedSegments(), 3)
        );
        if ($result instanceof Response) {
            $response = $result;
        }
        return $response;   
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