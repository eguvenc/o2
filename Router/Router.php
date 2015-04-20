<?php

namespace Obullo\Router;

use Closure;
use Controller;
use LogicException;
use BadMethodCallException;
use Obullo\Container\Container;

/**
 * Router Class
 *
 * Modeled after Codeigniter router class.
 * 
 * @category  Router
 * @package   Request
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/router
 */
class Router
{
    public $uri;                            // Uri class
    public $logger;                         // Logger class
    public $class = '';                     // Controller class name
    public $routes = array();               // Routes config
    public $method = 'index';               // Default method
    public $directory = '';                 // Directory name
    public $module = '';                    // Module name
    public $middlewares = array();          // Defined route middlewares
    public $attach = array();               // Attached after routes to middlewares
    public $defaultController = 'welcome';  // Default controller name
    public $pageNotFoundController = '';    // Page not found handler ( 404 )
    public $groupDomain = '*';              // Groupped route domain address

    protected $ROOT;                        // Defined host address in the config file.
    protected $HOST;                        // Host address user.example.com
    protected $DOMAIN;                      // Current domain name
    protected $domainMatches = array();     // Keeps matched domains in cache
    protected $httpMethod = 'get';          // Http methods ( get, post, put, delete )
    protected $group = array('name' => 'UNNAMED', 'domain' => null);       // Group configuration array

    /**
     * Constructor
     * Runs the route mapping function.
     * 
     * @param array $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->uri    = $this->c['uri'];
        $this->logger = $this->c['logger'];
        $this->HOST   = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : null;

        if (defined('STDIN')) {
            $this->HOST = 'Cli';  // Define fake host for Command Line Interface
        }
        if ($this->HOST != 'Cli' AND strpos($this->HOST, $c['config']['url']['webhost']) === false) {
            $this->c['response']->showError('Your host configuration is not correct in the main config file.');
        }
        $this->logger->debug('Router Class Initialized', array('host' => $this->HOST), 7);
    }

    /**
     * Clean all data for Layers
     *
     * @return void
     */
    public function clear()
    {
        $this->uri = $this->c['uri'];   // reset cloned URI object.
        $this->class = '';
        $this->directory = '';
        $this->module = '';
    }

    /**
     * Clone URI object for layers
     * 
     * When "clone" word used in Layer object it use the cloned 
     * URI instead of orginal.
     *
     * @return void
     */
    public function __clone()
    {
        $this->uri = clone $this->uri;
    }

    /**
     * Sets default host
     *
     * @param string $domain your root domain name
     * 
     * @return object Router
     */
    public function domain($domain = '')
    {
        $this->ROOT = trim($domain, '.');
        if (defined('STDIN')) {
            $this->ROOT = 'Cli'; // Define fake domain to Command Line Interface
        }
        return $this;
    }

    /**
     * Set default route
     * 
     * @param string $pageController controller
     * 
     * @return object
     */
    public function defaultPage($pageController)
    {
        $this->defaultController = $pageController;
        return $this;
    }

    /**
     * Error 404 not found controller
     * 
     * @param string $errorController error page
     * 
     * @return object
     */
    public function error404($errorController)
    {
        $this->pageNotFoundController = $errorController;
        return $this;
    }

    /**
     * Defines http $_GET based routes
     * 
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * 
     * @return object router
     */
    public function get($match, $rewrite = null, $closure = null)
    {
        $this->route(array('get'), $match, $rewrite, $closure = null);
        return $this;
    }

    /**
     * Defines http $_POST based routes
     * 
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * 
     * @return object router
     */
    public function post($match, $rewrite = null, $closure = null)
    {
        $this->route(array('post'), $match, $rewrite, $closure = null);
        return $this;
    }

    /**
     * Defines http $_REQUEST['PUT'] based routes
     * 
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * 
     * @return object router
     */
    public function put($match, $rewrite = null, $closure = null)
    {
        $this->route(array('put'), $match, $rewrite, $closure = null);
        return $this;
    }

    /**
     * Defines http $_REQUEST['DELETE'] based routes
     * 
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * 
     * @return object router
     */
    public function delete($match, $rewrite = null, $closure = null)
    {
        $this->route(array('delete'), $match, $rewrite, $closure = null);
        return $this;
    }

    /**
     * Defines multiple http request based routes
     * 
     * @param string $methods http methods
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * 
     * @return object router
     */
    public function match($methods, $match, $rewrite = null, $closure = null)
    {
        $this->route($methods, $match, $rewrite, $closure = null);
        return $this;
    }

