<?php

namespace Obullo\Cli;

use Obullo\Cli\UriInterface;
use Obullo\Log\LoggerInterface;

/**
 * Cli Router Class ( ! Warning : Midllewares & Layers Disabled in CLI mode )
 * 
 * @category  Router
 * @package   Router
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/router
 */
class Router
{
    protected $logger;                         // Logger class
    protected $class = '';                     // Controller class name
    protected $routes = array();               // Routes config
    protected $method = 'index';               // Default method
    protected $directory = '';                 // Directory name
    protected $module = '';                    // Module name
    protected $defaultController = 'welcome';  // Default controller name

    protected $uri;
    protected $HOST;                // Host address user.example.com
    protected $DOMAIN;              // Current domain name

    /**
     * Constructor
     * 
     * Runs the route mapping function.
     * 
     * @param object $uri    \Obullo\Cli\UriInterface
     * @param array  $logger \Obullo\Log\LoggerInterface
     */
    public function __construct(UriInterface $uri, LoggerInterface $logger)
    {
        $this->uri = $uri;
        $this->logger = $logger;

        $this->parseCli();
        $this->logger->debug('Cli Router Class Initialized', array('host' => $this->HOST), 9998);
    }

    /**
     * Parse command line interface uri
     * 
     * @return void
     */
    protected function parseCli()
    {
        $this->uri->init();
        $this->setCliHeaders($this->uri->getUriString(false));
    }

    /**
     * Returns to console uri string
     * 
     * @return string
     */
    public function getUriString()
    {
        return $this->uriString;
    }

    /**
     * Set fake headers for cli
     *
     * @param string $uriString valid uri
     * 
     * @return void
     */
    protected function setCliHeaders($uriString)
    {        
        $this->uriString = $uriString;
        if ($host = $this->uri->argument('host')) {
            $this->HOST = $this->DOMAIN = $host;
        }
        $_SERVER['HTTP_USER_AGENT'] = 'Cli';       /// Define cli headers for any possible isset errors.
        $_SERVER['HTTP_ACCEPT_CHARSET'] = 'utf-8';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_HOST'] = $this->HOST;
        $_SERVER['ORIG_PATH_INFO'] = $_SERVER['QUERY_STRING'] = $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'] = $uriString;
    }

    /**
     * Clean all data for Layers
     *
     * @return void
     */
    public function clear()
    {
        $this->class = '';
        $this->directory = '';
        $this->module = '';
    }

    /**
     * Set the route mapping ( Access must be public for Layer Class. )
     *
     * This function determines what should be served based on the URI request,
     * as well as any "routes" that have been set in the routing config file.
     *
     * @return void
     */
    public function init()
    {
        if ($this->getUriString() == '') {     // Is there a URI string ? // If not, the default controller specified in the "routes" file will be shown.
            $segments = $this->validateRequest(explode('/', $this->defaultController));  // Turn the default route into an array.
            $this->setClass($segments[1]);
            $this->setMethod('index');
            $this->logger->debug('No URI present. Default controller set.');
            return;
        }
        $this->setRequest($this->explodeSegments());  // If we got this far it means we didn't encounter a matching route so we'll set the site default route
    }

    /**
     * Explode the URI Segments. The individual segments will
     * be stored in the $this->segments array.
     *
     * @return void
     */
    public function explodeSegments()
    {
        $segments = array();
        foreach (explode('/', $this->getUriString()) as $val) {
            $val = trim($val);
            if ($val != '') {
                $segments[] = $val;
            }
        }
        return $segments;
    }

    /**
     * Detect class && method names
     *
     * This function takes an array of URI segments as
     * input, and sets the current class/method
     *
     * @param array $segments segments
     * 
     * @return void
     */
    public function setRequest($segments = array())
    {
        $segments = $this->validateRequest($segments);
        if (count($segments) == 0) {
            return;
        }
        $this->setClass($segments[1]);
        if (! empty($segments[2])) {
            $this->setMethod($segments[2]); // A standard method request
        } else {
            $segments[2] = 'index'; // This lets the "routed" segment array identify that the default index method is being used.
            $this->setMethod('index');
        }
    }

