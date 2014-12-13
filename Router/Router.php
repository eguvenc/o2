<?php

namespace Obullo\Router;

use Controller,
    Closure,
    Obullo\Http\Response,
    LogicException;

/**
 * Router Class
 *
 * Modeled after Codeigniter router class.
 * 
 * @category  Router
 * @package   Request
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
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
     * Directory name
     * 
     * @var string
     */
    public $directory = '';

    /**
     * Top directory name
     * 
     * @var string
     */
    public $topDirectory = '';

    /**
     * Defined route filters
     * 
     * @var array
     */
    public $filters = array();

    /**
     * Attached before routes to filters
     * 
     * @var array
     */
    public $attachAfter = array();

    /**
     * Attached after routes to filters
     * 
     * @var array
     */
    public $attachBefore = array();

    /**
     * Default controller name
     * 
     * @var string
     */
    public $defaultController = 'welcome';

    /**
     * 404 not found handler
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
     * Host address
     *
     * user.example.com
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
     * Constructor
     * Runs the route mapping function.
     * 
     * @param array $c      container
     * @param array $params configuration array
     * 
     * @return void
     */
    public function __construct($c, $params = array())
    {
        $this->c = $c;
        $this->router = $params;
        $this->uri    = $this->c->load('uri');
        $this->config = $this->c->load('config');
        $this->logger = $this->c->load('service/logger');
        $this->HOST   = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : null;

        if (defined('STDIN')) {
            $this->HOST = 'Cli';  // Define fake host for Command Line Interface
        }
        if ($this->HOST != 'Cli' AND strpos($this->HOST, $this->config['url']['host']) === false) {
            $c->load('response')->showError('Your host configuration is not correct in the main config file.');
        }
        $this->logger->debug('Router Class Initialized', array('host' => $this->HOST));
    }

    /**
     * Clean all data for Layers
     *
     * @return  void
     */
    public function clear()
    {
        $this->uri = $this->c->load('uri');   // reset cloned URI object.
        $this->class = '';
        $this->directory = '';
        $this->topDirectory = '';
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
     * @return void
     */
    public function defaultPage($pageController)
    {
        $this->defaultController = $pageController;
    }

    /**
     * Error 404 not found controller
     * 
     * @param string $errorController error page
     * 
     * @return void
     */
    public function error404($errorController)
    {
        $this->pageNotFoundController = $errorController;
    }

    /**
     * Assign your routes
     * 
     * @param string $method  POST, GET, PUT, DELETE
     * @param string $match   uri string match regex
     * @param string $rewrite uri rewrite regex value
     * @param string $closure optional closure function
     * @param string $group   optional group name
     * 
     * @return object router
     */
    public function route($method, $match, $rewrite, $closure = null, $group = array('domain' => null, 'name' => '*'))
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
                'sub.domain' => is_object($group['domain']) ? $group['domain']->domain->regex : $group['domain'],
                'method' => $method, 
                'match' => $match, 
                'rewrite' => $rewrite, 
                'scheme' => $scheme, 
                'closure' => $closure,
            );
        } else {
            $this->routes[$this->DOMAIN][] = array(
                'group' => $group['name'],
                'sub.domain' => null,
                'method' => $method, 
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
     * @return void
     */
    public function replace(array $replace)
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

        if (isset($segments[1]) 
            AND ! is_dir(PUBLIC_DIR .$segments[0]. DS .'controller'. DS) // If controller is not a folder.
            AND is_dir(PUBLIC_DIR .$segments[0]. DS . $segments[1]. DS)  // Detect Top Directory and change directory !!
        ) {
            $this->setTopDirectory($segments[0]);
            $this->setDirectory($segments[1]);
            array_shift($segments);
        }
        // if segments[1] exists set first segment as a directory 
        if ( ! empty($segments[1]) AND file_exists(PUBLIC_DIR . $this->fetchTopDirectory(DS). $this->fetchDirectory() . DS . 'controller' . DS . $segments[1] . '.php')) {
            return $segments;
        }
        // if segments[1] not exists. forexamle http://example.com/welcome
        if (file_exists(PUBLIC_DIR. $this->fetchDirectory() . DS . 'controller' . DS . $this->fetchDirectory() . '.php')) {
            array_unshift($segments, $this->fetchDirectory());
            return $segments;
        }
        // HTTP 404
        // If we've gotten this far it means that the URI does not correlate to a valid
        // controller class.  We will now see if there is an +override
        if ( ! empty($this->pageNotFoundController)) {
            $exp = explode('/', $this->pageNotFoundController);
            $this->setDirectory($exp[0]);
            $this->setClass($exp[1]);
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
            // $this->logger->error('Route domain configuration hasn\'t been set correctly.', array('domain' => $this->DOMAIN));
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
            if (preg_match('#^' . $val['match'] . '$#', $uri)) {  // Does the RegEx match?
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
        if (is_object(Controller::$instance)) {
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
     * Fetch the current class
     *
     * @return    string
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
    public function setTopDirectory($directory)
    {
        $this->topDirectory = $directory;
    }

    /**
     * Get top directory
     * 
     * @param string $seperator DS constant
     * 
     * @return void
     */
    public function fetchTopDirectory($seperator = '')
    {
        return ( ! empty($this->topDirectory)) ? filter_var($this->topDirectory, FILTER_SANITIZE_SPECIAL_CHARS). $seperator : '';
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
        $routedDomain = (isset($options['domain'])) ? $options['domain'] : '*'; 
        $routedDomain = is_object($routedDomain) ? $routedDomain->domain->regex : $routedDomain;
        $match = false;
        if ($routedDomain != '*' AND $match = $this->matchDomain($routedDomain)) { // If host matched with option['domain'] assign domain as $option['domain']
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
     * @return void
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
        if ( ! isset($options['before.filters']) AND ! isset($options['after.filters'])) {
            throw new LogicException('You need to create "before.filters" or "after.filters" key in options to intialize the filters.');
        }
        if ( ! isset($options['domain'])) {
            $options['domain'] = '*';
        }
        if (isset($options['before.filters'])) {
            $this->configureFilters($options, $route, 'before');
        }
        if (isset($options['after.filters'])) {
            $this->configureFilters($options, $route, 'after');
        }
    }

    /**
     * Build filter array
     * 
     * @param array  $options group options
     * @param string $route   current url regex
     * @param string $dir     direction before or after
     * 
     * @return void
     */
    protected function configureFilters(array $options, $route, $dir = 'before')
    {
        $direction = 'attach'.ucfirst($dir); // attachBefore, attachAfter

        foreach ($options[$dir.'.filters'] as $value) {
            $this->{$direction}[$this->DOMAIN][] = array(
                'name' => $value, 
                'arguments' => $options, 
                'route' => trim($route, '/'), 
                'attachedRoute' => trim($route)
            );
        }
    }

    /**
     * Creates route filter
     * 
     * @param string $name               filter name
     * @param mixed  $closureOrClassName anonymous function or classname string
     * 
     * @return void
     */
    public function createFilter($name, $closureOrClassName)
    {
        if (is_callable($closureOrClassName)) {
            $this->filters[$name]['closure'] = $closureOrClassName; // closure
            return;
        }
        $this->filters[$name]['class'] = $closureOrClassName; // class
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
     * @param string $direction before or after direction
     * 
     * @return void
     */
    public function initFilters($direction = 'before')
    {
        if (defined('STDIN')) {  // Disable filters for Console commands
            return;
        }
        $direction = ucfirst($direction);
        $attachDirection = 'attach'.$direction;
        if (count($this->{$attachDirection}) == 0 OR ! isset($this->{'attach'.$direction}[$this->DOMAIN])) {
            return;
        }
        $route = $this->uri->getUriString();        // Get current uri
        if ( ! isset($_SERVER['LAYER_REQUEST'])) {  // Don't run with 
            foreach ($this->{'attach'.$direction}[$this->DOMAIN] as $value) {
                if ($value['route'] == $route) {    // if we have natural route match
                    $this->runFilter($value['name'], $value['arguments']);
                } elseif (preg_match('#^' . $value['attachedRoute'] . '$#', $route)) {
                    $this->runFilter($value['name'], $value['arguments']);
                }
            }
        }
    }

    /**
     * Run filters
     * 
     * @param array $name   filter name
     * @param array $params parameters
     * 
     * @return void
     */
    protected function runFilter($name, $params = array())
    {        
        if (isset($this->filters[$name]['closure'])) {
            $this->bind($this->filters[$name]['closure'], $params);
        } elseif (isset($this->filters[$name]['class'])) { 
            $Class = '\\'.ucfirst($this->filters[$name]['class']);
            new $Class($this->c, $params);
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

}

// END Router.php File
/* End of file Router.php

/* Location: .Obullo/Router/Router.php */