    /**
     * Defines http $_REQUEST based routes
     * 
     * @param string $methods method names
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * 
     * @return object router
     */
    public function route($methods, $match, $rewrite = null, $closure = null)
    {
        $domainMatch = $this->detectDomain($this->group);
        $match = trim($match, '/');
        $rewrite = trim($rewrite, '/');

        if ( ! isset($this->group['name'])) {
            $this->group['name'] = 'UNNAMED';
        }
        if ($domainMatch === false AND $this->group['domain'] !== null) {
            return;
        }
        $scheme = (strpos($match, '}') !== false) ? $match : null;

        $subDomain = null;
        if ($this->isSubDomain($this->DOMAIN)) {
            $subDomain = isset($this->group['domain']['regex']) ? $this->group['domain']['regex'] : $this->group['domain'];
        }
        $this->routes[$this->DOMAIN][] = array(
            'group' => $this->group['name'],
            'sub.domain' => $subDomain,
            'when' => $methods, 
            'match' => $match,
            'rewrite' => $rewrite,
            'scheme' => $scheme,
            'closure' => $closure,
        );
        return $this;
    }

    /**
     * Replace route scheme
     * 
     * @param array $replace scheme replacement
     * 
     * @return object
     */
    public function where(array $replace)
    {   
        $count = count($this->routes) - 1;
        if ($count == -1) {
            return;
        };
        $domain = $this->ROOT;
        if ( ! empty($this->routes[$this->DOMAIN][$count]['sub.domain'])) {
            $domain = $this->routes[$this->DOMAIN][$count]['sub.domain'];
        }
        if ($this->DOMAIN == $domain) {
            $scheme = str_replace(array_keys($replace), array_values($replace), $this->routes[$this->DOMAIN][$count]['scheme']);
            $scheme = str_replace(array('{','}'), array('',''), $scheme);
            $this->routes[$this->DOMAIN][$count]['match'] = $scheme;
        }
        return $this;
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
        $this->uri->fetchUriString();  // Detect the complete URI string
        if ($this->uri->getUriString() == '') {     // Is there a URI string ? // If not, the default controller specified in the "routes" file will be shown.

            if ($this->defaultController == '') {   // Set the default controller so we can display it in the event the URI doesn't correlated to a valid controller.
                $message = 'Unable to determine what should be displayed. A default route has not been specified in the routing file.';

                if (isset($_SERVER['LAYER_REQUEST'])) {  // Returns to false if we have Layer connection error.
                    $this->c['response']->setError('@ErrorTemplate@'.$message);
                    return false;
                }
                $this->c['response']->showError($message, 404);
            }
            $segments = $this->validateRequest(explode('/', $this->defaultController));  // Turn the default route into an array.
            if (isset($_SERVER['LAYER_REQUEST']) AND $segments === false) {   // Returns to false if we have Layer connection error.
                return false;  
            }
            $this->setClass($segments[1]);
            $this->setMethod('index');
            $this->uri->rsegments = $segments;  // Assign the segments to the URI class
            $this->logger->debug('No URI present. Default controller set.');
            return;
        }
        $this->uri->removeUrlSuffix();   // Do we need to remove the URL suffix?
        $this->uri->explodeSegments();   // Compile the segments into an array 
        $this->parseRoutes();            // Parse any custom routing that may exist
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
        if ( ! empty($segments[2])) {
            $this->setMethod($segments[2]); // A standard method request
        } else {
            $segments[2] = 'index'; // This lets the "routed" segment array identify that the default index method is being used.
            $this->setMethod('index');
        }
        $this->uri->rsegments = $segments;  // Update our "routed" segment array to contain the segments.    
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
        if ( ! isset($segments[0])) {
            return $segments;
        }
        if (defined('STDIN') AND ! isset($_SERVER['LAYER_REQUEST'])) {  // Command Line Requests
            array_unshift($segments, 'tasks');
        }
        $this->setDirectory($segments[0]); // Set first segment as default "top" directory 

        if (isset($segments[1]) AND is_dir(MODULES .$segments[0]. DS . $segments[1]. DS)  // Detect Module and change directory !!
        ) {
            $this->setModule($segments[0]);
            $this->setDirectory($segments[1]);
            array_shift($segments);
        }
        $directory = $this->fetchDirectory();   // if segment no = 1 exists set first segment as a directory 

        if ( ! empty($segments[1]) AND file_exists(MODULES .$this->fetchModule(DS).$directory. DS .self::ucwordsUnderscore($segments[1]).'.php')) {
            return $segments;
        }
        if (file_exists(MODULES .$directory. DS .self::ucwordsUnderscore($directory). '.php')) {  // if segments[1] not exists. forexamle http://example.com/welcome
            array_unshift($segments, $directory);
            return $segments;
        }
        if ( ! empty($this->pageNotFoundController)) {            // HTTP 404
            $exp = explode('/', $this->pageNotFoundController);   // If we've gotten this far it means that the URI does not correlate to a valid
            $this->setDirectory($exp[0]);                         // controller class.  We will now see if there is an +override
            $this->setClass($exp[1]);
            $this->setMethod(isset($exp[2]) ? $exp[2] : 'index');
            return $exp;
        }
        if (isset($_SERVER['LAYER_REQUEST'])) {
            $this->c['response']->setError('@ErrorTemplate@<b>404 layer not found: </b>'.$this->uri->getUriString());  // Using getError method we show error in Layer package.
            return false;
        }
        $this->c['response']->show404($this->uri->getUriString());
    }

