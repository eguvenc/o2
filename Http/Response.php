<?php

namespace Obullo\Http;

use Obullo\Http\Response\Headers;
use Obullo\Container\ContainerInterface;

/**
 * Http response Class.
 * 
 * Set Http Response Code
 * Set Outputs
 * Set & Finalize Headers
 * 
 * @category  Http
 * @package   Response
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http
 */
class Response
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
    public $error = null;

    /**
     * Status code
     * 
     * @var int
     */
    public $status = 200;

    /**
     * Final output string
     * 
     * @var string
     */
    public $output;

    /**
     * Enable / Disable flush ( send output to browser )
     * 
     * @var boolean
     */
    protected $enabled = true;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->c['config']->load('response');

        $this->output = '';
        $this->c['response.headers'] = function () {
            return new Headers;
        };
    }

    /**
     * Response headers loader
     * 
     * @param string $variable name
     * 
     * @return object | bool
     */
    public function __get($variable)
    {   
        return $this->c['response.'.$variable];
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
        $this->output = $output;
        $this->length = strlen($this->output);
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
        $this->length = strlen($this->output);
        return $this->output;
    }

    /**
     * Append HTTP response body
     * 
     * @param string $output output
     * 
     * @return string output
     */
    public function append($output = '')
    {
        if ($this->output == '') {
            $this->output = $output;
        } else {
            $this->output.= $output;
        }
        return $this->output;
    }

    /**
     * Get page output length
     * 
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Finalize
     *
     * This prepares this response and returns an array
     * of (status, headers, body).
     *
     * @return array[int status, array headers, string body]
     */
    public function finalize()
    {
        if (in_array($this->status, array(204, 304))) {
            $this->c['response.headers']->remove('Content-Type');
            $this->c['response.headers']->remove('Content-Length');
        }
        $headers = $options = array();
        
        if ($this->c->used('response.headers')) {  // If response headers object loaded

            $headers = $this->c['response.headers']->all();
            $options = $this->c['response.headers']->options();
        }
        return array($this->status, $headers, $options, $this->getOutput());
    }

    /**
     * Send response headers
     * 
     * @param integer $status  http response code
     * @param array   $headers http headers
     * @param array   $options header replace option
     * 
     * @return object
     */
    public function sendHeaders($status, $headers, $options)
    {
        if (headers_sent() === false) {   // Send headers

            http_response_code($status);

            if (count($headers) > 0) {  // Are there any server headers to send ?
                $replace = true;
                foreach ($headers as $header => $value) {
                    if (isset($options[$header]['replace'])) {
                        $replace = $options[$header]['replace'];
                    }
                    if (strpos($header, ':') === false && ! empty($value)) {
                        $header = $header.': '.$value;
                    }
                    header($header, $replace);
                }            
            }
        }
        return $this;
    }

    /**
     * Send headers and echo output
     * 
     * @return object
     */
    public function flush()
    {
        if ($this->enabled) {  // Send output
            list($status, $headers, $options, $output) = $this->finalize();
            $this->sendHeaders($status, $headers, $options);

            echo $output; // Send output
        }
        return $this;
    }

    /**
    * Set HTTP Status Header
    * 
    * @param int $code the status code
    * 
    * @return object
    */    
    public function status($code = 200)
    {
        $this->status = $code;
        return $this;
    }

    /**
     * Returns to response status
     * 
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
    * 404 Page Not Found Handler
    *
    * @param string $page page name
    * 
    * @return void|string
    */
    public function show404($page = '')
    {
        $error = new Error($this->c, $this);
        return $error->show404($page);
    }

    /**
    * Manually Set General Http Errors
    *
    * @param string $message message
    * @param int    $heading heading text
    *
    * @return void|string
    */
    public function showError($message, $heading = 'An Error Was Encountered')
    {
        $status = ($this->getStatus() == 200) ? 500 : $this->getStatus();
        $error = new Error($this->c, $this);
        return $error->showError($message, $status, $heading);
    }

    /**
     * Encode json data and set json headers.
     * 
     * @param array  $data   array data
     * @param string $header config header
     * 
     * @return string json encoded data
     */
    public function json(array $data, $header = 'default')
    {
        $this->disableOutput(); // Disable output, we need to send json headers.

        if ($header != false) {
            if (isset($this->c['config']['response']['headers']['json'][$header])) {  //  If selected headers defined in the response config set headers.
                foreach ($this->c['config']['response']['headers']['json'][$header] as $value) {
                    $this->headers->set($value);
                }
            }
            list($status, $headers, $options) = $this->finalize();
            $this->sendHeaders($status, $headers, $options);
        }
        return json_encode($data);
    }

    /**
     * Enables write output method
     * 
     * @return object
     */
    public function enableOutput()
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * Disables write output method
     * 
     * @return object
     */
    public function disableOutput()
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * Returns to true output enabled otherwise false
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
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
     * Clear error variables for layer requests
     * 
     * @return object
     */
    public function clear()
    {
        $this->error = null;
        return $this;
    }

}

// END Response.php File
/* End of file Response.php

/* Location: .Obullo/Http/Response.php */