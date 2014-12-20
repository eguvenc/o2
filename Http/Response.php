<?php

namespace Obullo\Http;

use Controller;

/**
 * Response Class.
 * 
 * Set Http Response, Set Output Errors
 * Get Output
 * 
 * @category  Http
 * @package   Response
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http/response
 */
Class Response
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Response last error
     * 
     * @var string
     */
    public $error;

    /**
     * Logger
     * 
     * @var object
     */
    public $logger;

    /**
     * Final output
     * 
     * @var string
     */
    public $finalOutput;

    /**
     * Compression switch
     * 
     * @var boolean
     */
    public $outputCompression = true;

    /**
     * Php headers
     * 
     * @var array
     */
    public $headers = array();

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->finalOutput = '';
        $this->logger = $this->c->load('return service/logger');
        $this->logger->debug('Response Class Initialized');
    }

    /**
    * Set Output
    *
    * Sets the output string
    *
    * @param string $output output
    * 
    * @return object response
    */    
    public function setOutput($output)
    {
        $this->finalOutput = $output;
        return $this;
    }

    /**
     * Append Output
     *
     * Appends data onto the output string
     *
     * @param string $output output
     * 
     * @return object response
     */
    public function appendOutput($output)
    {
        if ($this->finalOutput == '') {
            $this->finalOutput = $output;
        } else {
            $this->finalOutput.= $output;
        }
        return $this;
    }

    /**
    * Get Output
    *
    * Returns the current output string
    *
    * @return string
    */    
    public function getOutput()
    {
        return $this->finalOutput;
    }

    /**
     * Enable Compress Output Header
     * 
     * @return object response
     */
    public function compressOutput() 
    {
        if (extension_loaded('zlib')
            AND isset($_SERVER['HTTP_ACCEPT_ENCODING'])
            AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false
        ) {
            ob_start('ob_gzhandler');
        }
        return $this;
    }

    /**
     * Display Output
     *
     * This function sends the finalized output data to the browser along
     * with any server headers and profile data.  It also stops the
     * benchmark timer so the page rendering speed and memory usage can be shown.
     *
     * @param string $output output
     * 
     * @return string output
     */
    public function sendOutput($output = '')
    {
        if ($output == '') {                    // Set the output data
            $output = & $this->finalOutput;
        }
        if ($this->c['config']['output']['compress']  // Is compression requested ?
            AND $this->outputCompression == true
        ) {
            $this->compressOutput();
        }
        if (count($this->headers) > 0 AND ! headers_sent()) {  // Are there any server headers to send ?
            foreach ($this->headers as $header) {
                header($header[0], $header[1]);
            }            
        }
        if (class_exists('Controller', false) AND method_exists(Controller::$instance, '_response')) {    // Does the controller contain a function named _response()?
            Controller::$instance->_response($output);              // If so send the output there.  Otherwise, echo it.
            return;
        } 
        echo $output;  // Send it to the browser!
    }
    
    /**
    * Set HTTP Status Header
    * 
    * @param int $code the status code
    * 
    * @return object
    */    
    public function setHttpResponse($code = 200)
    {
        http_response_code($code);  // Php >= 5.4.0
        return $this;
    }

    /**
    * Set Header
    *
    * Lets you set a server header which will be outputted with the final display.
    *
    * Note:  If a file is cached, headers will not be sent.  We need to figure out
    * how to permit header data to be saved with the cache data.
    *
    * @param string  $header  header
    * @param boolean $replace replace override header
    * 
    * @return object response
    */    
    public function setHeader($header, $replace = true)
    {
        // If zlib.output_compression is enabled it will compress the output,
        // but it will not modify the content-length header to compensate for
        // the reduction, causing the browser to hang waiting for more data.
        // We'll just skip content-length in those cases.
        if (@ini_get('zlib.output_compression') AND strncasecmp($header, 'content-length', 14) == 0) {
            return;
        }
        $this->headers[] = array($header, $replace);
        return $this;
    }

    /**
    * 404 Page Not Found Handler
    *
    * @param string  $page    page name
    * @param boolean $http404 http 404 or layer 404
    * 
    * @return string
    */
    public function show404($page = '', $http404 = true)
    {
        $error = new Error($this->c, $this);
        $error->show404($page, $http404);
    }

    /**
    * Manually Set General Http Errors
    *
    * @param string $message    message
    * @param int    $statusCode status
    * @param int    $heading    heading text
    *
    * @return void
    */
    public function showError($message, $statusCode = 500, $heading = 'An Error Was Encountered')
    {
        $error = new Error($this->c, $this);
        $error->showError($message, $statusCode, $heading);
    }

    /**
     * Show user friendly n messages
     * 
     * @param string $message message
     * 
     * @return string error
     */
    public function showWarning($message)
    {
        $error = new Error($this->c, $this);
        return $error->showWarning($message);
    }

    /**
     * Show user friendly n messages
     * 
     * @param string $message message
     * 
     * @return string error
     */
    public function showNotice($message)
    {
        $error = new Error($this->c, $this);
        return $error->showNotice($message);
    }

    /**
     * Encode json data and set json headers.
     * 
     * @param array   $data    array data
     * @param boolean $headers disable json headers
     * 
     * @return string json encoded data
     */
    public function json(array $data, $headers = true)
    {
        $this->outputCompression = false;  // Default false for json response
        if ($headers) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-type: application/json;charset=UTF-8');
        }
        return json_encode($data);
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
     * Clear variables.
     * 
     * @return void
     */
    public function clear()
    {
        $this->error = null;
    }

}

// END Response.php File
/* End of file Response.php

/* Location: .Obullo/Http/Response.php */