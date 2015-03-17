<?php

namespace Obullo\Authentication\Storage;

use Obullo\Container\Container;
use Obullo\Authentication\AuthResult;
use Obullo\Authentication\AbstractStorage;
use Obullo\ServiceProviders\ServiceProviderInterface;

/**
 * O2 Authentication - Cache Storage
 * 
 * @category  Authentication
 * @package   Storage
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class Cache extends AbstractStorage
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
        $data = $this->getCredentials($block);
        $data[$key] = $val;
        $this->setCredentials($data, null, $block);
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
        $data = $this->getCredentials($block);
        unset($data[$key]);
        $this->setCredentials($data, null, $block);
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
        $this->data[$block] = array($this->getLoginId() => $credentials);
        if ( ! empty($pushData) AND is_array($pushData)) {
            $this->data[$block] = array($this->getLoginId() => array_merge($credentials, $pushData));
        }
        $allData = $this->cache->get($this->getMemoryBlockKey($block), false);  // Get all data
        $lifetime = ($ttl == null) ? $this->getMemoryBlockLifetime($block) : (int)$ttl;

        if ($allData == false) {
            $allData = array();
        }
        return $this->cache->set($this->getMemoryBlockKey($block), array_merge($allData, $this->data[$block]), $lifetime);
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
        $data = $this->cache->get($this->getBlock($block));
        if (isset($data[$this->getLoginId()])) {
            return $data[$this->getLoginId()];
        }
        return false;
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
        $lid  = $this->getLoginId();
        $credentials = $this->cache->get($this->getBlock($block));  // Don't do container cache

        if ( ! isset($credentials[$lid])) {  // already removed
            return;
        }
        unset($credentials[$lid]);
        $this->cache->set($this->getMemoryBlockKey($block), $credentials, $this->getMemoryBlockLifetime($block));

        $credentials = $this->cache->get($this->getBlock($block)); // Destroy auth block if its empty
        if (empty($credentials)) {
            $this->cache->delete($this->getBlock($block));
        }
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
        return $this->cache->get($this->getBlock($block));
    }

    /**
     * Returns to storage full key of identity data
     *
     * @param string $block name
     * 
     * @return string
     */
    public function getMemoryBlockKey($block = '__temporary')
    {
        return $this->cacheKey. ':' .$block. ':' .$this->getUserId();  // Create unique key
    }

    /**
     * Get multiple authenticated sessions
     * 
     * @return array
     */
    public function getAllSessions()
    {
        $sessions   = array();
        $dbSessions = $this->cache->get($this->getMemoryBlockKey('__permanent'));
        
        if ($dbSessions == false) {
            return $sessions;
        }
        foreach ($dbSessions as $lid => $val) {
            if (isset($val['__isAuthenticated']) AND $val['__isAuthenticated'] == 1) {
                $sessions[$lid]['__isAuthenticated'] = $val['__isAuthenticated'];
                $sessions[$lid]['__time'] = $val['__time'];
                $sessions[$lid]['id']  = $this->getUserId();
                $sessions[$lid]['key'] = $this->getMemoryBlockKey('__permanent');   
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
        $data = $this->cache->get($this->getMemoryBlockKey('__permanent'));
        unset($data[$loginId]);
        $this->cache->set($this->getMemoryBlockKey('__permanent'), $data, $this->getMemoryBlockLifetime('__permanent'));
    }

}

// END Cache.php File
/* End of file Cache.php

/* Location: .Obullo/Authentication/Storage/Cache.php */