<?php

namespace Obullo\Http\Request;

/**
 * RequestInterface Class
 * 
 * @category  Http
 * @package   Request
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http/request
 */
interface RequestInterface
{
    /**
     * GET wrapper
     * 
     * @param string  $key    key
     * @param boolean $filter name
     * 
     * @return mixed
     */
    public function get($key, $filter = null);

    /**
     * POST wrapper
     * 
     * @param string  $key    key
     * @param boolean $filter name
     * 
     * @return mixed
     */
    public function post($key, $filter = null);

    /**
     * REQUEST wrapper
     * 
     * @param string  $key    key
     * @param boolean $filter name
     * 
     * @return mixed
     */
    public function all($key, $filter = null);

    /**
     * Get $_SERVER variable items
     * 
     * @param string $key server key
     * 
     * @return void
     */
    public function server($key);

    /**
     * Get server request method 
     * 
     * @return string | bool
     */
    public function method();

    /**
     * Get ip address
     * 
     * @return string
     */
    public function getIpAddress();
    
    /**
     * Validate IP adresss
     * 
     * @param string $ip ip address
     * 
     * @return boolean
     */
    public function isValidIp($ip);

    /**
     * Is Cli ?
     *
     * Test to see if a request was made from the command line.
     *
     * @return bool
     */
    public function isCli();
    
    /**
     * Detect the layered vc requests
     * 
     * @return boolean
     */
    public function isLayer();

    /**
     * Detect the request is xmlHttp ( Ajax )
     * 
     * @return boolean
     */
    public function isAjax();

    /**
     * Detect the connection is secure ( Https )
     * 
     * @return boolean
     */
    public function isSecure();

    /**
     * If http request type equal to POST returns to true otherwise false.
     * 
     * @return boolean
     */
    public function isPost();

    /**
     * If http request type equal to GET returns true otherwise false.
     * 
     * @return boolean
     */
    public function isGet();

    /**
     * If http request type equal to PUT returns to true otherwise false.
     * 
     * @return boolean
     */
    public function isPut();

    /**
     * If http request type equal to PATCH returns to true otherwise false.
     * 
     * @return boolean
     */
    public function isPatch();

    /**
     * Check method is head
     * 
     * @return boolean
     */
    public function isHead();

    /**
     * Check method is options
     * 
     * @return boolean
     */
    public function isOptions();

    /**
     * If http request type equal to DELETE returns to true otherwise false.
     * 
     * @return boolean
     */
    public function isDelete();

    /**
     * Check method private function
     * 
     * @param string $METHOD GET, POST, PUT, DELETE
     * 
     * @return boolean
     */
    public function isMethod($METHOD = 'GET');

    /**
     * Request headers loader
     * 
     * @param string $variable name
     * 
     * @return object | bool
     */
    public function __get($variable);

}