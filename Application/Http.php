<?php

namespace Obullo\Application;

use Controller;
use Obullo\Config\Env;
use Obullo\Config\Config;
use BadMethodCallException;
use Obullo\Container\Container;
use Obullo\Application\Debugger\WebSocket;

/*
|--------------------------------------------------------------------------
| Php startup error handler
|--------------------------------------------------------------------------
*/
if (error_get_last() != null) {
    include TEMPLATES .'errors'. DS .'startup.php';
}
require OBULLO .'Container'. DS .'Container.php';
require OBULLO .'Config'. DS .'Config.php';

require 'Obullo.php';

/**
 * Container
 * 
 * @var object
 */
$c = new Container;

$c['env'] = function () use ($c) {
    return new Env($c);
};
$c['config'] = function () use ($c) {
    return new Config($c);
};
$c['app'] = function () {
    return new Http;
};
/**
 * Obullo bootstrap
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
 */
class Http extends Obullo
{
    /**
     * Middlewares stack
     * 
     * @var array
     */
    protected $middleware = array();

    /**
     * Debugger
     * 
     * @var boolean
     */
    protected $debugger = false;

    /**
     * Constructor
     *
     * @return void
     */
    public function init()
    {
        global $c;
        $this->c = $c;
        $this->debugger = $this->debuggerOn();

        $this->detectEnvironment();
        $this->setErrorReporting();
        $this->setDefaultTimezone();
        $this->setPhpDebugger();

        $this->middleware = array($this); // Define default middleware stack

        include OBULLO_CONTROLLER;
        include OBULLO_COMPONENTS;
        include OBULLO_PROVIDERS;
        include OBULLO_EVENTS;
        include OBULLO_ROUTES;

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
        global $c;

        $this->init();
        $this->c['router']->init();                 // Initialize Routes
        $route = $this->c['uri']->getUriString();   // Get current uri

        if ($this->c->exists('app.uri')) {                 // If layer used, use global request uri object instead of current.
            $route = $this->c['app']->uri->getUriString();                             
        }
        $class = $this->c['router']->fetchClass();
        $method = $this->c['router']->fetchMethod();
        $namespace = $this->c['router']->fetchNamespace();

        include CONTROLLERS .$this->c['router']->fetchModule(DS).$this->c['router']->fetchDirectory(). DS .$this->c['router']->fetchClass().'.php';

        $this->className = '\\'.$namespace.'\\'.$class;
        $this->notFoundUri = $route;

        $this->dispatchClass();

        $this->class = new $this->className;  // Call the controller
        $this->method = $method;
        $this->parseDocComments();

        $this->dispatchMethod();

        foreach ($this->c['router']->getAttachedRoutes() as $value) {
            if ($value['route'] == $route) {     // if we have natural route match
                $this->middleware($value['name'], $value['options']);
            } elseif (preg_match('#' . str_replace('#', '\#', $value['attachedRoute']) . '#', $route)) {
                $this->middleware($value['name'], $value['options']);
            }
        }
        include OBULLO_MIDDLEWARES;  // Run application middlewares at the top

        $middleware = current($this->middleware);  // Invoke middleware chains using current then each middleware will call next 
        $middleware->load();
        
        if (method_exists($this->class, 'extend')) {      // View traits must be run at the top level otherwise layout view file
            $this->class->extend();                       // could not load view variables.
        }
        $middleware->call();   

        list($status, $headers, $output) = $this->c['response']->finalize();
        $this->c['response']->sendHeaders($status, $headers);

        if ($this->debugger) {
            $output = \Obullo\Application\Debugger\Notice::turnOff($output);
        }
        echo $output; // Send output
    }

    /**
     * Execute the controller
     * 
     * @return void
     */
    public function call()
    {
        if ($this->c['config']['output']['compress'] == true AND extension_loaded('zlib')  // Do we need to output compression ?
            AND isset($_SERVER['HTTP_ACCEPT_ENCODING'])
            AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false
        ) {
            ob_start('ob_gzhandler');
        } else {
            ob_start();
        }
        call_user_func_array(array($this->class, $this->method), array_slice($this->class->uri->rsegments, 3));
    }

    /**
     * Register shutdown
     * 
     * @return void
     */
    public function close()
    {
        // Write queued cookie headers if cookie package available in 
        // the application and we have queued cookies.

        if ($this->c->loaded('cookie') AND count($cookies = $this->c['cookie']->getQueuedCookies()) > 0) {
            foreach ($cookies as $cookie) {
                $this->c['cookie']->write($cookie);
            }
        }
        $this->checkDebugger();
    }

    /**
     * Check http debugger is active ?
     * 
     * @return void
     */
    public function checkDebugger()
    {
        $debug = $this->debuggerOn();

        if ($debug AND ! isset($_REQUEST[FRAMEWORK.'_debugger'])) {
            $websocket = new WebSocket($this->c);
            $websocket->emit();
        }
    }

}

// END Http.php File
/* End of file Http.php

/* Location: .Obullo/Application/Http.php */