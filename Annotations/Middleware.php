<?php

namespace Obullo\Annotations;

use Obullo\Event\EventInterface;
use Obullo\Application\ApplicationInterface;
use Obullo\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Annotations Middleware Class
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Middleware
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Application
     * 
     * @var object
     */
    protected $app;

    /**
     * Event
     * 
     * @var object
     */
    protected $event;

    /**
     * When counter
     * 
     * @var array
     */
    protected $when = [];

    /**
     * Container
     * 
     * @var object
     */
    protected $count = 0;

    /**
     * Http request
     * 
     * @var string
     */
    protected $request;

    /**
     * Constructor
     * 
     * @param ContainerInterface     $c       container
     * @param ServerRequestInterface $request request
     * @param ApplicationInterface   $app     app
     * @param EventInterface         $event   event
     */
    public function __construct(ContainerInterface $c, ServerRequestInterface $request, ApplicationInterface $app, EventInterface $event)
    {
        $this->c = $c;
        $this->app = $app;
        $this->event = $event;
        $this->request = $request;
    }

    /**
     * Add new middleware(s)
     * 
     * @param mixed $middleware name
     * 
     * @return object
     */
    public function add($middleware)
    {
        if (! is_array($middleware)) {      // Do we have any possible parameters ?
            $middleware = array($middleware);
        }
        $allowedMethods = end($this->when);  // Get the last used when method values
        $when = count($this->when);

        if ($when > 0 && in_array($this->request->getMethod(), $allowedMethods)) {
            $this->addMiddleware($middleware);
            $this->when = array();  // reset when
            return $this;
        } elseif ($when == 0) {
            $this->addMiddleware($middleware);
        }
        return $this;
    }

    /**
     * Remove middleware(s)
     * 
     * @param mixed $middleware name
     * 
     * @return object
     */
    public function remove($middleware)
    {
        if (! is_array($middleware)) {      // Do we have any possible parameters ?
            $middleware = array($middleware);
        }
        foreach ($middleware as $name) {
            $this->c['middleware']->remove(ucfirst($name));
        }
    }

    /**
     * Initialize to after filters
     * 
     * @param string|array $params http method(s): ( post, get, put, delete )
     * 
     * @return object
     */
    public function when($params)
    {
        if (is_string($params)) {
            $params = array($params);
        }
        $this->when[] = $params;
        return $this;
    }

    /**
     * Initialize to allowed methods filters
     * 
     * @param string|array $params parameters
     * 
     * @return void
     */
    public function method($params = null)
    {
        if (is_string($params)) {
            $params = array($params);
        }
        $this->c['middleware']->add('NotAllowed')->setMethods($params);
        return;
    }

    /**
     * Subscribe to events
     *
     * @param string $namespace event subscribe listener
     * 
     * @return void
     */
    public function subscribe($namespace)
    {
        $Class = '\\'.ltrim($namespace, '\\');
        $allowedMethods = end($this->when);  // Get the last used when method values
        $when = count($this->when);

        if ($when > 0 && in_array($this->request->getMethod(), $allowedMethods)) {
            $this->event->subscribe(new $Class($this->c));
            $this->when = array();  // Reset when
            return $this;
        } elseif ($when == 0) {
            $this->event->subscribe(new $Class($this->c));
        }
    }

    /**
     * Add middlewares to application
     * 
     * @param array $middlewares names
     * 
     * @return void
     */
    protected function addMiddleware(array $middlewares)
    {
        foreach ($middlewares as $name) {
            $this->c['middleware']->add(ucfirst($name));
        }
    }
    
}