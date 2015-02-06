<?php

namespace Obullo\Router;

use Closure,
    Controller,
    LogicException,
    Obullo\Http\Response,
    BadMethodCallException,
    Obullo\Container\Container;

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
Class Router
{
    /**
     * Uri class
     * 
     * @var object
     */
    public $uri;

    /**
     * Logger class
     * 
     * @var object
     */
    public $logger;

    /**
     * Config class
     * 
     * @var object
     */
    public $config;

    /**
     * Response class
     * 
     * @var object
     */
    public $response;

    /**
     * Routes config
     * 
     * @var array
     */
    public $routes = array();

    /**
     * Controller class name
     * 
     * @var string
     */
    public $class = '';

    /**
     * Default method
     * 
     * @var string
     */
    public $method = 'index';

    /**
     * Directory name
     * 
     * @var string
     */
    public $directory = '';

    /**
     * Module name
     * 
     * @var string
     */
    public $module = '';

    /**
     * Defined route filters
     * 
     * @var array
     */
    public $filters = array();

    /**
     * Attached after routes to filters
     * 
     * @var array
     */
    public $attach = array();

    /**
     * Default controller name
     * 
     * @var string
     */
    public $defaultController = 'welcome';

    /**
     * Page not found handler ( 404 )
     * 
     * @var string
     */
    public $pageNotFoundController = '';

    /**
     * Groupped route domain address
     * 
     * @var string
     */
    public $groupDomain = '*';

    /**
     * Defined host address in the config file.
     * 
     * @var string
     */
    protected $ROOT;

    /**
     * Host address user.example.com
     * 
     * @var string
     */
    protected $HOST;

    /**
     * Current domain name
     * 
     * @var string
     */
    protected $DOMAIN;

    /**
     * Keeps matched domains in cache
     * 
     * @var array
     */
    protected $domainMatches = array();

    /**
     * Http methods ( get, post, put, delete )
     * 
     * @var string
     */
    protected $httpMethod = 'get';

    /**
     * Constructor
     * Runs the route mapping function.
     * 
     * @param array $c      container
     * @param array $params configuration array
     */
    public function __construct(Container $c, $params = array())
    {
        $this->c = $c;
        $this->router = $params;
        $this->uri    = $this->c['uri'];
        $this->config = $this->c['config'];
        $this->logger = $this->c['logger'];
        $this->HOST   = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : null;

        if (defined('STDIN')) {
            $this->HOST = 'Cli';  // Define fake host for Command Line Interface
        }
        if ($this->HOST != 'Cli' AND strpos($this->HOST, $this->config['url']['webhost']) === false) {
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
        $this->response = null;
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
     * @return void
     */
    public function domain($domain = '')
    {
        $this->ROOT = trim($domain, '.');
        if (defined('STDIN')) {
            $this->ROOT = 'Cli'; // Define fake domain to Command Line Interface
        }
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
     * @param string $group   optional group name
     * 
     * @return object router
     */
    public function get($match, $rewrite = null, $closure = null, $group = array())
    {
        $this->route(array('get'), $match, $rewrite, $closure = null, $group);
        return $this;
    }

    /**
     * Defines http $_POST based routes
     * 
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * @param string $group   optional group name
     * 
     * @return object router
     */
    public function post($match, $rewrite = null, $closure = null, $group = array())
    {
        $this->route(array('post'), $match, $rewrite, $closure = null, $group);
        return $this;
    }

    /**
     * Defines http $_REQUEST['PUT'] based routes
     * 
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * @param string $group   optional group name
     * 
     * @return object router
     */
    public function put($match, $rewrite = null, $closure = null, $group = array())
    {
        $this->route(array('put'), $match, $rewrite, $closure = null, $group);
        return $this;
    }

    /**
     * Defines http $_REQUEST['DELETE'] based routes
     * 
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * @param string $group   optional group name
     * 
     * @return object router
     */
    public function delete($match, $rewrite = null, $closure = null, $group = array())
    {
        $this->route(array('delete'), $match, $rewrite, $closure = null, $group);
        return $this;
    }

    /**
     * Defines multiple http request based routes
     * 
     * @param string $methods http methods
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * @param string $group   optional group name
     * 
     * @return object router
     */
    public function match($methods, $match, $rewrite = null, $closure = null, $group = array())
    {
        $this->route($methods, $match, $rewrite, $closure = null, $group);
    }

    /**
     * Defines http $_REQUEST based routes
     * 
     * @param string $methods method names
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * @param string $group   optional group name
     * 
     * @return object router
     */
    public function route($methods = array(), $match, $rewrite = null, $closure = null, $group = array('domain' => null, 'name' => '*'))
    {
        $domainMatch = $this->detectDomain($group);

        if ( ! isset($group['domain'])) {
            $group['domain'] = null;
        }
        if ( ! isset($group['name'])) {
            $group['name'] = '*';
        }
        if ($domainMatch === false AND $group['domain'] !== null) {
            return;
        }
        $scheme = (strpos($match, '}') !== false) ? $match : null;
        if ($this->isSubDomain($this->DOMAIN)) {
            $this->routes[$this->DOMAIN][] = array(
                'group' => $group['name'],
                'sub.domain' => isset($group['domain']['regex']) ? $group['domain']['regex'] : $group['domain'],
                'when' => $methods, 
                'match' => $match,
                'rewrite' => $rewrite,
                'scheme' => $scheme,
                'closure' => $closure,
            );
        } else {
            $this->routes[$this->DOMAIN][] = array(
                'group' => $group['name'],
                'sub.domain' => null,
                'when' => $methods, 
                'match' => $match,
                'rewrite' => $rewrite,
                'scheme' => $scheme,
                'closure' => $closure,
            );
        }
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
                $this->response = new Response($this->c);
                if (isset($_SERVER['LAYER_REQUEST'])) {  // Returns to false if we have Layer connection error.
                    $this->response->showError($message, false);
                    return false;
                }
                $this->response->showError($message, 404);
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
     * Set the Route
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
        if (isset($segments[2])) {
            $this->setMethod($segments[2]); // A standard method request
        } else {
            $segments[2] = 'index'; // This lets the "routed" segment array identify that the default index method is being used.
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

        if (isset($segments[1]) AND is_dir(CONTROLLERS .$segments[0]. DS . $segments[1]. DS)  // Detect Top Directory and change directory !!
        ) {
            $this->setModule($segments[0]);
            $this->setDirectory($segments[1]);
            array_shift($segments);
        }
        $module = $this->fetchModule(DS);
        $directory = $this->fetchDirectory();

        // if segments[1] exists set first segment as a directory 
        if ( ! empty($segments[1]) AND file_exists(CONTROLLERS .$module.$directory. DS .$segments[1].'.php')) {
            return $segments;
        }
        // if segments[1] not exists. forexamle http://example.com/welcome
        if (file_exists(CONTROLLERS .$directory. DS .$directory. '.php')) {
            array_unshift($segments, $directory);
            return $segments;
        }
        // HTTP 404
        // If we've gotten this far it means that the URI does not correlate to a valid
        // controller class.  We will now see if there is an +override
        if ( ! empty($this->pageNotFoundController)) {
            $exp = explode('/', $this->pageNotFoundController);
            $this->setDirectory($exp[0]);
            $this->setClass($exp[1]);
            $this->setMethod(isset($exp[2]) ? $exp[2] : 'index');
            return $exp;
        }
        $errorPage = (isset($segments[1])) ? $segments[0] . '/' . $segments[1] : $segments[0];
        $this->response = new Response($this->c); // 404 Response
        if (isset($_SERVER['LAYER_REQUEST'])) {
            $this->response->show404($errorPage, false);
            return false;
        }
        $this->response->show404($errorPage);
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
        $uri = implode('/', $this->uri->segments);
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

                if (count($val['when']) > 0) { //  When http method filter
                    $this->runFilter('methodNotAllowed', 'before', array('allowedMethods' => $val['when']));
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
     * @return void
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Set current method
     * 
     * @param string $method name
     *
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Fetch the current routed class name
     *
     * @return string
     */
    public function fetchClass()
    {
        return $this->class;
    }

    /**
     * Set the directory name
     *
     * @param string $directory directory
     * 
     * @return void
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
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
     * @param string $seperator DS constant
     * 
     * @return void
     */
    public function fetchModule($seperator = '')
    {
        return ( ! empty($this->module)) ? filter_var($this->module, FILTER_SANITIZE_SPECIAL_CHARS). $seperator : '';
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
        return str_replace(' ', '_', $namespace);
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
        return ucwords($str);
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
        $domain = (isset($options['domain'])) ? $options['domain'] : '*'; 
        $domain = isset($domain['regex']) ? $domain['regex'] : $domain;
        $match = false;

        if ($domain != '*' AND $match = $this->matchDomain($domain)) { // If host matched with option['domain'] assign domain as $option['domain']
            $this->DOMAIN = $match;
            return true; // Regex match.
        }
        if ($this->ROOT == $this->HOST) {
            $this->DOMAIN = $this->ROOT;
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
        if (isset($this->domainMatches[$domain.'#'.$this->HOST])) {
            return $this->domainMatches[$domain.'#'.$this->HOST];
        }
        if (preg_match('#'.$domain.'#', $this->HOST, $matches)) {
            return $this->domainMatches[$domain.'#'.$this->HOST] = $matches[0];
        }
        return false;
    }

    /**
     * Attach route to filter
     * 
     * @param string $route   filter route
     * @param array  $options attach options
     * 
     * @return object
     */
    public function attach($route, array $options = array())
    {
        $match = $this->detectDomain($options);

        // Domain Regex Support
        // If we have defined domain and not match with host don't run the filter.
        if (isset($options['domain']) AND ! $match) {  // If we have defined domain and not match with host don't run the filter.
            return;
        }
        // Attach Regex Support
        $host = str_replace($this->getSubDomain($this->DOMAIN), '', $this->HOST);
        if ( ! $this->isSubDomain($this->DOMAIN) AND $this->isSubDomain($this->HOST)) {
            $host = $this->HOST; // We have a problem when the host is subdomain and config domain not. This fix the isssue.
        }
        if ($this->DOMAIN != $host) {
            return;
        }
        if ( ! isset($options['domain'])) {
            $options['domain'] = '*';
        }
        if (isset($options['filters'])) {
            $this->configureFilters($options['filters'], $route, $options);
            return $this;
        }
        $this->configureFilters($options, $route, $options);
        return $this;
    }

    /**
     * Configure attached filters
     * 
     * @param array  $filters arguments
     * @param string $route   route
     * @param array  $options arguments
     * 
     * @return void
     */
    protected function configureFilters($filters, $route, $options)
    {
        foreach ($filters as $value) {
            $this->attach[$this->DOMAIN][] = array(
                'name' => $value, 
                'options' => $options,
                'route' => trim($route, '/'), 
                'attachedRoute' => trim($route)
            );
        }
    }

    /**
     * Creates route filter
     * 
     * @param string $name      filter name
     * @param mixed  $namespace classname with namespace
     * @param mixed  $method    directions ( before, after, load, finish )
     * 
     * @return void
     */
    public function filter($name, $namespace, $method = 'before')
    {
        $this->filters[$method][$name] = $namespace;
        return $this;
    }

    /**
     * Create grouped routes or route filters
     * 
     * @param array  $options domain, directions and filter name
     * @param object $closure which contains $this->attach(); methods
     * 
     * @return void
     */
    public function group(array $options, $closure)
    {
        if ($this->detectDomain($options) == false) {   // When run the group if domain not match with regex don't run the group function.
            return;                                     // Forexample we define a sub domain in group but regex does not match with this domain
        }                                               // we need to stop group process.
        if ( ! isset($options['name'])) {
            throw new LogicException('Please give a name to your route group.');
        }
        $closure = Closure::bind($closure, $this, get_class());
        $closure($options);
        return $this;
    }

    /**
     * Initialize filter
     * 
     * @param string $method           directions ( before, after, load, finish )
     * @param object $annotationFilter annotations filter object
     * 
     * @return void
     */
    public function initFilters($method = 'before', $annotationFilter = false)
    {
        if (defined('STDIN')) {  // Disable filters for Console commands
            return;
        }
        if ($annotationFilter) {
            $annotationFilter->initFilters($method);  // Initialize annotation filters
        }
        if (count($this->attach) == 0 OR ! isset($this->attach[$this->DOMAIN])) {
            return;
        }
        $route = $this->uri->getUriString();        // Get current uri

        if ( ! isset($_SERVER['LAYER_REQUEST'])) {  // Don't run filters on layers
            foreach ($this->attach[$this->DOMAIN] as $value) {
                if ($value['route'] == $route) {    // if we have natural route match
                    $this->runFilter($value['name'], $method, $value['options']);
                } elseif (preg_match('#^' . $value['attachedRoute'] . '$#', $route)) {
                    $this->runFilter($value['name'], $method, $value['options']);
                }
            }
        }
    }

    /**
     * Run filters
     * 
     * @param array  $name   filter name
     * @param string $method directions ( before, after, load, finish )
     * @param array  $params parameters array
     * 
     * @return void
     */
    public function runFilter($name, $method = 'before', $params = array())
    {
        if (isset($this->filters[$method][$name])) {  // If filter method exists just run one time for every methods

            $Class = '\\'.ucfirst($this->filters[$method][$name]);
            $class = new $Class($this->c, $params);

            if ( ! method_exists($class, $method)) {   // Throw exception if filter method not exists.
                throw new BadMethodCallException(
                    sprintf(
                        'Filter class %s requires %s method but not found.',
                        ltrim($Class, '\\'),
                        $method
                    )
                );
            }
            $class->$method();
        }
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

    /**
     * Retutns registered filters
     * 
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

}

// END Router.php File
/* End of file Router.php

/* Location: .Obullo/Router/Router.php */