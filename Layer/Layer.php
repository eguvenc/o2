<?php

namespace Obullo\Layer;

use stdClass,
    Controller;

/**
 * Layers ( Layered View Controller ) is a programming technique that delivers you to
 * "Multitier Architecture" to scale your applications.
 * 
 * Derived from HMVC pattern and named as "Layers" in Obullo.
 * 
 * Copyright (c) 2009 - 2014 Ersin Guvenc
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Layer Class
 * 
 * @category  Layer
 * @package   Layer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/layer
 */
Class Layer
{
    public static $app;

    const CACHE_KEY = 'Layer:';
    const LOG_HEADER = '<br /><div style="float:left;">';
    const LOG_FOOTER = '</div><div style="clear:both;"></div>';

    /**
     * Layered Vc configuration
     * 
     * @var array
     */
    public $config = array();

    /**
     * Uri class
     * 
     * @var object
     */
    public $uri    = null;

    /**
     * Router class
     * 
     * @var object
     */
    public $router = null;

    /**
     * Logger class
     * 
     * @var object
     */
    public $logger = null;

    /**
     * Request method
     * 
     * @var string
     */
    public $requestMethod = 'GET';    

    /**
     * Request data
     * 
     * @var array
     */
    public $requestData = array();

    /**
     * Process flag
     * 
     * @var boolean
     */
    public $processDone = false;

    /**
     * Unique Lvc connection string that 
     * we need to convert it md5.
     *  
     * @var string
     */
    protected $hashString = null;

    /**
     * Layer uri string
     * 
     * @var string
     */
    protected $layerUri;

    /**
     * Container
     * 
     * @var object
     */
    protected $c = null;

    /**
     * Gobal instance of the controller 
     * we need to clone it.
     * 
     * @var object
     */
    protected $global = null;

    /**
     * Reset all variables for multiple
     * Layered Vc requests.
     *
     * @return void
     */
    public function clear()
    {
        $this->c['response']->clear();
        $this->processDone = false;
        $this->requestMethod = 'GET';
        // $GLOBALS['_SERVER_BACKUP']  = array();
        unset($_SERVER['LAYER_REQUEST'], $_SERVER['LAYER_REQUEST_URI'], $_SERVER['LAYER_REQUEST_METHOD']);
    }

    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params config 
     *
     * @return void
     */
    public function __construct($c, $params)
    {
        $this->c = $c;
        $this->params = $params;
        $this->logger = $c->load('service/logger');
        register_shutdown_function(array($this, 'close'));  // Close current layer
    }

    /**
     * Set request headers
     *
     * @return void
     */
    public function setHeaders()
    {
        $_SERVER['LAYER_REQUEST'] = true;   // Set Hvc Headers
        // $GLOBALS['_SERVER_BACKUP'] = $_SERVER; // Get backup $_SERVER variable
        // unset($_SERVER['HTTP_ACCEPT'], $_SERVER['REQUEST_METHOD']);    // Don't touch other global server items 
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
        $this->hashString = '';     // Reset hash string otherwise it causes unique id errors.
        $_SERVER['LAYER_REQUEST_URI'] = trim($uriString, '/'); // Set uri string to $_SERVER GLOBAL
        $this->prepareHash($_SERVER['LAYER_REQUEST_URI']);

        $this->global = Controller::$instance;     // We need get backup object of main controller
        $this->uri    = Controller::$instance->uri;     // Create copy of original Uri class.
        $this->router = Controller::$instance->router;  // Create copy of original Router class.

        $this->originalGlobal = clone $this->global;
        $this->originalUri = clone $this->uri;
        $this->originalRouter = clone $this->router;

        $this->c['uri']->clear();           // Reset uri objects we will reuse it for layer
        $this->c['router']->clear();        // Reset router objects we will reuse it for layer
        $this->c['uri']->setUriString($_SERVER['LAYER_REQUEST_URI']);
        $this->c['router']->init();
    }

    /**
     * Set Layer Request Method
     *
     * @param string $method lvc method
     * @param array  $data   params
     * 
     * @return void
     */
    public function setMethod($method, $data = array())
    {
        if (empty($data)) {
            $data = array();
        }
        $this->prepareHash($data); // Set md5 Unique id foreach requests
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
        // Warning ! 
        global $c; // This is required for LAYERS "$c" scope if we remove it controller function use ($c) does not works.
        static $storage = array();  // Store used "$app " variables

        $KEY = $this->id();    // Get layer id
        $start = microtime(true);   // Start query timer 

        if ($this->params['cache']) {
            $response = $c->load('service/cache')->get($KEY);     // This type cache use cache package
            if ( ! empty($response)) {              // If cache exists return to cached string.
                $this->log('$_LAYER_CACHED:', $this->c['uri']->getUriString(), $start, $KEY, $response);
                $this->reset();
                return base64_decode($response);    // Encode specialchars
            }
        }
        if ($this->c['response']->getError() != '') {  // If router dispatch fail ?
            $this->reset();
            return $this->c['response']->getError();
        }
        //  Create an uniq Layer Uri it must be unique otherwise may cause collission with standart uri
        $this->c['uri']->setUriString(rtrim($this->c['uri']->getUriString(), '/') . '/' .$KEY); // Create a uniq Lvc Uri String with Layer ID
        $directory = $this->c['router']->fetchDirectory();
        $className = $this->c['router']->fetchClass();

        $this->layerUri = $this->c['router']->fetchTopDirectory().'/'.$directory.'/'.$className;
        $controller = PUBLIC_DIR .$this->c['router']->fetchTopDirectory(DS).$directory. DS .'controller'. DS .$className. '.php';
                                                    
                                                  // Check class is exists in the storage
        if (isset($storage[$this->layerUri])) {   // Don't allow multiple include.
            $class = $storage[$this->layerUri];     // Get stored class.
        } else {
            include $controller;        // Load the controller file.
        }

        $class = new $className;  // Call the controller

        if (method_exists($class, 'load')) {
            $class->load();
        }

        if ( ! method_exists($class, 'index')) {  // Check method exist or not
            $this->reset();
            return $this->c['response']->show404($this->layerUri, false);
        }

        $this->makeGlobal();
        $this->assignObjects($class); // Assign main controller objects to sub layers.

        ob_start();
        call_user_func_array(array($class, 'index'), array_slice($this->c['uri']->rsegments, 2));
        $response = ob_get_clean();
                                          // Store classes to $storage container
        $storage[$this->layerUri] = $class; // Store class names to storage. We fetch it if its available in storage.
        
        if (is_numeric($expiration)) {
            $c->load('service/cache')->set($KEY, base64_encode($response), (int)$expiration); // Write to Cache
        }
        $this->log('$_LAYER:', $this->getUri(), $start, $KEY, $response);
        return $response;
    }

    /**
     * Assign libraries to all Layers
     * 
     * @param object $class called controller
     * 
     * @return void
     */
    protected function assignObjects($class)
    {
        $instance = $this->c['request']->globals->global;  // Assign loaded libraries to called controller.
        unset(
            $instance->uri,
            $instance->router,
            $instance->config,
            $instance->logger,
            $instance->response
        );
        foreach ($this->c['request']->globals->global as $key => $value) {
            $class->{$key} = $value;
        }
    }

    /**
     * Make global objects in request class
     * 
     * @return void
     */
    protected function makeGlobal()
    {
        $request = $this->c['request'];
        if ( ! isset($request->globals)) {
            $request->globals = new stdClass;
            $request->globals->uri = $this->originalUri;
            $request->globals->router = $this->originalRouter;
            $request->globals->global = $this->originalGlobal;
        }
    }

    /**
     * Reset router for mutiple layer requests
     * and close the layer connections.
     *
     * @return   void
     */
    protected function reset()
    {
        if ( ! isset($_SERVER['LAYER_REQUEST_URI'])) { // If no layer header return to null;
            return;
        }
        // $_SERVER = array();                     // Assign global variables we get backup before.
        // $_SERVER = $GLOBALS['_SERVER_BACKUP'];  // Just reset server variable otherwise we couldn't use global variables layer in layer loops.
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
        Controller::$instance = $this->global;
        Controller::$instance->uri = $this->originalUri;
        Controller::$instance->router = $this->originalRouter;
        $this->processDone = true;
    }

    /**
     * Create Lvc connection string next
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
    public function id()
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
        $this->logger->debug(
            $label.' '.$uri, 
            array(
                'time' => number_format(microtime(true) - $start, 4), 
                'key' => $KEY, 
                'output' => static::LOG_HEADER .preg_replace('/[\r\n\t]+/', '', $response). static::LOG_FOOTER
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