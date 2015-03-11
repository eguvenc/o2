<?php

namespace Obullo\Cookie;

use Obullo\Container\Container;

/**
 * Control cookie set, get, delete and queue operations
 * 
 * @category  Cookie
 * @package   Cookie
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cookie
 */
Class Cookie
{
    /**
     * Config
     * 
     * @var object
     */
    protected $config;

    /**
     * Queued cookies
     * 
     * @var array
     */
    protected $queued = array();

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $this->c['config'];
        $this->logger = $this->c['logger'];

        $this->logger->debug('Cookie Class Initialized');
    }

    /**
     * Set cookie
     *
     * Accepts six parameter, or you can submit an associative
     * array in the first parameter containing all the values.
     * 
     * @param string  $name     cookie name
     * @param string  $value    cookie value
     * @param integer $expire   expire
     * @param string  $domain   domain e.g. .example.com
     * @param string  $path     null default "/"
     * @param boolean $secure   whether to https enabled cookie
     * @param boolean $httpOnly when true the cookie will be made accessible only through the HTTP protocol
     * @param string  $prefix   cookie prefix
     *
     * @return void
     */
    public function set($name, $value = '', $expire = 0, $domain = '', $path = null, $secure = false, $httpOnly = false, $prefix = '')
    {
        $name = $this->getParameters($this->buildParameters($name, $value, $expire, $domain, $path, $secure, $httpOnly, $prefix));
        return $this->write($name);
    }
    
    /**
     * Write cookie to headers
     * 
     * @param array $cookie params
     * 
     * @return void
     */
    public function write(array $cookie)
    {
        $this->c['logger']->alert('Cookie values', array($cookie));

        setcookie(
            $cookie['prefix'].$cookie['name'],
            $cookie['value'],
            $cookie['expire'],
            $cookie['path'],
            $cookie['domain'],
            $cookie['secure'],
            $cookie['httpOnly']
        );
    }

    /**
     * Build cookie parameters
     * 
     * @param string  $name     cookie name
     * @param string  $value    cookie value
     * @param integer $expire   expire
     * @param string  $domain   domain e.g. .example.com
     * @param string  $path     null default "/"
     * @param boolean $secure   whether to https enabled cookie
     * @param boolean $httpOnly when true the cookie will be made accessible only through the HTTP protocol
     * @param string  $prefix   cookie prefix
     * 
     * @return array
     */
    protected function buildParameters($name, $value = '', $expire = 0, $domain = '', $path = null, $secure = false, $httpOnly = false, $prefix = '')
    {
        if (is_array($name)) {
            $cookie = $name;
            foreach ($cookie as $k => $v) {
                if (in_array($k, array('name','value','expire','domain','path','secure','httpOnly','prefix'))) {
                    $$k = $v;
                }
            }
        }
        $prefixVal = empty($prefix) ? $this->config['cookie']['prefix'] : $prefix;
        return array(
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
            'domain' => $domain,
            'path' => ($path == null) ? $this->config['cookie']['path'] : $path,
            'secure' => $secure,
            'httpOnly' => $httpOnly,
            'prefix' => $prefixVal,
        );
    }

    /**
     * Build parameters
     *
     * @param array $params cookie params
     * 
     * @return array
     */
    protected function getParameters(array $params)
    {
        if ($params['domain'] == '' AND $this->config['cookie']['domain'] != '') {
            $params['domain'] = $this->config['cookie']['domain'];
        }
        if ($params['secure'] == false AND $this->config['cookie']['secure'] != false) {
            $params['secure'] = $this->config['cookie']['secure'];
        }
        if ( ! is_numeric($params['expire']) AND is_numeric($this->config['cookie']['expire'])) {
            $params['expire'] = $this->config['cookie']['expire'];
        }
        if ($params['httpOnly'] == false AND $this->config['cookie']['httpOnly'] != false) {
            $params['httpOnly'] = $this->config['cookie']['httpOnly'];
        }
        $params['expire'] = $this->getExpiration($params['expire']);
        return $params;
    }

    /**
     * Get expiration of cookie
     * 
     * @param int $expire in second
     * 
     * @return int
     */
    protected function getExpiration($expire)
    {
        if ( ! is_numeric($expire)) {
            $expire = time() - 86500;
        } else {
            if ($expire > 0) {
                $expire = time() + $expire;
            }
        }
        return $expire;
    }

    /**
     * Get cookie
     * 
     * @param string $key cookie key
     * 
     * @return string sanizited cookie
     */
    public function get($key)
    {
        $prefix = '';
        if ( ! isset($_COOKIE[$key]) AND $this->config['cookie']['prefix'] != '') {
            $prefix = $this->config['cookie']['prefix'];
        }
        if ( ! isset($_COOKIE[$prefix.$key])) {
            return false;
        }
        return $_COOKIE[$prefix.$key];
    }

    /**
    * Delete a cookie
    *
    * @param string $name   cookie
    * @param string $domain the cookie domain.  Usually:  ".yourdomain.com"
    * @param string $path   the cookie path     generally "/""
    * 
    * @return   void
    */
    public function delete($name = '', $domain = '', $path = '/')
    {
        $this->set($name, '', '', $domain, $path);
    }

    /**
     * Queue a cookie to send with the next response.
     *
     * Accepts six parameter, or you can submit an associative
     * array in the first parameter containing all the values.
     * 
     * @param string  $name     cookie name
     * @param string  $value    cookie value
     * @param integer $expire   expire
     * @param string  $domain   domain e.g. .example.com
     * @param string  $path     null default "/"
     * @param boolean $secure   whether to https enabled cookie
     * @param boolean $httpOnly when true the cookie will be made accessible only through the HTTP protocol
     * @param string  $prefix   cookie prefix
     *
     * @return void
     */
    public function queue($name, $value = '', $expire = 0, $domain = '', $path = null, $secure = false, $httpOnly = false, $prefix = '')
    {
        $cookie = $this->getParameters($this->buildParameters($name, $value, $expire, $domain, $path, $secure, $httpOnly, $prefix));

        $this->c['logger']->error('Cookie sent to queue', array('cookie' => $cookie));

        if (is_string($name)) {
            $this->queued[$name] = $cookie;
        } elseif (is_array($name) AND isset($name['name'])) {
            $this->queued[$name['name']] = $cookie;
        }
    }

    /**
     * Get a queued cookie array
     *
     * @param string $name cookie name
     * 
     * @return string
     */
    public function queued($name)
    {
        return isset($this->queued[$name]) ? $this->queued[$name]['value'] : false;
    }

    /**
     * Remove a cookie from the queue.
     *
     * @param string $name cookie name
     *
     * @return void
     */
    public function unqueue($name)
    {
        unset($this->queued[$name]);
    }

    /**
     * Get the cookies which have been queued for the next request
     *
     * @return array
     */
    public function getQueuedCookies()
    {
        return $this->queued;
    }

}

// END Cookie.php File
/* End of file Cookie.php

/* Location: .Obullo/Cookie/Cookie.php */