    /**
     * Validates the supplied segments. Attempts to determine the path to
     * the controller.
     *
     * Segments:  0 = directory, 1 = controller, 2 = method
     *
     * @param array $segments uri segments
     * 
     * @return array
     */
    public function validateRequest($segments)
    {
        if (! isset($segments[0])) {
            return $segments;
        }
        array_unshift($segments, 'tasks');

        $this->setDirectory($segments[0]);      // Set first segment as default "top" directory 
        $segments  = $this->detectModule($segments);
        $directory = $this->fetchDirectory();   // if segment no = 1 exists set first segment as a directory 

        if (! empty($segments[1]) && file_exists(MODULES .$this->fetchModule(DS).$directory. DS .self::ucwordsUnderscore($segments[1]).'.php')) {
            return $segments;
        }
        if (file_exists(MODULES .$directory. DS .self::ucwordsUnderscore($directory). '.php')) {  // if segments[1] not exists. forexamle http://example.com/welcome
            array_unshift($segments, $directory);
            return $segments;
        }
        return $this->show404();
    }

    /**
     * Task not found
     *
     * @param mixed $page null
     * 
     * @return string
     */
    protected function show404($page = null)
    {
        $task = (empty($page)) ? $this->getUriString() : $page;
        return '[Task Not Found]: The task file' .$task. ' you requested was not found.'."\n";
    }

    /**
     * Check first segment if have a module set module name
     * 
     * @param array $segments uri segments
     * 
     * @return array
     */
    protected function detectModule($segments)
    {
        if (isset($segments[1])
            && strtolower($segments[1]) != 'view'  // http://example/debugger/view/index bug fix
            && is_dir(MODULES .$segments[0]. DS . $segments[1]. DS)  // Detect Module and change directory !!
        ) {
            $this->setModule($segments[0]);
            $this->setDirectory($segments[1]);
            array_shift($segments);
        }
        return $segments;
    }

    /**
     * Set the class name
     * 
     * @param string $class classname segment 1
     *
     * @return object Router
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Set current method
     * 
     * @param string $method name
     *
     * @return object Router
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Fetch the current routed class name
     *
     * @return string
     */
    public function fetchClass()
    {
        $class = self::ucwordsUnderscore($this->class);
        return $class;
    }

    /**
     * Set the directory name
     *
     * @param string $directory directory
     * 
     * @return object Router
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * Sets stop directory http://example.com/api/user/delete/4
     * 
     * @param string $directory sets top directory
     *
     * @return void
     */
    public function setModule($directory)
    {
        $this->module = $directory;
    }

    /**
     * Get module directory
     *
     * @param string $separator directory seperator
     * 
     * @return void
     */
    public function fetchModule($separator = '')
    {
        return ( ! empty($this->module)) ? filter_var($this->module, FILTER_SANITIZE_SPECIAL_CHARS).$separator : '';
    }

    /**
     * Fetch the directory (if any) that contains the requested controller class
     *
     * @return string
     */
    public function fetchDirectory()
    {
        return filter_var($this->directory, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * Returns to current method
     * 
     * @return string
     */
    public function fetchMethod()
    {
        return $this->method;
    }

    /**
     * Returns php namespace of the current route
     * 
     * @return string
     */
    public function fetchNamespace()
    {
        $namespace = self::ucwordsUnderscore($this->fetchModule()).'\\'.self::ucwordsUnderscore($this->fetchDirectory());
        $namespace = trim($namespace, '\\');
        return $namespace;
    }

    /**
     * Replace underscore to spaces to use ucwords
     * 
     * Before : widgets\tutorials a  
     * After  : Widgets\Tutorials_A
     * 
     * @param string $string namespace part
     * 
     * @return void
     */
    protected static function ucwordsUnderscore($string)
    {
        $str = str_replace('_', ' ', $string);
        $str = ucwords($str);
        return str_replace(' ', '_', $str);
    }

    /**
     * Get domain which is configured in your routes.php
     * 
     * @return string
     */
    public function getDomain()
    {
        return $this->DOMAIN;
    }

    /**
     * Get currently worked domain name
     * 
     * @return string
     */
    public function getHost()
    {
        return $this->HOST;
    }

}