    /**
     * Parse Routes
     *
     * This function matches any routes that may exist in the routes.php file against the URI to
     * determine if the directory/class need to be remapped.
     *
     * @return void
     */
    public function parseRoutes()
    {
        $uri = $this->uri->getUriString();  // Warning !: don't use $this->uri->segments in here instead of use getUriString otherwise
                                            // we could not get url suffix ".html".

        if ( ! isset($this->routes[$this->DOMAIN])) {
            $this->setRequest($this->uri->segments); 
            return;
        }
        $parameters = array();
        foreach ($this->routes[$this->DOMAIN] as $val) {   // Loop through the route array looking for wild-cards

            if (strpos($val['scheme'], '}') !== false) {   // Does we have route Parameters like {id}/{name} ?
                $parametersIndex = preg_split('#{(.*?)}#', $val['scheme']); // Get parameter indexes
                foreach ($parametersIndex as $key => $values) {  // Find parameters we will send it to closure($args)
                    $values = null;
                    $parameters[] = (isset($this->uri->segments[$key])) ? $this->uri->segments[$key] : null;
                }
                $val['scheme'] = preg_replace('#{(.*?)}#', '', $val['scheme']);
            }
            if (static::routeMatch($val['match'], $uri)) {  // Does the route match ?

                if (count($val['when']) > 0) { //  Dynamically add methot not allowed middleware
                    $this->c['app']->middleware('MethodNotAllowed', $val['when']);
                }
                if ( ! empty($val['rewrite']) AND strpos($val['rewrite'], '$') !== false AND strpos($val['match'], '(') !== false) {  // Do we have a back-reference ?
                    $val['rewrite'] = preg_replace('#^' . $val['match'] . '$#', $val['rewrite'], $uri);
                }
                $segments = (empty($val['rewrite'])) ? $this->uri->segments : explode('/', $val['rewrite']);
                $this->setSegments($val['closure'], $segments, $parameters);
                return;
            }
        }
        $this->setRequest($this->uri->segments);  // If we got this far it means we didn't encounter a matching route so we'll set the site default route
    }

    /**
     * Check route is matched if yes returns to true 
     * otherwise false
     * 
     * @param string $match value
     * @param string $uri   current uri
     * 
     * @return boolean
     */
    protected static function routeMatch($match, $uri)
    {
        if ($match == $uri) { // Is there any literal match ? 
            return true;
        }
        if (preg_match('#^' . $match . '$#', $uri)) {  // Is there any regex match ?
            return true;
        }
        return false;
    }

    /**
     * Set segments and run route closures
     * 
     * @param object $closure    function
     * @param array  $segments   segment array
     * @param array  $parameters closure parameters
     *
     * @return void
     */
    protected function setSegments($closure, $segments = array(), $parameters = array())
    { 
        $this->setRequest($segments);
        $this->bind($closure, $parameters, true);
    }

