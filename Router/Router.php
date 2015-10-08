<?php

namespace Obullo\Router;

use Closure;
use Controller;
use LogicException;

use Obullo\Log\LoggerInterface;
use Obullo\Config\ConfigInterface;
use Obullo\Container\ContainerInterface;

use Psr\Http\Message\UriInterface;

/**
 * Http Router Class ( Modeled after Codeigniter router )
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Router
{
    protected $uri;                            // Uri class
    protected $logger;                         // Logger class
    protected $class = '';                     // Controller class name
    protected $routes = array();               // Routes config
    protected $method = 'index';               // Default method
    protected $directory = '';                 // Directory name
    protected $module = '';                    // Module name
    protected $middlewares = array();          // Defined route middlewares
    protected $attach = array();               // Attached after routes to middlewares
    protected $defaultController = '';         // Default controller name
    protected $pageNotFoundController = '';    // Page not found handler ( 404 )
    protected $groupDomain = '*';              // Groupped route domain address

    protected $ROOT;                        // Defined host address in the config file.
    protected $HOST;                        // Host address user.example.com
    protected $DOMAIN;                      // Current domain name
    protected $domainMatches = array();     // Keeps matched domains in cache
    protected $httpMethod = 'get';          // Http methods ( get, post, put, delete )
    protected $group = array('name' => 'UNNAMED', 'domain' => null);       // Group configuration array

    /**
     * If default route not detected
     */
    const DEFAULT_PAGE_ERROR = 'Unable to determine what should be displayed. A default route has not been specified in the routing file.';

    /**
     * Constructor
     * 
     * Runs the route mapping function.
     * 
     * @param array  $c      \Obullo\Container\ContainerInterface
     * @param array  $config \Obullo\Config\ConfigInterface
     * @param object $uri    \Psr\Http\Message\UriInterface
     * @param array  $logger \Obullo\Log\LoggerInterface
     */
    public function __construct(ContainerInterface $c, ConfigInterface $config, UriInterface $uri, LoggerInterface $logger)
    {
        $this->c = $c;
        $this->uri = $uri;
        $this->logger = $logger;
        $this->HOST = $uri->getHost();

        if (strpos($this->HOST, $config['url']['webhost']) === false) {
            $this->c['response']->withStatus(500)->showError('Your host configuration is not correct in the main config file.');
        }
        $this->logger->debug('Router Class Initialized', array('host' => $this->HOST), 9998);
    }

    /**
     * Clean all data for Layers
     *
     * @return void
     */
    public function clear()
    {
        $this->uri = $this->c['uri'];   // Reset cloned URI object.
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
     * Configure router
     * 
     * @param array $params config params
     * 
     * @return void
     */
    public function configuration(array $params)
    {
        if (! isset($params['domain'])) {
            throw new RuntimeException("Domain not configured in routes.php");
        }
        $this->ROOT = trim($params['domain'], '.');
        $this->pageNotFoundController = $params['error404'];
        $this->defaultController = $params['defaultPage'];
    }

    /**
     * Sets default page controller
     * 
     * @param string $page uri
     * 
     * @return object
     */
    public function defaultPage($page)
    {
        $this->defaultController = $page;
        return $this;
    }

    /**
     * Sets 404 not found controller
     * 
     * @param string $page uri
     * 
     * @return object
     */
    public function error404($page)
    {
        $this->pageNotFoundController = $page;
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
        if ($this->uri->getUriString() == '') {     // Is there a URI string ? If not, the default controller specified in the "routes" file will be shown.
            $layerRequest = isset($_SERVER['LAYER_REQUEST']);
            if ($layerRequest) {
                $this->c['output']->setError('{Layer404}'.static::DEFAULT_PAGE_ERROR); // Returns to false if we have Layer error.
                return false;
            }
            $this->checkErrors();
            $segments = $this->resolve(explode('/', $this->defaultController));  // Turn the default route into an array.
            $this->setClass($segments[1]);
            $this->setMethod('index');
            $this->uri->setRoutedSegments($segments);  // Assign the segments to the URI class
            $this->logger->debug('No URI present. Default controller set.');
            return;
        }
        $this->dispatch();
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
        if ($this->detectDomainMatch($this->group) === false && $this->group['domain'] !== null) {
            return;
        }
        $this->routes[$this->DOMAIN][] = array(
            'group' => $this->_getGroupNameValue(),
            'sub.domain' => $this->_getSubDomainValue(),
            'when' => $methods, 
            'match' => trim($match, '/'),
            'rewrite' => trim($rewrite, '/'),
            'scheme' => $this->_getSchemeValue($match),
            'closure' => $closure,
        );
        return $this;
    }

    /**
     * Get scheme value
     * 
     * @param string $match param
     * 
     * @return string
     */
    private function _getSchemeValue($match)
    {
        return (strpos($match, '}') !== false) ? trim($match, '/') : null;
    }

    /**
     * Get group value
     * 
     * @return string
     */
    private function _getGroupNameValue()
    {
        if (! isset($this->group['name'])) {
            $this->group['name'] = 'UNNAMED';
        }
        return $this->group['name'];
    }

    /**
     * Get subdomain value
     * 
     * @return mixed
     */
    private function _getSubDomainValue()
    {
        if ($this->isSubDomain($this->DOMAIN)) {
            return $this->group['domain'];
        }
        return null;
    }

    /**
     * Check domain has sub name
     * 
     * @param string $domain name
     * 
     * @return boolean
     */
    protected function isSubDomain($domain)
    {
        if (empty($domain)) {
            return false;
        }
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
    protected function getSubDomain($domain)
    {
        return str_replace($domain, '', $this->HOST);
    }

    /**
     * Check route errors
     * 
     * @return void
     */
    protected function checkErrors()
    {        
        if ($this->defaultController == '') {   // Set the default controller so we can display it in the event the URI doesn't correlated to a valid controller.
            $this->c['response']->withStatus(404)->showError(static::DEFAULT_PAGE_ERROR, '404');
        }
    }

    /**
     * Dispatch routes if we have no errors
     * 
     * @return void
     */
    protected function dispatch()
    {
        $this->uri->parseSegments();   // Compile the segments into an array 
        if (empty($this->routes)) {
            $this->setRequest($this->uri->getSegments());
            return;
        }
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
        $segments = $this->resolve($segments);
        if (count($segments) == 0) {
            return;
        }
        if (isset($segments[1])) {
            $this->setClass($segments[1]);
        }
        if (! empty($segments[2])) {
            $this->setMethod($segments[2]); // A standard method request
        } else {
            $segments[2] = 'index';         // This lets the "routed" segment array identify that the default index method is being used.
            $this->setMethod('index');
        }
        $this->uri->setRoutedSegments($segments);  // Update our "routed" segment array to contain the segments.
    }

    /**
     * Resolve segments
     * 
     * @param array $segments uri parts
     * 
     * @return array
     */
    protected function resolve($segments)
    {
        if (! isset($segments[0])) {
            return $segments;
        }
        $this->setDirectory($segments[0]);      // Set first segment as default "top" directory 
        $segments = $this->detectModule($segments);
        $directory = $this->fetchDirectory();
        $module = $this->fetchModule('/');
        $class = $this->fetchClass();
        if (! empty($class) && ! empty($module)) {   // Module index file support e.g modules/demo/tutorials/tutorials.php
            $directory = strtolower($class);
        }
        $checkIndexFile = false;
        if (! isset($segments[1]) || isset($segments[1]) && $segments[1] == $this->fetchMethod()) { //  Just check index file if second segment is not a class.
            $checkIndexFile = true;
        }
        if ($checkIndexFile && is_file(MODULES.$module.$directory.'/'.self::ucwordsUnderscore($directory).'.php')) {  // if segments[1] not exists. forexamle http://example.com/welcome
            array_unshift($segments, $directory);
            return $segments;
        }

        return $segments;
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
            && is_dir(MODULES .$segments[0].'/'. $segments[1].'/')  // Detect Module and change directory !!
        ) {
            $this->setModule($segments[0]);
            $this->setDirectory($segments[1]);
            array_shift($segments);
        }
        return $segments;
    }

    /**
     * Parse Routes
     *
     * This function matches any routes that may exist in the routes.php file against the URI to
     * determine if the directory/class need to be remapped.
     *
     * @return void
     */
    protected function parseRoutes()
    {
        $uri = $this->uri->getUriString();  // Warning !: Don't use $this->uri->segments in here instead of use getUriString otherwise
                                            // we could not get url suffix ".html".

        if (! empty($this->routes[$this->DOMAIN])) {
            foreach ($this->routes[$this->DOMAIN] as $val) {   // Loop through the route array looking for wild-cards
                $parameters = $this->parseParameters($uri, $val);
                if ($this->hasRegexMatch($val['match'], $uri)) {    // Does the route match ?
                    $this->dispatchRouteMatches($uri, $val, $parameters);
                    return;
                }
            }
        }
        $this->setRequest($this->uri->getSegments());  // If we got this far it means we didn't encounter a matching route so we'll set the site default route
    }

    /**
     * Parse closure parameters
     * 
     * @param string $uri uri
     * @param array  $val route values
     * 
     * @return array
     */
    protected function parseParameters($uri, $val)
    {
        $parameters = array();
        if (! is_null($val['scheme'])) {   // Do we have route scheme like {id}/{name} ?

            $parametersIndex = preg_split('#{(.*?)}#', $val['scheme']); // Get parameter indexes
            $parameterUri = substr($uri, strlen($parametersIndex[0]));
            $parametersReIndex = array_keys(array_slice($parametersIndex, 1));
            $segments = explode('/', $parameterUri);

            foreach ($parametersReIndex as $key) {  // Find parameters we will send it to closure($args)
                $parameters[] = (isset($segments[$key])) ? $segments[$key] : null;
            }
            return $parameters;
        }
        return $parameters;
    }


    /**
     * Dispatch route matches and assign middlewares
     * 
     * @param string $uri        current uri
     * @param array  $val        route values
     * @param array  $parameters closure parameters
     * 
     * @return void
     */
    protected function dispatchRouteMatches($uri, $val, $parameters)
    {
        if (count($val['when']) > 0) {  //  Dynamically add method not allowed middleware
            $this->c['app']->middleware('MethodNotAllowed', $val['when']);
        }
        if (! empty($val['rewrite']) && strpos($val['rewrite'], '$') !== false && strpos($val['match'], '(') !== false) {  // Do we have a back-reference ?
            $val['rewrite'] = preg_replace('#^'.$val['match'].'$#', $val['rewrite'], $uri);
        }
        $segments = (empty($val['rewrite'])) ? $this->uri->segments : explode('/', $val['rewrite']);
        $this->setRequest($segments);
        $this->bind($val['closure'], $parameters, true);
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
        if (! empty($this->routes[$this->DOMAIN][$count]['sub.domain'])) {
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
     * Closure bind function
     * 
     * @param object  $closure         anonymous function
     * @param array   $args            arguments
     * @param boolean $useCallUserFunc whether to use call_user_func_array()
     * 
     * @return void
     */
    protected function bind($closure, $args = array(), $useCallUserFunc = false)
    {
        if (! is_callable($closure)) {
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
     * Set the directory name : It must be lowercase otherwise sub module does not work
     *
     * @param string $directory directory
     * 
     * @return object Router
     */
    public function setDirectory($directory)
    {
        $this->directory = strtolower($directory);
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
        $this->module = strtolower($directory);
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
        return (empty($this->module)) ? '' : filter_var($this->module, FILTER_SANITIZE_SPECIAL_CHARS).$separator;
    }

    /**
     * Fetch the directory
     *
     * @return string
     */
    public function fetchDirectory()
    {
        return filter_var($this->directory, FILTER_SANITIZE_SPECIAL_CHARS);
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
     * Set grouped routes, options like middleware
     * 
     * @param array  $group   domain, directions and middleware name
     * @param object $closure which contains $this->attach(); methods
     * 
     * @return object
     */
    public function group(array $group, Closure $closure)
    {
        if (isset($group['match']) && ! $this->detectGroupMatch($group)) {
            return $this;
        }
        if (! $this->detectDomainMatch($group)) {     // When groups run if domain not match with regex don't continue.
            return $this;                             // Forexample we define a sub domain but group domain does not match
        }                                             // so we need to stop group process.
        $this->group = $group;
        $closure = Closure::bind($closure, $this, get_class());
        $sub = false;
        if (isset($group['domain']) && isset($this->domainMatches[$group['domain']])) {
            $sub = strstr($this->domainMatches[$group['domain']], '.', true);
        }
        $closure($sub);
        $this->group = array('name' => 'UNNAMED', 'domain' => null);  // Reset group variable after foreach group definition
        return $this;
    }

    /**
    * Detect static domain
    * 
    * @param array $options subdomain array
    * 
    * @return void
    */
    protected function detectDomainMatch(array $options = array())
    {
        $domain = $this->ROOT;
        if (isset($options['domain'])) {
            $domain = $options['domain'];
        }
        if ($match = $this->matchDomain($domain)) { // If host matched with option['domain'] assign domain as $option['domain']
            $this->DOMAIN = $match;
            return true;                // Regex match.
        }
        return false;  // No regex match.
    }

    /**
     * Detect class namespace
     * 
     * @param array $options array
     * 
     * @return bool
     */
    protected function detectGroupMatch($options = array())
    {
        if ($this->matchGroup($options['match'])) {
            return true;
        }
        return false;
    }

    /**
     * Does group has match ?
     *
     * @param string $match class namespace
     * 
     * @return bool
     */
    protected function matchGroup($match)
    {
        $uri = $this->uri->getUriString();
        if ($this->hasNaturalGroupMatch($match, $uri)) {
            return true;
        }
        if ($this->hasRegexMatch($match, $uri)) {
            return true;
        }
        return false;

    }

    /**
     * Before regex check natural uri match
     * 
     * @param string $match match url or regex
     * @param string $uri   uri string
     * 
     * @return boolean
     */
    protected function hasNaturalGroupMatch($match, $uri)
    {
        $exp = explode('/', $uri);
        $uriStr = '';
        $keyValues = array_keys(explode('/', $match));
        foreach ($keyValues as $k) {
            if (isset($exp[$k])) {
                $uriStr.= strtolower($exp[$k]).'/';
            }
        }
        if ($match == rtrim($uriStr, '/')) {
            return true;
        }
        return false;
    }

    /**
     * Check regex regex match
     * 
     * @param string $match regex or string
     * @param string $uri   current uri
     * 
     * @return boolean
     */
    protected function hasRegexMatch($match, $uri)
    {
        if ($match == $uri) { // Is there any literal match ? 
            return true;
        }
        if (preg_match('#^'.$match.'$#', $uri)) {
            return true;
        }
        return false;
    }

    /**
     * Store matched domain to cache then fetch if it exists
     * 
     * @param string $domain url
     * 
     * @return array matches
     */
    protected function matchDomain($domain)
    {
        if ($domain == $this->HOST) {
            return $domain;
        }
        $key = $domain;
        if (isset($this->domainMatches[$key])) {
            return $this->domainMatches[$key];
        }
        if (preg_match('#^'.$domain.'$#', $this->HOST, $matches)) {
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
        $match = $this->detectDomainMatch($this->group);
                                                          // Domain Regex Support, if we have defined domain and not match with host don't run the middleware.
        if (isset($this->group['domain']) && ! $match) {  // If we have defined domain and not match with host don't run the middleware.
            return;
        }
        $host = str_replace($this->getSubDomain($this->DOMAIN), '', $this->HOST);          // Attach Regex Support
        if (! $this->isSubDomain($this->DOMAIN) && $this->isSubDomain($this->HOST)) {
            $host = $this->HOST;  // We have a problem when the host is subdomain and config domain not. This fix the isssue.
        }
        if ($this->DOMAIN != $host) {
            return;
        }
        if (! isset($this->group['domain'])) {
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
     * Returns attached middlewares of current domain
     * 
     * @return mixed
     */
    public function getAttachedRoutes()
    {
        if (! isset($this->attach[$this->DOMAIN])) {  // Check first
            return array();
        }
        return $this->attach[$this->DOMAIN];
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

    /**
     * Returns to 404 controller name
     * 
     * @return mixed
     */
    public function get404Class()
    {
        if (empty($this->pageNotFoundController)) {
            return false;
        } 
        $this->setRequest(explode('/', $this->pageNotFoundController));
        return '\\'.$this->fetchNamespace().'\\'.$this->fetchClass();
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
        $this->route(array('get'), $match, $rewrite, $closure);
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
        $this->route(array('post'), $match, $rewrite, $closure);
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
        $this->route(array('put'), $match, $rewrite, $closure);
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
        $this->route(array('delete'), $match, $rewrite, $closure);
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
        $this->route($methods, $match, $rewrite, $closure);
        return $this;
    }

}