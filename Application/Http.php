<?php

namespace Obullo\Application;

use Obullo\Http\Middleware\ParamsAwareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Obullo\Http\Middleware\ControllerAwareInterface;

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
    // public function call(Response $response)
    // {
    //     $router = $this->c['router'];
    //     $middleware = $this->c['middleware'];

    //     include MODULES .$router->getModule('/').$router->getDirectory().'/'.$router->getClass().'.php';
    //     $className = '\\'.$router->getNamespace().'\\'.$router->getClass();
        
    //     // $check404Error = $this->dispatchController($className, $router);
    //     // if (! $check404Error) {
    //     //     return false;
    //     // }
    //     $controller = new $className;
    //     // $reflector = new \ReflectionClass($controller);
    //     $method = $router->getMethod();  // default index

    //     // if (! $reflector->hasMethod($method)) {
    //     //     $body = $this->c['template']->make('404');
    //     //     return $response->withStatus(404)
    //     //         ->withHeader('Content-Type', 'text/html')
    //     //         ->withBody($body);
    //     // }
    //     // $docs = new \Obullo\Application\Annotations\Controller;
    //     // $docs->setContainer($this->c);
    //     // $docs->setReflectionClass($reflector);
    //     // $docs->setMethod($method);
    //     // $docs->parse();

    //     $controller->__setContainer($this->c);

    //     unset($this->c['response']);
    //     $this->c['response'] = function () use ($response) {
    //         return $response;
    //     };

    //     foreach ($middleware->getNames() as $name) {
    //         // echo $name;    
    //         if ($middleware->get($name) instanceof ControllerAwareInterface) {
    //             $middleware->get($name)->setController($controller);
    //         }
    //     }

    //     // ECHO 'OUTPUT';
    //     $result = call_user_func_array(
    //         array(
    //             $controller,
    //             $method
    //         ),
    //         array_slice($controller->request->getUri()->getRoutedSegments(), 3)
    //     );
    //     if ($result instanceof Response) {
    //         $response = $result;
    //     }
    //     return $response;
    // }

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
        
        if (! class_exists($className)) {
            $router->clear();  // Fix layer errors.
            return false;
        }
        $controller = new $className;
        $method = $router->getMethod();

        if (! method_exists($controller, $method)
            || substr($method, 0, 1) == '_'
        ) {
            return false;
        }
        unset($this->c['response']);
        $this->c['response'] = function () use ($response) {
            return $response;
        };
        $controller->__setContainer($this->c);
        foreach ($middleware->getNames() as $name) {
            if ($middleware->has($name) && $middleware->get($name) instanceof ControllerAwareInterface) {
                $middleware->get($name)->setController($controller);
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
     * Register shutdown
     * 
     * @return void
     */
    public function close()
    {
        $this->registerFatalError();
    }

}