    /**
     * Closure bind function
     * 
     * @param object  $closure         anonymous function
     * @param array   $args            arguments
     * @param boolean $useCallUserFunc whether to use call_user_func_array()
     * 
     * @return void
     */
    public function bind($closure, $args = array(), $useCallUserFunc = false)
    {
        if ( ! is_callable($closure)) {
            return;
        }
        if (Controller::$instance != null) {
            $closure = Closure::bind($closure, Controller::$instance, 'Controller');
        }
        if ($useCallUserFunc) {
            return call_user_func_array($closure, $args);
        }
        return $closure($args);
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
        return self::ucwordsUnderscore($this->class);
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
     * Check domain has sub name
     * 
     * @param string $domain name
     * 
     * @return boolean
     */
    public function isSubDomain($domain)
    {
        $subDomain = $this->getSubDomain($domain);
        return (empty($subDomain)) ? false : true;
    }

    /**
     * Get sub domain e.g. test.example.com returns to "test".
     * 
     * @param string $domain name
     * 
     * @return boolean
     */
    public function getSubDomain($domain)
    {
        return str_replace($domain, '', $this->HOST);
    }

    /**
    * Detect static domain
    * 
    * @param array $options subdomain array
    * 
    * @return void
    */
    public function detectDomain(array $options = array())
    {
        $domain = $this->ROOT;
        if (isset($options['domain'])) {
            $domain = $options['domain'];
        }
        if (is_array($options['domain']) AND isset($options['domain']['regex'])) { // If regex defined
            $domain = $options['domain']['regex'];
        }
        if ($match = $this->matchDomain($domain)) { // If host matched with option['domain'] assign domain as $option['domain']
            $this->DOMAIN = $match;
            return true;                // Regex match.
        }
        return false;  // No regex match.
    }

    /**
     * Store matched domain to cache then fetch if it exists
     * 
     * @param string $domain url
     * 
     * @return array matches
     */
    public function matchDomain($domain)
    {
        if ($domain == $this->HOST) {
            return $domain;
        }
        $key = $domain.$this->HOST;
        if (isset($this->domainMatches[$key])) {
            return $this->domainMatches[$key];
        }
        if (preg_match('#'.$domain.'#', $this->HOST, $matches)) {
            return $this->domainMatches[$key] = $matches[0];
        }
        return false;
    }

    /**
     * Attach route to middleware
     * 
     * @param string $route middleware route
     * 
     * @return object
     */
    public function attach($route)
    {
        $match = $this->detectDomain($this->group);

        // Domain Regex Support, if we have defined domain and not match with host don't run the middleware.
        if (isset($this->group['domain']) AND ! $match) {  // If we have defined domain and not match with host don't run the middleware.
            return;
        }
        $host = str_replace($this->getSubDomain($this->DOMAIN), '', $this->HOST);          // Attach Regex Support

        if ( ! $this->isSubDomain($this->DOMAIN) AND $this->isSubDomain($this->HOST)) {
            $host = $this->HOST;  // We have a problem when the host is subdomain and config domain not. This fix the isssue.
        }
        if ($this->DOMAIN != $host) {
            return;
        }
        if ( ! isset($this->group['domain'])) {
            $this->group['domain'] = $this->ROOT;
        }
        if (isset($this->group['middleware'])) {
            $this->setMiddlewares($this->group['middleware'], $route, $this->group);
            return $this;
        }
        return $this;
    }

    /**
     * Assign middleware(s) to current route
     * 
     * @param mixed $middlewares array|string
     * 
     * @return object
     */
    public function middleware($middlewares)
    {
        $routeLast = end($this->routes[$this->DOMAIN]);
        $route = $routeLast['match'];

        if (is_array($middlewares)) {
            $this->setMiddlewares($middlewares, $route, array());
            return;
        }
        $this->setMiddleware($middlewares, $route, array());
        return $this;
    }

    /**
     * Configure attached middleware
     * 
     * @param array  $middlewares arguments
     * @param string $route       route
     * @param array  $options     arguments
     * 
     * @return void
     */
    protected function setMiddlewares(array $middlewares, $route, $options)
    {
        foreach ($middlewares as $value) {
            $this->setMiddleware($value, $route, $options);
        }
    }

    /**
     * Set middleware
     * 
     * @param string $middleware name
     * @param string $route      curent route
     * @param array  $options    arguments
     *
     * @return void
     */
    protected function setMiddleware($middleware, $route, $options)
    {
        $this->attach[$this->DOMAIN][] = array(
            'name' => $middleware,
            'options' => $options,
            'route' => trim($route, '/'), 
            'attachedRoute' => trim($route)
        );
    }

    /**
     * Set grouped routes, options like middleware
     * 
     * @param array  $group   domain, directions and middleware name
     * @param object $closure which contains $this->attach(); methods
     * 
     * @return void
     */
    public function group(array $group, $closure)
    {
        if ($this->detectDomain($group) == false) {   // When run the group if domain not match with regex don't run the group function.
            return;                                   // Forexample we define a sub domain in group but regex does not match with this domain
        }                                             // we need to stop group process.
        if ( ! isset($group['name'])) {
            throw new LogicException('Please give a name to your route group.');
        }
        $this->group = $group;
        $closure = Closure::bind($closure, $this, get_class());
        $closure();
        $this->group = array('name' => 'UNNAMED', 'domain' => null);  // Reset group variable after foreach group definition
        return $this;
    }

    /**
     * Returns attached middlewares of current domain
     * 
     * @return void
     */
    public function getAttachedRoutes()
    {
        if (defined('STDIN') OR ! isset($this->attach[$this->DOMAIN])) {  // Disable middlewares for CLI interface
            return array();
        }
        return $this->attach[$this->DOMAIN];
    }

    /**
     * Get currently worked domain configured in your routes.php
     * 
     * @return string
     */
    public function getDomain()
    {
        return $this->DOMAIN;
    }

}

// END Router.php File
/* End of file Router.php

/* Location: .Obullo/Router/Router.php */