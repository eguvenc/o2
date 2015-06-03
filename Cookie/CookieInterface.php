<?php

namespace Obullo\Cookie;

/**
 * Control cookie set, get, delete and queue operations
 * 
 * @category  Cookie
 * @package   CookieInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cookie
 */
interface CookieInterface
{
    /**
     * Set cookie name
     * 
     * @param string $name cookie name
     * 
     * @return object
     */
    public function name($name);
    
    /**
     * Set cookie value
     * 
     * @param string $value value
     * 
     * @return object
     */
    public function value($value = '');

    /**
     * Set cookie expire in seconds
     * 
     * @param integer $expire seconds
     * 
     * @return object
     */
    public function expire($expire = 0);

    /**
     * Set cookie domain name
     * 
     * @param string $domain name
     * 
     * @return void
     */
    public function domain($domain = '');

    /**
     * Set cookie path
     * 
     * @param string $path name
     * 
     * @return object
     */
    public function path($path = '/');

    /**
     * Set secure cookie
     * 
     * @param boolean $bool true or false
     * 
     * @return object
     */
    public function secure($bool = false);

    /**
     * Make cookie available just for http. ( No javascript )
     * 
     * @param boolean $bool true or false
     * 
     * @return object
     */
    public function httpOnly($bool = false);

    /**
     * Set a cookie prefix
     * 
     * @param string $prefix prefix
     * 
     * @return object
     */
    public function prefix($prefix = '');

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
    public function set($name = null, $value = null, $queue = false);

    /**
     * Write cookie to headers
     * 
     * @param array $cookie params
     * 
     * @return void
     */
    public function write(array $cookie);

    /**
     * Send cookies to queue with cookie name
     * 
     * @param string $name  cookie name
     * @param string $value cookie value
     * 
     * @return void
     */
    public function queue($name = null, $value = null);

    /**
     * Get a queued cookie array
     *
     * @param string $name   cookie name
     * @param string $prefix prefix
     * 
     * @return string
     */
    public function queued($name, $prefix = '');

    /**
     * Remove a cookie from the queue.
     *
     * @param string $name   cookie name
     * @param string $prefix prefix
     * 
     * @return void
     */
    public function unqueue($name, $prefix = '');

    /**
     * Get cookie
     * 
     * @param string $key    cookie key
     * @param string $prefix cookie prefix
     * 
     * @return string sanizited cookie
     */
    public function get($key, $prefix = '');

    /**
    * Delete a cookie
    *
    * @param string $name   cookie
    * @param string $prefix custom prefix
    * 
    * @return   void
    */
    public function delete($name = null, $prefix = null);

    /**
     * Get the cookies which have been queued for the next request
     *
     * @return array
     */
    public function getQueuedCookies();

}

// END CookieInterface.php File
/* End of file CookieInterface.php

/* Location: .Obullo/Cookie/CookieInterface.php */