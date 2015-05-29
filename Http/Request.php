<?php

namespace Obullo\Http;

use Obullo\Http\InputFilter;
use Obullo\Container\Container;
use Obullo\Http\Request\Headers;

/**
 * Request Class
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
     * Constructor
     *
     * @param array $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->c['logger']->debug('Request Class Initialized');
        $this->c['request.headers'] = function () {
            return new Headers;
        };
        $this->c['request.filter'] = function () use ($c) {
            return new InputFilter($c);
        };
    }

    /**
     * Request headers loader
     * 
     * @param string $variable name
     * 
     * @return object | bool
     */
    public function __get($variable)
    {   
        return $this->c['request.'.$variable];
    }

    /**
     * GET wrapper
     * 
     * @param string  $key    key
     * @param boolean $filter name
     * 
     * @return mixed
     */
    public function get($key, $filter = null)
    {
        if (is_bool($key)) {
            return $_GET;
        }
        $value = isset($_GET[$key]) ? $_GET[$key] : false;
        if (is_string($filter)) {
            return $this->c['request.filter']->setFilter($filter)->setValue($value);
        }
        return $value;
    }

    /**
     * POST wrapper
     * 
     * @param string  $key    key
     * @param boolean $filter name
     * 
     * @return mixed
     */
    public function post($key, $filter = null)
    {
        if (is_bool($key)) {
            return $_POST;
        }
        $value = isset($_POST[$key]) ? $_POST[$key] : false;
        if (is_string($filter)) {
            return $this->c['request.filter']->setFilter($filter)->setValue($value);
        }
        return $value;
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
        $value = isset($_REQUEST[$key]) ? $_REQUEST[$key] : false;
        if (is_string($filter)) {
            return $this->c['request.filter']->setFilter($filter)->setValue($value);
        }
        return $value;
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
            return strtoupper($_SERVER['REQUEST_METHOD']);
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
            $ipAddress = ($spoof !== false && in_array($REMOTE_ADDR, $proxyIps, true)) ? $spoof : $REMOTE_ADDR;
        }
        return $ipAddress;
    }

    
    /**
     * Validate IP adresss
     * 
     * @param string $ip ip address
     * 
     * @return boolean
     */
    public function isValidIp($ip)
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * Is Cli ?
     *
     * Test to see if a request was made from the command line.
     *
     * @return  bool
     */
    public function isCli()
    {
        return $this->c['app']->isCli();
    }
    
    /**
     * Detect the layered vc requests
     * 
     * @return boolean
     */
    public function isLayer()
    {
        if (isset($_SERVER['LAYER_REQUEST']) && $_SERVER['LAYER_REQUEST'] == true) {
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
        if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
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
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == $METHOD) {
            return true;
        }
        return false;
    }

}

// END Request class

/* End of file Request.php */
/* Location: .Obullo/Http/Request.php */