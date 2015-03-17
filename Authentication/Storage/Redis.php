<?php

namespace Obullo\Authentication\Storage;

use Obullo\Container\Container;
use Obullo\Authentication\AuthResult;
use Obullo\Authentication\AbstractStorage;
use Obullo\ServiceProviders\ServiceProviderInterface;

/**
 * O2 Authentication - Memory Storage
 * 
 * @category  Authentication
 * @package   Storage
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class Redis extends AbstractStorage implements StorageInterface
{
    protected $c;               // Container
    protected $cache;           // Cache class
    protected $cacheKey;        // Cache key
    protected $config;          // Auth config array
    protected $session;         // Session class
    protected $identifier;      // Identify of user ( username, email * .. )
    protected $logger;          // Logger

    /**
     * Constructor
     * 
     * @param object $c        container
     * @param object $provider ServiceProviderInterface
     */
    public function __construct(Container $c, ServiceProviderInterface $provider) 
    {
        $this->c = $c;
        $this->config = $this->c['auth.config'];
        $this->cache = $provider->get(
            [
                'driver' => $this->config['cache']['provider']['driver'],
                'connection' => $this->config['cache']['provider']['connection']
            ]
        );
        $this->cacheKey = (string)$this->config['cache.key'];
        $this->logger  = $this->c['logger'];
        $this->session = $this->c['session'];
    }

    /**
     * Returns true if temporary credentials does "not" exists
     *
     * @param string $block __temporary or __permanent | full key
     * 
     * @return bool
     */
    public function isEmpty($block = '__permanent')
    {
        $exists = $this->cache->keyExists($this->getBlock($block));
        return ($exists) ? false : true;
    }

    /**
     * Match the user credentials.
     * 
     * @return object|false
     */
    public function query()
    {
        if ( ! $this->isEmpty('__permanent')) {  // If user has cached auth return to data otherwise false

            $data = $this->getCredentials($this->getMemoryBlockKey('__permanent'));

            if (count($data) == 0) {
                return false;
            }
            return $data;
        }
        return false;
    }

    /**
     * Update credentials
     * 
     * @param array  $credentials user identity old data
     * @param mixed  $pushData    push to identity data
     * @param string $block       storage persistence type permanent / temporary
     * @param string $ttl         storage lifetime
     * 
     * @return boolean
     */
    public function setCredentials(array $credentials, $pushData = null, $block = '__temporary', $ttl = null)
    {
        if ($this->getIdentifier() == '__empty') {
            return false;
        }
        $data = $credentials;
        if ( ! empty($pushData) AND is_array($pushData)) {
            $data = array_merge($credentials, $pushData);
        }
        $lifetime = ($ttl == null) ? $this->getMemoryBlockLifetime($block) : (int)$ttl;

        return $this->cache->hMSet($this->getMemoryBlockKey($block), $data, $lifetime);
    }

    /**
     * Get Temporary Credentials Data
     *
     * @param string $block name
     * 
     * @return void
     */
    public function getCredentials($block = '__permanent')
    {
        if ($this->getIdentifier() == '__empty') {
            return false;
        }
        return $this->cache->hGetAll($this->getBlock($block));
    }

    /**
     * Deletes memory block
     *
     * @param string $block name or key
     * 
     * @return void
     */
    public function deleteCredentials($block = '__temporary')
    {
        return $this->cache->delete($this->getBlock($block));
    }

    /**
     * Update identity item value
     * 
     * @param string $key   string
     * @param value  $val   value
     * @param string $block block key
     *
     * @return boolean|integer
     */
    public function update($key, $val, $block = '__permanent')
    {
        return $this->cache->hSet($this->getMemoryBlockKey($block), $key, $val);
    }

    /**
     * Unset identity item
     * 
     * @param string $key   string
     * @param string $block block key
     * 
     * @return boolean|integer
     */
    public function remove($key, $block = '__permanent')
    {
        return $this->cache->hDel($this->getMemoryBlockKey($block), $key);
    }

    /**
     * Check whether to identify exists
     *
     * @param string $block __temporary or __permanent
     * 
     * @return array keys if succes otherwise false
     */
    public function getAllKeys($block = '__permanent')
    {
        $data = $this->cache->getAllKeys($this->getKey($block).':*');
        if (isset($data[0])) {
            return $data;
        }
        return false;
    }

    /**
     * Get multiple authenticated sessions
     * 
     * @return array
     */
    public function getAllSessions()
    {
        $sessions   = array();
        $identifier = $this->getUserId();
        $key        = $this->cacheKey.':__permanent:';
        $dbSessions = $this->cache->getAllKeys($key.$identifier.':*');
        
        if ($dbSessions == false) {
            return $sessions;
        }
        foreach ($dbSessions as $val) {
            $exp = explode(':', $val);
            $lid = end($exp);

            $isAuthenticated = $this->cache->hGet($key.$identifier.':'.$lid, '__isAuthenticated');
            if ($isAuthenticated == 1) {
                $sessions[$lid]['__isAuthenticated'] = $isAuthenticated;
                $sessions[$lid]['__time'] = $this->cache->hGet($key.$identifier.':'.$lid, '__time');
                $sessions[$lid]['id'] = $identifier;
                $sessions[$lid]['key'] = $key.$identifier.':'.$lid;
            }
        }
        return $sessions;
    }

    /**
     * Kill session using by login id
     * 
     * @param integer $loginId login id max e.g. 87060e89 ( user@example.com:87060e89 )
     * 
     * @return void
     */
    public function killSession($loginId)
    {
        $this->deleteCredentials($this->cacheKey.':__permanent:'.$this->getUserId().':'.$loginId);
    }

}

// END Redis.php File
/* End of file Redis.php

/* Location: .Obullo/Authentication/Storage/Redis.php */