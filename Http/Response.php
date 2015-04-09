<?php

namespace Obullo\Http;

use Obullo\Container\Container;

/**
 * Http response Class.
 * 
 * Set Http Response Code, Set Outputs, Send Headers
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
     * Php headers
     * 
     * @var array
     */
    public $headers = array();

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
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->c['config']->load('response');

        $this->output = '';
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
            unset($this->headers['Content-Type']);
            unset($this->headers['Content-Length']);
        }
        return array($this->status, $this->headers, $this->getOutput());
    }

    /**
     * Send response headers
     * 
     * @param integer $status  http response code
     * @param array   $headers http headers
     * 
     * @return void
     */
    public function sendHeaders($status, $headers)
    {
        if (headers_sent() === false) {   // Send headers

            http_response_code($status);

            if (count($headers) > 0) {  // Are there any server headers to send ?
                foreach ($headers as $header => $cookie) {
                    header($header, $cookie['replace']);
                }            
            }
        }
    }

    /**
     * Send headers and echo output
     * 
     * @return string
     */
    public function flush()
    {
        if ($this->enabled) {  // Send output
            list($status, $headers, $output) = $this->finalize();
            $this->sendHeaders($status, $headers);

            echo $output; // Send output
        }
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
        $this->headers[$header] = array('replace' => $replace);
        return $this;
    }

    /**
    * 404 Page Not Found Handler
    *
    * @param string  $page    page name
    * @param boolean $http404 http 404 or layer 404
    * 
    * @return void|string
    */
    public function show404($page = '', $http404 = true)
    {
        $error = new Error($this->c, $this);
        return $error->show404($page, $http404);
    }

    /**
    * Manually Set General Http Errors
    *
    * @param string $message    message
    * @param int    $statusCode status
    * @param int    $heading    heading text
    *
    * @return void|string
    */
    public function showError($message, $statusCode = 500, $heading = 'An Error Was Encountered')
    {
        $error = new Error($this->c, $this);
        return $error->showError($message, $statusCode, $heading);
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
        $this->disableOutput(); // Disable output because of we return to json encode and send headers.

        if ($header != false) {
            if (isset($this->c['config']['response']['headers']['json'][$header])) {  //  If selected headers defined in the response config set headers.
                foreach ($this->c['config']['response']['headers']['json'][$header] as $value) {
                    $this->setHeader($value);
                }
            }
            list($status, $headers) = $this->finalize();
            $this->sendHeaders($status, $headers);
        }
        return json_encode($data);
    }

    /**
     * Enables write output method
     * 
     * @return void
     */
    public function enableOutput()
    {
        $this->enabled = true;
    }

    /**
     * Disables write output method
     * 
     * @return void
     */
    public function disableOutput()
    {
        $this->enabled = false;
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
     * @return string
     */
    public function setError($error)
    {
        $this->error = $error;
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