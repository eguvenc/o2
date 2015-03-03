<?php

namespace Obullo\Session\SaveHandler;

use Obullo\Container\Container;

/**
 * Save Handler Class 
 * 
 * @category  Session
 * @package   SaveHandler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
Class Cache implements SaveHandlerInterface
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
     * Cache provider
     * 
     * @var object
     */
    protected $provider;

    /**
     * Constructor
     *
     * @param array $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $c['config']->load('session');
        $this->key = $this->config['session']['key'];
        $this->lifetime = $this->config['session']['lifetime'];
        $this->provider = $this->c['service provider cache'];
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
        $this->cache = $this->provider->get(
            [
                'driver' => $this->config['provider']['driver'],
                'serializer' => 'none'
            ]
        );
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

/* Location: .Obullo/Session/SaveHandler/Cache.php */