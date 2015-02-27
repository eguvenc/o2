<?php

namespace Obullo\Application;

use Controller;
use Obullo\Config\Env;
use Obullo\Error\Debug;
use Obullo\Config\Config;
use BadMethodCallException;
use Obullo\Container\Container;

require OBULLO .'Container'. DS .'Container.php';
require OBULLO .'Config'. DS .'Config.php';

require 'Obullo.php';

/**
 * Container
 * 
 * @var object
 */
$c = new Container;

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
     * Version
     */
    const VERSION = '2.0';

    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Middlewares stack
     * 
     * @var array
     */
    protected $middleware = array();

    /**
     * Environments config
     * 
     * @var array
     */
    protected $envArray = array();

    /**
     * Constructor
     *
     * @return void
     */
    public function run()
    {
        global $c;
        $this->c = $c;
        $this->envArray = include ROOT .'app'. DS .'environments.php';
        $this->detectEnvironment();

        $c['env'] = function () use ($c) {
            return new Env($c);
        };
        $c['config'] = function () use ($c) {
            return new Config($c);
        };
        if ($c['config']['error']['reporting']) {   // Disable / Ebable Php Native Errors
            ini_set('display_errors', 1);
            error_reporting(E_ALL | E_STRICT | E_NOTICE);
        } else {
            error_reporting(0);
        }
        date_default_timezone_set($c['config']['locale']['date']['php_date_default_timezone']);   //  Set Default Time Zone Identifer. 
        
        if ($c['config']['error']['debug'] AND $c['config']['error']['reporting'] == false) {  // // If framework debug feature enabled we register error & exception handlers.
            Debug::enable(E_ALL | E_NOTICE | E_STRICT);
        }        
        $this->middleware = array($this); // Define default middleware stack

        include OBULLO_CONTROLLER;
        include OBULLO_COMPONENTS;
        include OBULLO_PROVIDERS;
        include OBULLO_EVENTS;
        include OBULLO_ROUTES;
        include OBULLO_MIDDLEWARES;

        $this->exec();
    }

    /**
     * Run
     *
     * This method invokes the middleware stack, including the core application;
     * the result is an array of HTTP status, header, and output.
     * 
     * @return void
     */
    public function exec()
    {
        $this->c['router']->init();       // Initialize Routes

        $route = $this->c['uri']->getUriString();          // Get current uri
        $routes = $this->c['router']->getAttachedRoutes();

        if ($this->c->exists('app.uri')) {
            $route = $this->c['app.uri']->getUriString();  // If layer used, use global request uri object instead of layered.
                                                           // Filters always run once because of we don't init filters in Layer class.
        }
        $module = $this->c['router']->fetchModule();
        $directory = $this->c['router']->fetchDirectory();
        $class = $this->c['router']->fetchClass();
        $method = $this->c['router']->fetchMethod();
        $namespace = $this->c['router']->fetchNamespace();

        include CONTROLLERS .$this->c['router']->fetchModule(DS).$this->c['router']->fetchDirectory(). DS .$this->c['router']->fetchClass().'.php';

        $className = '\\'.$namespace.'\\'.$class;
        $this->notFoundUri = "$module / $directory / $class / $method";

        if ( ! class_exists($className, false)) {
            $this->c['response']->show404($this->notFoundUri);
        }
        $this->class = new $className;  // Call the controller
        $this->method = $method;
        $this->parseDocComments();
        $this->dispatchMethods();

        foreach ($routes as $value) {
            if ($value['route'] == $route) {     // if we have natural route match
                $this->middleware($value['name'], $value['options']);
            } elseif (preg_match('#' . str_replace('#', '\#', $value['attachedRoute']) . '#', $route)) {
                $this->middleware($value['name'], $value['options']);
            }
        }
        $middleware = current($this->middleware);  // Invoke middleware chains using current then each middleware will call next 
        $middleware->load();          
        $middleware->call();          

        $this->c['response']->sendOutput();  //  send headers and echo output
    }

    /**
     * Execute the controller
     * 
     * @return void
     */
    public function call()
    {
        if ($this->c['config']['output']['compress'] == true AND extension_loaded('zlib')
            AND isset($_SERVER['HTTP_ACCEPT_ENCODING'])
            AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false
        ) {
            ob_start('ob_gzhandler');
        } else {
            ob_start();
        }
        call_user_func_array(array($this->class, $this->method), array_slice($this->class->uri->rsegments, 3));
    }

}

// END Http.php File
/* End of file Http.php

/* Location: .Obullo/Application/Http.php */