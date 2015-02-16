<?php

namespace Obullo\Layer;

use stdClass;
use Controller;
use Obullo\Container\Container;

/**
 * Layers is a programming technique that delivers you to "Multitier Architecture" 
 * to scale your applications.
 * 
 * Derived from HMVC pattern and named as "Layers" in Obullo.
 * 
 * Copyright (c) 2009 - 2015 Ersin Guvenc
 */

/**
 * Layer Class
 * 
 * @category  Layer
 * @package   Layer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/layer
 */
Class Layer
{
    const CACHE_KEY = 'Layer:';
    const LOG_HEADER = '<br /><div style="float:left;">';
    const LOG_FOOTER = '</div><div style="clear:both;"></div>';

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
     * Reset all variables for multiple layer requests.
     *
     * @return void
     */
    public function clear()
    {
        $this->c['response']->clear();
        $this->processDone = false;
        $this->requestMethod = 'GET';
        unset($_SERVER['LAYER_REQUEST'], $_SERVER['LAYER_REQUEST_URI'], $_SERVER['LAYER_REQUEST_METHOD']);
    }

    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params config 
     */
    public function __construct(Container $c, $params)
    {
        $this->c = $c;
        $this->params = $params;
        $this->logger = $c['logger'];
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

        $this->c['uri']->clear();      // Reset uri objects we will reuse it for layer
        $this->c['router']->clear();   // Reset router objects we will reuse it for layer

        $this->c['uri']->setUriString($_SERVER['LAYER_REQUEST_URI']);
        $this->c['router']->init();
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
        $KEY = $this->getId();         // Get layer id
        $start = microtime(true);   // Start query timer 

        if ($this->params['cache']) {
            $response = $this->c['cache']->get($KEY);     // This type cache use cache package
            if ( ! empty($response)) {              // If cache exists return to cached string.
                $this->log('$_LAYER_CACHED:', $this->c['uri']->getUriString(), $start, $KEY, $response);
                $this->reset();
                return base64_decode($response);
            }
        }
        if ($this->c['response']->getError() != '') {  // If router dispatch fail ?
            $this->reset();
            return $this->c['response']->getError();
        }

        $this->c['uri']->setUriString(rtrim($this->c['uri']->getUriString(), '/') . '/' .$KEY); //  Create Layer ID
        
        $directory = $this->c['router']->fetchDirectory();
        $className = $this->c['router']->fetchClass();
        $method    = $this->c['router']->fetchMethod();

        $this->layerUri = $this->c['router']->fetchModule().'/'.$directory.'/'.$className;
        $controller = CONTROLLERS .$this->c['router']->fetchModule() .DS .$directory. DS .$className. '.php';

        $className = '\\'.$this->c['router']->fetchNamespace().'\\'.$className;

                                                    // Check class is exists in the storage
        if ( ! class_exists($className, false)) {   // Don't allow multiple include.
            include $controller;                    // Load the controller file.
        }
        $class = new $className;  // Call the controller

        if (method_exists($class, 'load')) {
            $class->load();
        }
        if ( ! method_exists($class, $method)) {  // Check method exist or not
            $this->reset();
            return $this->c['response']->show404($this->layerUri.'/'.$method, false);
        }

        ob_start();
        call_user_func_array(array($class, $method), array_slice($this->c['uri']->rsegments, 3));
        $response = ob_get_clean();

        // $this->assignObjects($class); // Assign main controller objects to sub layers.

        if (is_numeric($expiration)) {
            $this->c['cache']->set($KEY, base64_encode($response), (int)$expiration); // Write to Cache
        }
        $this->log('$_LAYER:', $this->getUri(), $start, $KEY, $response);
        return $response;
    }

    /**
     * Reset router for mutiple layer requests
     * and close the layer connections.
     *
     * @return void
     */
    protected function reset()
    {
        if ( ! isset($_SERVER['LAYER_REQUEST_URI'])) { // If no layer header return to null;
            return;
        }
        $this->clear();  // Reset all Layer variables.
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

        Controller::$instance = $this->c['controller'];
        Controller::$instance->uri = $this->c['request.uri'];
        Controller::$instance->router = $this->c['request.router'];
        $this->processDone = true;
    }

    /**
     * Create layer connection string next
     * we will convert it to connection id.
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
        return self::CACHE_KEY. (int)sprintf("%u", crc32((string)$id));
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
     * @param string $KEY      cache key
     * @param string $response data
     * 
     * @return void
     */
    public function log($label, $uri, $start, $KEY, $response)
    {
        $this->c['logger']->debug(
            $label.' '.$uri, 
            array(
                'time' => number_format(microtime(true) - $start, 4), 
                'key' => $KEY, 
                'output' => static::LOG_HEADER .substr($response, 0, 50).' ...'. static::LOG_FOOTER
            )
        );
    }

    /**
     * Close Layer Connections
     * 
     * If we have any possible Layer exceptions
     * reset the router variables and restore all objects
     * to complete Layer process. Otherwise we see 
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

// END Layer class

/* End of file Request.php */
/* Location: .Obullo/Layer/Layer.php */