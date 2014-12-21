<?php

namespace Obullo\Session\Handler;

/**
 * Cache Session Handler Class 
 * 
 * @implements HandlerInterface but we don't declare implementation
 * to consume less memory.
 * 
 * @category  Session
 * @package   Cache
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
Class Cache
{
    /**
     * Container
     * 
     * @var object
     */
    public $c; 

    /**
     * Redis key name
     * 
     * @var string
     */
    public $key = 'Sessions:';

    /**
     * Expiration time of current session
     * 
     * @var integer
     */
    public $lifetime = 7200; // two hours
 
    /**
     * Constructor
     *
     * @param array $c      container
     * @param array $params configuration
     */
    public function __construct($c, $params = array())
    {
        $this->c = $c;
        $this->params = $params;
        $this->key = $params['session']['key'];
        $this->lifetime = $params['session']['lifetime'];
    }

    /**
    * Php5 session handler interface open function
    * 
    * @param string $savePath    save path 
    * @param string $sessionName session name
    * 
    * @return bool
    */
    public function open($savePath, $sessionName)
    {
        $savePath = null;
        $sessionName = null;
        $this->cache = $this->c->load('return service/provider/cache', array('serializer' => 'SERIALIZER_NONE'));
        return is_object($this->cache) ? true : false;
    }
 
    /**
     * Close the connection. Called by PHP when the script ends.
     * 
     * @return void
     */
    public function close()
    {
        return;
    }
 
    /**
     * Read data from the session.
     * 
     * @param string $id session id
     * 
     * @return mixed
     */
    public function read($id)
    {   
        $key = $this->key . $id;
        $result = $this->cache->get($key);
        return $result ?: null;
    }
 
    /**
     * Write data to the session.
     * 
     * @param string $id   current session id
     * @param mixed  $data mixed data
     * 
     * @return bool
     */
    public function write($id, $data)
    {
        if (empty($data)) { // If we have no session data don't write it.
            return false;
        }
        $key = $this->key . $id;
        $result = $this->cache->set($key, $data, $this->getLifetime());
        return $result ? true : false;
    }
 
    /**
     * Delete data from the session.
     * 
     * @param string $id current session id
     * 
     * @return bool
     */
    public function destroy($id)
    {
        $key = $this->key . $id;
        $result = $this->cache->delete($key);
        return $result ? true : false;
    }

    /**
     * Run garbage collection
     * 
     * @param integer $maxLifetime expration time
     * 
     * @return bool
     */
    public function gc($maxLifetime)
    {
        $maxLifetime = null;
        return true;
    }

    /**
     * Set expiration of valid session
     * 
     * @param int $ttl lifetime
     * 
     * @return void
     */
    public function setLifetime($ttl)
    {
        $this->lifetime = (int)$ttl;
    }

    /**
     * Get expiration of valid session
     * 
     * @return int
     */ 
    public function getLifetime()
    {
        return $this->lifetime;
    }
}

// END Cache.php File
/* End of file Cache.php

/* Location: .Obullo/Session/Handler/Cache.php */