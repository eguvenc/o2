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
        
        $this->c['translator']->setLocale($this->c['translator']->getDefault());  // Set default translation

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

        $this->class->load();   // Disabled middlewares in Cli mode.
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
        $this->dispatchMethod();  // Display 404 error if method not exists also runs extend() method.
        $arguments = array_slice($this->class->uri->rsegments, $argumentSlice);
        
        call_user_func_array(array($this->class, $this->c['router']->fetchMethod()), $arguments);
    }

}

// END Cli.php File
/* End of file Cli.php

/* Location: .Obullo/Application/Cli.php */
