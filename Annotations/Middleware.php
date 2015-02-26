<?php

namespace Obullo\Annotations;

use Controller;
use Obullo\Container\Container;

/**
 * Annotations Middleware Class
 * 
 * @category  Annotations
 * @package   Filter
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
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->count = 0;
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get';
        $this->httpMethod = strtolower($method);
    }

    /**
     * Initialize to before filters
     * 
     * @param mixed $name name
     * 
     * @return object
     */
    public function assign($name)
    {
        $middleware = $name;
        $params = array();
        if (is_array($name)) {      // Do we have any possible parameters ?
            $middleware = $name[0];
            array_shift($name);
            $params = $name;
        }
        $allowedMethods = end($this->when);
        if (count($allowedMethods) > 0 AND in_array($this->httpMethod, $allowedMethods)) {
            $this->c['app']->middleware($middleware, $params);
            $this->when = array();  // reset when
        }
        if (count($allowedMethods) == 0) {
            $this->c['app']->middleware($middleware, $params);
        }
        return $this;
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
        $this->c['event']->subscribe(new $Class($this->c));
    }

}

// END Filter.php File
/* End of file Filter.php

/* Location: .Obullo/Application/Filter.php */