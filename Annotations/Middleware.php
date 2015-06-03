<?php

namespace Obullo\Annotations;

use Obullo\Container\ContainerInterface;

/**
 * Annotations Middleware Class
 * 
 * @category  Annotations
 * @package   Middleware
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/annotations
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
     * Event
     * 
     * @var object
     */
    protected $event;

    /**
     * Container
     * 
     * @var object
     */
    protected $count = 0;

    /**
     * When counter
     * 
     * @var array
     */
    protected $when = array();

    /**
     * Http method name
     * 
     * @var string
     */
    protected $httpMethod = 'get';

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->httpMethod = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';
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
        if ( ! is_array($middleware)) {      // Do we have any possible parameters ?
            $middleware = array($middleware);
        }
        $allowedMethods = end($this->when);  // Get the last used when method values
        $when = count($this->when);

        if ($when > 0 && in_array($this->httpMethod, $allowedMethods)) {
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
        if ( ! is_array($middleware)) {      // Do we have any possible parameters ?
            $middleware = array($middleware);
        }
        foreach ($middleware as $name) {
            $this->c['app']->remove(ucfirst($name));
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
        $this->c['app']->middleware('MethodNotAllowed', $params);
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

        if ($when > 0 && in_array($this->httpMethod, $allowedMethods)) {
            $this->c['event']->subscribe(new $Class($this->c));
            $this->when = array();  // Reset when
            return $this;
        } elseif ($when == 0) {
            $this->c['event']->subscribe(new $Class($this->c));
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
            $this->c['app']->middleware(ucfirst($name));
        }
    }

}

// END Middleware.php File
/* End of file Middleware.php

/* Location: .Obullo/Application/Middleware.php */