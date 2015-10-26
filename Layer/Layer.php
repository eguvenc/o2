<?php

namespace Obullo\Layer;

use stdClass;
use Obullo\Http\Controller;
use Obullo\Log\LoggerInterface;
use Obullo\Container\ContainerInterface;

/**
 * Layers is a programming technique that delivers you to "Multitier Architecture" 
 * to scale your applications.
 * 
 * Derived from HMVC pattern and named as "Layers" in Obullo.
 * 
 * Copyright (c) 2009 - 2015 Ersin Guvenc
 */

/**
 * Layer
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Layer
{
    const CACHE_KEY = 'Layer:';
    
    /**
     * Container
     * 
     * @var object
     */
    protected $c = null;

    /**
     * Layer uri string
     * 
     * @var string
     */
    protected $layerUri;

    /**
     * Unique connection string.
     *  
     * @var string
     */
    protected $hashString = null;

    /**
     * Process flag
     * 
     * @var boolean
     */
    protected $processDone = false;

    /**
     * Request method
     * 
     * @var string
     */
    protected $requestMethod = 'GET';

    /**
     * Request data
     * 
     * @var array
     */
    protected $requestData = array();

    /**
     * Layer error
     * 
     * @var null
     */
    protected $error = null;

    /**
     * Constructor
     * 
     * @param object $c      \Obullo\Container\ContainerInterface
     * @param object $logger \Obullo\Log\LoggerInterface
     * @param array  $params config parameters
     */
    public function __construct(ContainerInterface $c, LoggerInterface $logger, array $params)
    {
        $this->c = $c;
        $this->params = $params;
        $this->logger = $logger;

        register_shutdown_function(array($this, 'close'));  // Close current layer
    }

    /**
     * Set request headers
     *
     * @return void
     */
    public function setHeaders()
    {
        $_SERVER['LAYER_REQUEST'] = true;   // Set Headers
    }

    /**
     * Prepare Request ( Set the URI String ).
     * 
     * @param string $uriString uri
     * 
     * @return void
     */
    public function setUrl($uriString)
    {
        $this->hashString = '';        // Reset hash string otherwise it causes unique id errors.
        
        $_SERVER['LAYER_REQUEST_URI'] = trim($uriString, '/'); // Set uri string to $_SERVER GLOBAL
        $this->prepareHash($_SERVER['LAYER_REQUEST_URI']);

        $this->cloneObjects();
        $this->makeGlobals();

        $this->c['uri']->clear();      // Reset uri objects we will reuse it for layer
        $this->c['router']->clear();   // Reset router objects we will reuse it for layer
        
        $this->c['uri']->setUriString($_SERVER['LAYER_REQUEST_URI']);
        $this->c['router']->init();
    }

    /**
     * Clone controller, router and uri objects
     * 
     * @return void
     */
    protected function cloneObjects()
    {
        $this->controller = Controller::$instance;      // We need get backup object of main controller
        $this->uri = Controller::$instance->uri;        // Create copy of original Uri class.
        $this->router = Controller::$instance->router;  // Create copy of original Router class.

        $this->uri = clone $this->uri;
        $this->router = clone $this->router;
    }

    /**
     * Make available global objects in the container
     * 
     * @return void
     */
    public function makeGlobals()
    {
        $this->c['app.uri'] = function () {
            return $this->uri;
        };
        $this->c['app.router'] = function () {
            return $this->router;
        };
    }

    /**
     * Set Layer Request Method
     *
     * @param string $method layer method
     * @param array  $data   params
     * 
     * @return void
     */
    public function setMethod($method, $data = array())
    {
        if (empty($data)) {
            $data = array();
        }
        $this->prepareHash($data); // Set unique id foreach requests
        $_SERVER['LAYER_REQUEST_METHOD'] = $this->requestMethod = strtoupper($method);

        foreach ($data as $key => $val) { //  Assign all post data to REQUEST variable.
            $_REQUEST[$key] = $val;
            if ($this->requestMethod == 'POST') {
                $_POST[$key] = $val;
                $this->requestData['POST'][$key] = $val;
            } 
            if ($this->requestMethod == 'GET') {
                $_GET[$key] = $val;
                $this->requestData['GET'][$key] = $val;
            }
            $this->requestData['REQUEST'][$key] = $val;
        }
    }

    /**
     * Execute Layer Request
     * 
     * @param integer $expiration cache ttl
     * 
     * @return string
     */
    public function execute($expiration = '')
    {
        $layerID = $this->getId();  // Get layer id
        $start = microtime(true);   // Start query timer 

        if ($this->params['cache'] && $response = $this->c['cache']->get($layerID)) {   
            $this->log('$_LAYER_CACHED:', $this->c['uri']->getUriString(), $start, $layerID, $response);
            $this->reset();
            return base64_decode($response);
        }
        if ($this->getError() != '') {  // If router dispatch fail ?
            $error = $this->getError();
            $this->reset();
            return $error;
        }
        $this->c['uri']->setUriString(rtrim($this->c['uri']->getUriString(), '/') . '/' .$layerID); //  Create Layer ID
        
        $directory = $this->c['router']->getDirectory();
        $className = $this->c['router']->getClass();
        $method    = $this->c['router']->getMethod();

        $this->layerUri = $this->c['router']->getModule('/') .$directory.'/'.$className;
        $controller = MODULES .$this->c['router']->getModule('/') .$directory.'/'.$className. '.php';
        $className = '\\'.$this->c['router']->getNamespace().'\\'.$className;

                                                   // Check class is exists in the storage
        if (! class_exists($className, false)) {   // Don't allow multiple include.
            include $controller;                   // Load the controller file.
        }
        if (! class_exists($className, false)) {
            return $this->show404($method);
        }
        $class = new $className;  // Call the controller

        if (! method_exists($class, $method)) {  // Check method exist or not
            return $this->show404($method);
        }
        ob_start();
        call_user_func_array(array($class, $method), array_slice($this->c['uri']->getRoutedSegments(), 3));
        $response = ob_get_clean();

        if (is_numeric($expiration)) {
            $this->c['cache']->set($layerID, base64_encode($response), (int)$expiration); // Write to Cache
        }
        $this->log('$_LAYER:', $this->getUri(), $start, $layerID, $response);
        return $response;
    }

    /**
     * Show404 output and reset layer variables
     * 
     * @param string $method current method
     * 
     * @return string 404 message
     */
    protected function show404($method)
    {   
        $this->reset();
        $this->setError('{Layer404}<b>404 layer not found:</b> '.$this->layerUri.'/'.$method);
        return $this->getError();
    }

    /**
     * Reset router for mutiple layer requests
     * and close the layer connections.
     *
     * @return void
     */
    protected function reset()
    {
        if (! isset($_SERVER['LAYER_REQUEST_URI'])) { // If no layer header return to null;
            return;
        }
        $this->clear();  // Reset all Layer variables.
    }

    /**
     * Reset all variables for multiple layer requests.
     *
     * @return void
     */
    public function clear()
    {
        $this->error = null;    // Clear variables otherwise all responses of layer return to same error.
        $this->processDone = false;
        $this->requestMethod = 'GET';
        unset($_SERVER['LAYER_REQUEST'], $_SERVER['LAYER_REQUEST_URI'], $_SERVER['LAYER_REQUEST_METHOD']);
    }

    /**
     * Restore original controller objects
     * 
     * @return void
     */
    public function restore()
    {
        if (isset($this->requestData[$this->requestMethod])) {
            $data['REQUEST'] = &$_REQUEST;
            $data['POST']    = &$_POST;
            $data['GET']     = &$_GET;
            foreach (array_keys($this->requestData[$this->requestMethod]) as $v) {
                unset($data[$this->requestMethod][$v]);
            }
        }
        $this->reset();
        Controller::$instance = $this->controller;
        Controller::$instance->uri = $this->uri;
        Controller::$instance->router = $this->router;
        $this->processDone = true;
    }

    /**
     * Create layer connection string next we will convert it to connection id.
     *
     * @param mixed $resource string
     *
     * @return void
     */
    protected function prepareHash($resource)
    {
        if (is_array($resource)) {
            if (sizeof($resource) > 0) {
                $this->hashString .= str_replace('"', '', json_encode($resource));
            }
            return;
        } 
        $this->hashString .= $resource;
    }

    /**
     * Returns to Cache key ( layer id ).
     * 
     * @return string
     */
    public function getId()
    {
        $id = trim($this->hashString);
        return self::CACHE_KEY. sprintf("%u", crc32((string)$id));
    }

    /**
     * Get last Layer uri
     * 
     * @return string
     */
    public function getUri()
    {
        return $this->layerUri;
    }

    /**
     * Log response data
     * 
     * @param string $label    log label
     * @param string $uri      uri string
     * @param string $start    start time
     * @param string $id       layer id
     * @param string $response data
     * 
     * @return void
     */
    public function log($label, $uri, $start, $id, $response)
    {
        $uriString = md5($this->c['app']->uri->getUriString());

        $this->c['logger']->debug(
            $label.' '.strtolower($uri), 
            array(
                'time' => number_format(microtime(true) - $start, 4),
                'id' => $id, 
                'output' => '<div class="obullo-layer" data-unique="u'.uniqid().'" data-id="'.$id.'" data-uristring="'.$uriString.'">' .$response. '</div>',
            )
        );
    }

    /**
     * Set last response error
     *
     * @param string $error message
     * 
     * @return object
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Get last response error
     * 
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Close Layer Connections
     * 
     * If we have any possible Layer exceptions
     * reset the router variables and restore all objects
     * to complete Layer process. Otherwise we see uncompleted request errors.
     * 
     * @return void
     */
    public function close()
    {
        if ($this->processDone == false) {  // If "processDone == true" we understand process completed successfully.
            $this->restore();               // otherwise process is failed and we need to shutdown connection.
            return;
        }
        $this->processDone = false;
    }
    
}