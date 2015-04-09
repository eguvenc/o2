<?php

namespace Obullo\Application;

use Controller;
use Obullo\Config\Env;
use Obullo\Config\Config;
use BadMethodCallException;
use Obullo\Container\Container;
use Obullo\Application\Debugger\WebSocket;

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
    return new Cli;
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
class Cli extends Obullo
{
    /**
     * Constructor
     *
     * @return void
     */
    public function init()
    {
        if (isset($_SERVER['REMOTE_ADDR'])) die('Access denied');

        global $c;
        $this->c = $c;

        $this->detectEnvironment();
        $this->setErrorReporting();
        $this->setDefaultTimezone();
        $this->setPhpDebugger();

        // Warning : Http middlewares are disabled in Cli mode.

        include OBULLO_CONTROLLER;
        include OBULLO_COMPONENTS;
        include OBULLO_PROVIDERS;
        include OBULLO_EVENTS;
        include OBULLO_ROUTES;
        
        $this->c['translator']->setLocale($this->c['translator']->getDefault());  // Set default translation
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
        $this->c['router']->init();       // Initialize Routes

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

        if (method_exists($this->class, 'load')) {
            $this->class->load();
        }
        if (method_exists($this->class, 'extend')) {      // View traits must be run at the top level otherwise layout view file
            $this->class->extend();                       // could not load view variables.
        }
        $this->call();          

        $this->c['response']->flush();  // Send headers and echo output if output enabled
    }

    /**
     * Call the controller
     * 
     * @return void
     */
    public function call()
    {
        $argumentSlice = 3;
        if ( ! method_exists($this->class, $this->method) OR $this->method == 'load' OR $this->method == 'extend') { // load method reserved
            $argumentSlice = 2;
            $this->c['router']->setMethod('index');    // If we have index method run it in cli mode. This feature enables task functionality.
            $this->method = 'index';
        }
        $this->dispatchMethod();  // Display 404 error if method doest not exist also run extend() method.
        $arguments = array_slice($this->class->uri->rsegments, $argumentSlice);
        
        call_user_func_array(array($this->class, $this->c['router']->fetchMethod()), $arguments);

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

        if ($debug) {
            $websocket = new WebSocket($this->c);
            $websocket->cliHandshake();
        }
    }

}

// END Cli.php File
/* End of file Cli.php

/* Location: .Obullo/Application/Cli.php */