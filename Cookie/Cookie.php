<?php

namespace Obullo\Cookie;

/**
 * Fetches data from $_COOKIE variables
 * 
 * @category  Cookie
 * @package   Cookie
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/cookie
 */
Class Cookie
{
    /**
     * Config
     * 
     * @var object
     */
    public $config;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        
        $this->config = $this->c->load('config');
        $this->logger = $this->c->load('service/logger');
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
     * @param string  $path     /
     * @param string  $prefix   cookie prefix_
     * @param boolean $secure   whether to https enabled cookie
     * @param boolean $httpOnly when true the cookie will be made accessible only through the HTTP protocol
     *
     * @return void
     */
    public function set($name = '', $value = '', $expire = 0, $domain = '', $path = '/', $prefix = '', $secure = false, $httpOnly = false)
    {
        if (is_array($name)) {
            foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'name') as $item) {
                if (isset($name[$item])) {
                    $$item = $name[$item];
                }
            }
        }
        if ($prefix == '' AND $this->config['cookie']['prefix'] != '') {
            $prefix = $this->config['cookie']['prefix'];
        }
        if ($domain == '' AND $this->config['cookie']['domain'] != '') {
            $domain = $this->config['cookie']['domain'];
        }
        if ($path == '/' AND $this->config['cookie']['path'] != '/') {
            $path = $this->config['cookie']['path'];
        }
        if ($secure == false AND $this->config['cookie']['secure'] != false) {
            $secure = $this->config['cookie']['secure'];
        }
        if ($expire == '' AND $this->config['cookie']['expire'] != '') {
            $expire = $this->config['cookie']['expire'];
        }
        if ($httpOnly == false AND $this->config['cookie']['httpOnly'] != false) {
            $httpOnly = $this->config['cookie']['httpOnly'];
        }

        if ( ! is_numeric($expire)) {
            $expire = time() - 86500;
        } else {
            if ($expire > 0) {
                $expire = time() + $expire;
            } else {
                $expire = 0;
            }
        }
        setcookie($prefix.$name, $value, $expire, $path, $domain, $secure, $httpOnly);
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
        if ( ! isset($_COOKIE[$key]) AND $this->config['cookie_prefix'] != '') {
            $prefix = $this->config['cookie']['prefix'];
        }
        if ( ! isset($_COOKIE[$prefix.$key])) {
            return false;
        }
        return $this->c->load('http/sanitizer')->sanitize($_COOKIE[$prefix.$key]);
    }

    /**
    * Delete a COOKIE
    *
    * @param string $name   cookie
    * @param string $domain the cookie domain.  Usually:  ".yourdomain.com"
    * @param string $path   the cookie path     generally "/""
    * @param string $prefix the cookie prefix  
    * 
    * @return   void
    */
    public function delete($name = '', $domain = '', $path = '/', $prefix = '')
    {
        $this->set($name, '', '', $domain, $path, $prefix);
    }   

}

// END Cookie.php File
/* End of file Cookie.php

/* Location: .Obullo/Cookie/Cookie.php */