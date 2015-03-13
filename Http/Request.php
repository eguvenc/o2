<?php

namespace Obullo\Http;

use Obullo\Container\Container;

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
class Request
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

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
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->c['logger']->debug('Request Class Initialized');
    }

    /**
     * GET wrapper
     * 
     * @param string $key key
     * 
     * @return mixed
     */
    public function get($key)
    {
        if (is_bool($key)) {
            return $_GET;
        }
        if ( ! isset($_GET[$key])) {
            return false;
        }
        return $_GET[$key];
    }

    /**
     * POST wrapper
     * 
     * @param string $key key
     * 
     * @return mixed
     */
    public function post($key)
    {
        if (is_bool($key)) {
            return $_POST;
        }
        if ( ! isset($_POST[$key])) {
            return false;
        }
        return $_POST[$key];
    }

    /**
     * REQUEST wrapper
     * 
     * @param string $key key
     * 
     * @return mixed
     */
    public function all($key)
    {
        if (is_bool($key)) {
            return $_REQUEST;
        }
        if ( ! isset($_REQUEST[$key])) {
            return false;
        }
        return $_REQUEST[$key];
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
     * Get ip address
     * 
     * @return string
     */
    public function getIpAddress()
    {
        static $ipAddress = '';
        $REMOTE_ADDR = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        if ($ipAddress != '') {
            return $ipAddress;
        }
        $ipAddress = $this->getRealIp($REMOTE_ADDR);

        if ( ! $this->isValidIp($ipAddress)) {
            $ipAddress = '0.0.0.0';
        }
        return $ipAddress;
    }

    /**
     * Get real ip address if we have proxies in front of the web server
     * 
     * @param string $REMOTE_ADDR server remote addr ip
     * 
     * @return string
     */
    protected function getRealIp($REMOTE_ADDR)
    {
        $proxyIps  = $this->c['config']['proxy']['ips'];
        $ipAddress = $REMOTE_ADDR;

        if ( ! empty($proxyIps)) {
            $proxyIps = explode(',', str_replace(' ', '', $proxyIps));
            foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $header) {
                $spoof = (isset($_SERVER[$header])) ? $_SERVER[$header] : false;
                if ($spoof !== false) {
                    if (strpos($spoof, ',') !== false) {   // Some proxies typically list the whole chain of IP addresses through which the client has reached us.
                        $spoof = explode(',', $spoof, 2);  // e.g. client_ip, proxy_ip1, proxy_ip2, etc.
                        $spoof = $spoof[0];
                    }
                    if ( ! $this->isValidIp($spoof)) {
                        $spoof = false;
                    } else {
                        break;
                    }
                }
            }
            $ipAddress = ($spoof !== false AND in_array($REMOTE_ADDR, $proxyIps, true)) ? $spoof : $REMOTE_ADDR;
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
        if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        } elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }
        return false;
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