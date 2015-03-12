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
    public $key = 'sessions:';

    /**
     * Expiration time of current session
     * 
     * @var integer
     */
    public $lifetime = 7200; // two hours
 
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
        $this->cache = $this->c['service provider cache']->get(
            [
                'driver' => $this->config['cache']['provider']['driver'],
                'connection' => $this->config['cache']['provider']['connection']
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
        $result = $this->cache->get($this->key.$id);
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
        $result = $this->cache->set($this->key.$id, $data, $this->getLifetime());
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
        $result = $this->cache->delete($this->key.$id);
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