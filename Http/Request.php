<?php

namespace Obullo\Http;

use stdClass,
    Obullo\Http\Sanitizer;

/**
 * Request Class
 * 
 * Fetch variables from $_REQUEST global and 
 * Get Http Request Headers
 * 
 * @category  Http
 * @package   Request
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http/request
 */
Class Request
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Logger instance 
     * 
     * @var object
     */
    public $logger;

    /**
     * Http request headers
     * 
     * @var array
     */
    public $headers;

    /**
     * Constructor
     *
     * @param array $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->logger = $this->c->load('service/logger');
        $this->logger->debug('Request Class Initialized');
    }

    /**
     * Get global object, we store original global objects( uri and router ) into 
     * $this->global variable then we able to grab them from all layers.
     * 
     * @param string $key variable
     * 
     * @return void
     */
    public function __get($key)
    {
        if ($key != 'global') {
            return null;
        }
        if (is_object($this->globals)) {
            return $this->globals;
        }
        $this->globals = new stdClass;
        $this->globals->uri = $this->c->load('uri');
        $this->globals->router = $this->c->load('router');
        return $this->globals;
    }

    /**
     * GET wrapper
     * 
     * @param string $key key
     * 
     * @return void
     */
    public function get($key)
    {
        if ( ! isset($_GET[$key])) {
            return false;
        }
        return Sanitizer::sanitize($_GET[$key]);
    }

    /**
     * POST wrapper
     * 
     * @param string $key key
     * 
     * @return void
     */
    public function post($key)
    {
        if ( ! isset($_POST[$key])) {
            return false;
        }
        return Sanitizer::sanitize($_POST[$key]);
    }

    /**
     * Get server request method 
     * 
     * @return string | bool
     */
    public function method()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return $_SERVER['REQUEST_METHOD'];
        }
        return false;
    }

    /**
     * Get Header
     * e.g. echo $this->request->getHeader('Host');  // demo_blog
     *
     * @param string $key header key
     *
     * @link http://tr1.php.net/manual/en/function.getallheaders.php
     * 
     * @return string | boolean
     */
    public function header($key = 'Host')
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {  // If http server is not Apache ?
            $headers = '';
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$name] = $value;
                }
            }
        }
        foreach ($headers as $name => $val) {  // Backup the lowercase format each of keys
            $name = strtolower($name);
            $headers[$name] = $val;
        }
        if (isset($headers[$key])) { // get selected header
            return $headers[$key];
        }
        return false;
    }

    /**
     * Get $_SERVER variable items
     * 
     * @param string $key server key
     * 
     * @return void
     */
    public function server($key) 
    {
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        return null;
    }

    /**
     * Get ip address
     * 
     * @return string
     */
    public function ip()
    {
        static $ipAddress = '';
        $REMOTE_ADDR = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        if ($ipAddress != '') {
            return $ipAddress;
        }
        $ipAddress = $REMOTE_ADDR;
        $proxyIps  = $this->c['config']['proxy']['ips'];
        
        if ( ! empty($proxyIps)) {
            $proxyIps = explode(',', str_replace(' ', '', $proxyIps));
            foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $header) {
                $spoof = (isset($_SERVER[$header])) ? $_SERVER[$header] : false;
                if ($spoof !== false) {
                    if (strpos($spoof, ',') !== false) {   // Some proxies typically list the whole chain of IP addresses through which the client has reached us.
                        $spoof = explode(',', $spoof, 2);  // e.g. client_ip, proxy_ip1, proxy_ip2, etc.
                        $spoof = $spoof[0];
                    }
                    if (!$this->isValidIp($spoof)) {
                        $spoof = false;
                    } else {
                        break;
                    }
                }
            }
            $ipAddress = ($spoof !== false AND in_array($REMOTE_ADDR, $proxyIps, true)) ? $spoof : $REMOTE_ADDR;
        }
        if ( ! $this->isValidIp($ipAddress)) {
            $ipAddress = '0.0.0.0';
        }
        return $ipAddress;
    }
    
    /**
     * Validate IP adresss
     * 
     * @param string $ip    ip address
     * @param string $which flag
     * 
     * @return boolean
     */
    public function isValidIp($ip, $which = '')
    {
        $which = strtolower($which);
        switch ($which) {
        case 'ipv4':
            $flag = FILTER_FLAG_IPV4;
            break;
        case 'ipv6':
            $flag = FILTER_FLAG_IPV6;
            break;
        default:
            $flag = '';
            break;
        }
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flag);
    }

    /**
     * Detect the layered vc requests
     * 
     * @return boolean
     */
    public function isLayer()
    {
        if (isset($_SERVER['LAYER_REQUEST']) AND $_SERVER['LAYER_REQUEST'] == true) {
            return true;
        }
        return false;
    }

    /**
     * Detect the request is xmlHttp ( Ajax )
     * 
     * @return boolean
     */
    public function isAjax()
    {
        if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return false;
    }

    /**
     * Detect the connection is secure ( Https )
     * 
     * @return boolean
     */
    public function isSecure()
    {
        if ( ! isset($_SERVER['https']) OR $_SERVER['https'] != 'on') {
            return false;
        }
        return true;
    }

    /**
     * If http request type equal to POST returns to true otherwise false.
     * 
     * @return boolean
     */
    public function isPost()
    {
        return $this->isMethod('POST');
    }

    /**
     * If http request type equal to GET returns true otherwise false.
     * 
     * @return boolean
     */
    public function isGet()
    {
        return $this->isMethod('GET');
    }

    /**
     * If http request type equal to PUT returns to true otherwise false.
     * 
     * @return boolean
     */
    public function isPut()
    {
        return $this->isMethod('PUT');
    }

    /**
     * If http request type equal to DELETE returns to true otherwise false.
     * 
     * @return boolean
     */
    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    /**
     * Check method private function
     * 
     * @param string $METHOD GET, POST, PUT, DELETE
     * 
     * @return boolean
     */
    protected function isMethod($METHOD = 'GET')
    {
        if (isset($_SERVER['REQUEST_METHOD']) AND $_SERVER['REQUEST_METHOD'] == $METHOD) {
            return true;
        }
        return false;
    }

}

// END Request class

/* End of file Request.php */
/* Location: .Obullo/Http/Request.php */