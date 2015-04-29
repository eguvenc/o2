<?php

namespace Obullo\Cookie;

use RuntimeException;
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
class Cookie
{
    /**
     * Cookie unique id
     * 
     * @var string
     */
    protected $id;

    /**
     * Queued cookies
     * 
     * @var array
     */
    protected $queued = array();

    /**
     * Standart cookies
     * 
     * @var array
     */
    protected $cookies = array();

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->c['logger']->debug('Cookie Class Initialized');
    }

    /**
     * Create unique cookie id
     * 
     * @return void
     */
    protected function createId()
    {
        if ($this->id == null) {
            $this->id = uniqid();  // Create random id for new cookie
        }
    }

    /**
     * Set cookie name
     * 
     * @param string $name cookie name
     * 
     * @return object
     */
    public function name($name)
    {
        $this->createId();
        $this->cookies[$this->id]['name'] = $name;
        return $this;
    }
    
    /**
     * Set cookie value
     * 
     * @param string $value value
     * 
     * @return object
     */
    public function value($value = '')
    {
        $this->createId();
        $this->cookies[$this->id]['value'] = $value;
        return $this;
    }

    /**
     * Set cookie expire in seconds
     * 
     * @param integer $expire seconds
     * 
     * @return object
     */
    public function expire($expire = 0)
    {
        $this->createId();
        $this->cookies[$this->id]['expire'] = (int)$expire;
        return $this;
    }

    /**
     * Set cookie domain name
     * 
     * @param string $domain name
     * 
     * @return void
     */
    public function domain($domain = '')
    {
        $this->createId();
        $this->cookies[$this->id]['domain'] = $domain;
        return $this;
    }

    /**
     * Set cookie path
     * 
     * @param string $path name
     * 
     * @return object
     */
    public function path($path = '/')
    {
        $this->createId();
        $this->cookies[$this->id]['path'] = $path;
        return $this;
    }

    /**
     * Set secure cookie
     * 
     * @param boolean $bool true or false
     * 
     * @return object
     */
    public function secure($bool = false)
    {
        $this->createId();
        $this->cookies[$this->id]['secure'] = $bool;
        return $this;
    }

    /**
     * Make cookie available just for http. ( No javascript )
     * 
     * @param boolean $bool true or false
     * 
     * @return object
     */
    public function httpOnly($bool = false)
    {
        $this->createId();
        $this->cookies[$this->id]['httpOnly'] = $bool;
        return $this;
    }

    /**
     * Set a cookie prefix
     * 
     * @param string $prefix prefix
     * 
     * @return object
     */
    public function prefix($prefix = '')
    {
        $this->createId();
        $this->cookies[$this->id]['prefix'] = $prefix;
        return $this;
    }

    /**
     * Set cookie
     *
     * Accepts six parameter, or you can submit an associative
     * array in the first parameter containing all the values.
     * 
     * @param string  $name  cookie name
     * @param string  $value cookie value
     * @param boolean $queue send to queue
     *
     * @return array
     */
    public function set($name = null, $value = null, $queue = false)
    {
        if (is_string($name) && $name != null) {    // Build method chain parameters

            if ( ! isset($this->cookies[$this->id]['name'])) {
                $this->name($name);   // Set cookie name
            }
            if ( ! isset($this->cookies[$this->id]['value'])) {
                $this->value($value); // Set cookie value
            }
            $name = $this->cookies[$this->id];
        }
        if ($name == null && $value == null) {  // If user want to use this way $this->cookie->name()->value()->set();
            $name = $this->cookies[$this->id];
        }
        $cookie = $this->buildParameters($name);
        if ($queue == false) {
            $this->write($cookie);
        }
        unset($this->cookies[$this->id]);  // Remove latest cookie from cookie array
        return $cookie;
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
     * Send cookies to queue with cookie name
     * 
     * @param string $name  cookie name
     * @param string $value cookie value
     * 
     * @return void
     */
    public function queue($name = null, $value = null)
    {
        $cookie = $this->set($name, $value, true);
        $this->queued[$cookie['prefix'].$cookie['name']] = $cookie;
    }

    /**
     * Get a queued cookie array
     *
     * @param string $name   cookie name
     * @param string $prefix prefix
     * 
     * @return string
     */
    public function queued($name, $prefix = '')
    {
        $prefix = empty($prefix) ? $this->c['config']['cookie']['prefix'] : $prefix;
        return isset($this->queued[$prefix.$name]) ? $this->queued[$prefix.$name]['value'] : false;
    }

    /**
     * Remove a cookie from the queue.
     *
     * @param string $name   cookie name
     * @param string $prefix prefix
     * 
     * @return void
     */
    public function unqueue($name, $prefix = '')
    {
        $prefix = empty($prefix) ? $this->c['config']['cookie']['prefix'] : $prefix;
        unset($this->queued[$prefix.$name]);
    }

    /**
     * Get cookie
     * 
     * @param string $key    cookie key
     * @param string $prefix cookie prefix
     * 
     * @return string sanizited cookie
     */
    public function get($key, $prefix = '')
    {
        if ( ! isset($_COOKIE[$key]) && $prefix == '' && $this->c['config']['cookie']['prefix'] != '') {
            $prefix = $this->c['config']['cookie']['prefix'];
        }
        if ( ! isset($_COOKIE[$prefix.$key])) {
            return false;
        }
        return $_COOKIE[$prefix.$key];
    }

    /**
     * Returns id of current cookie
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
    * Delete a cookie
    *
    * @param string $name   cookie
    * @param string $prefix custom prefix
    * 
    * @return   void
    */
    public function delete($name = null, $prefix = null)
    {
        if ($name != null) {
            $this->name($name);
        }
        if ($prefix != null) {
            $this->prefix($prefix);
        }
        $this->value(null)->expire(-1)->prefix($prefix)->set();
    }

    /**
     * Build cookie parameters
     * 
     * @param array $params cookie params
     * 
     * @return array
     */
    protected function buildParameters($params)
    {
        if ( ! is_array($params) || ! isset($params['name'])) {
            throw new RuntimeException("Cookie name can't be empty.");
        }
        $cookie = array();
        foreach (array('name','value','expire','domain','path','secure','httpOnly','prefix') as $k) {
            if (array_key_exists($k, $params)) {
                $cookie[$k] = $params[$k];
            } else {
                $cookie[$k] = $this->c['config']['cookie'][$k];
            }
        }
        $cookie['expire'] = $this->getExpiration($cookie['expire']);
        return $cookie;
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