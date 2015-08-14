<?php

namespace Obullo\Http\Response;

use Closure;

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
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http
 */
interface ResponseInterface
{
    /**
    * Set Output
    *
    * Sets the output string
    *
    * @param string $output output
    * 
    * @return object response
    */    
    public function setOutput($output);

    /**
    * Get Output
    *
    * Returns the current output string
    *
    * @return string
    */    
    public function getOutput();

    /**
     * Append HTTP response body
     * 
     * @param string $output output
     * 
     * @return string output
     */
    public function append($output = '');

    /**
     * Get content length
     * 
     * @return int
     */
    public function getLength();

    /**
     * Finalize
     *
     * This prepares this response and returns an array
     * of (status, headers, body).
     *
     * @return array[int status, array headers, string body]
     */
    public function finalize();

    /**
     * Send response headers
     * 
     * @param integer $status  http response code
     * @param array   $headers http headers
     * @param array   $options header replace option
     * 
     * @return object
     */
    public function sendHeaders($status, $headers, $options);

    /**
     * Send headers and echo output
     * 
     * @return object
     */
    public function flush();

    /**
     * Set custom response function
     *
     * @param object $closure callback
     * 
     * @return object
     */
    public function callback(Closure $closure);

    /**
    * Set HTTP Status Header
    * 
    * @param int $code the status code
    * 
    * @return object
    */    
    public function status($code = 200);

    /**
     * Returns to response status
     * 
     * @return int
     */
    public function getStatus();

    /**
    * 404 Page Not Found Handler
    *
    * @param string $page page name
    * 
    * @return void|string
    */
    public function show404($page = '');

    /**
    * Manually Set General Http Errors
    *
    * @param string $message message
    * @param int    $heading heading text
    *
    * @return void|string
    */
    public function showError($message, $heading = 'An Error Was Encountered');

    /**
     * Encode json data and set json headers.
     * 
     * @param array  $data   array data
     * @param string $header config header
     * 
     * @return string json encoded data
     */
    public function json(array $data, $header = 'default');

    /**
     * Enables write output method
     * 
     * @return object
     */
    public function enableOutput();

    /**
     * Disables write output method
     * 
     * @return object
     */
    public function disableOutput();

    /**
     * Returns to true if output enabled otherwise false
     * 
     * @return boolean
     */
    public function isAllowed();

    /**
     * Set last response error
     *
     * @param string $error message
     * 
     * @return object
     */
    public function setError($error);

    /**
     * Get last response error
     * 
     * @return string
     */
    public function getError();

    /**
     * Clear error variables for layer requests
     * 
     * @return object
     */
    public function clear();

    /**
     * Response headers loader
     * 
     * @param string $variable name
     * 
     * @return object | bool
     */
    public function __get($variable);
}