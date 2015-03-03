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
    public $keys;               // Authentication keys used in getAllKeys method
    protected $c;               // Container
    protected $cache;           // Cache class
    protected $session;         // Session class
    protected $data = array();  // User credentials data
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
        $this->cache = $provider->get(
            [
                'driver' => $this->c['config']['auth']['cache']['provider']['driver'], 
                'options' => $this->c['config']['auth']['cache']['provider']['options']
            ]
        );
        $this->c['config']->load('auth');
        $this->logger  = $this->c['logger'];
        $this->session = $this->c['session'];
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
        if (isset($this->keys[$block])) {
            return $this->keys[$block];
        }
        $key = $this->c['auth.params']['cache.key'].':'.$block.':Authorized:'.$this->getUserId();
        return $this->keys[$block] = $this->cache->get($key);
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
     * Get credentials and check authority
     * 
     * @return mixed bool
     */
    public function isAuthenticated()
    {
        $rowArray = $this->getCredentials('__permanent');
        
        if (is_array($rowArray) AND isset($rowArray['__isAuthenticated']) AND $rowArray['__isAuthenticated'] == 1) {
            return $rowArray;
        }
        return false;
    }

    /**
     * Register credentials to temporary storage
     * 
     * @param array $credentials user identities
     * 
     * @return void
     */
    public function loginAsTemporary(array $credentials)
    {
        $this->setCredentials($credentials, null, '__temporary', $this->getMemoryBlockLifetime('__temporary'));
    }

    /**
     * Register credentials to permanent storage
     * 
     * @param array $credentials user identities
     * 
     * @return void
     */
    public function loginAsPermanent(array $credentials)
    {
        $this->setCredentials($credentials, null, '__permanent', $this->getMemoryBlockLifetime('__permanent'));
    }
    
    /**
     * Update identity value
     * 
     * @param string $key string
     * @param value  $val value
     *
     * @return void
     */
    public function update($key, $val)
    {
        $data = $this->getCredentials('__permanent');
        $data[$key] = $val;
        $this->setCredentials($data, null, '__permanent');
    }

    /**
     * Remove identity 
     * 
     * @param string $key string
     * 
     * @return void
     */
    public function remove($key)
    {
        $data = $this->getCredentials('__permanent');
        unset($data[$key]);
        $this->setCredentials($data, null, '__permanent');
    }

    /**
     * Makes temporary credentials as permanent and authenticate the user.
     * 
     * @return mixed false|array
     */
    public function authenticateTemporaryIdentity()
    {
        if ($this->isEmpty('__temporary')) {
            $this->logger->debug(
                'Auth identifier not matched with __temporary cache key.', 
                ['identifier' => $this->getIdentifier(), 'key' => $this->getMemoryBlockKey('__temporary')]
            );
            return false;
        }
        $aid = $this->getLoginId();
        $credentials = $this->getCredentials('__temporary');

        $credentials[$aid]['__isAuthenticated'] = 1;
        $credentials[$aid]['__isVerified'] = 1;
        $credentials[$aid]['__type'] = 'Authorized';

        if ($this->setCredentials($credentials, null, '__permanent')) {
            $this->deleteCredentials('__temporary');
            return $credentials;
        }
        $this->logger->debug('Auth temporary data could not authenticated as __permanent.', array('identifier' => $this->getIdentifier()));
        return false;
    }

    /**
     * Update credentials
     * 
     * @param array  $oldCredentials user identity old data
     * @param mixed  $pushData       push to identity data
     * @param string $block          storage persistence type permanent / temporary
     * @param string $ttl            storage lifetime
     * 
     * @return boolean
     */
    public function setCredentials(array $oldCredentials, $pushData = null, $block = '__temporary', $ttl = null)
    {
        $identifier = $this->getIdentifier();
        if (empty($identifier)) {
            return false;
        }
        $this->data[$block] = array($this->getLoginId() => $oldCredentials);
        if ( ! empty($pushData) AND is_array($pushData)) {
            $this->data[$block] = array($this->getLoginId() => array_merge($oldCredentials, $pushData));
        }
        $lifetime = ($ttl == null) ? $this->getMemoryBlockLifetime($block) : (int)$ttl;
        
        return $this->cache->set($this->getMemoryBlockKey($block), $this->data[$block], $lifetime);
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
        $identifier = $this->getIdentifier();
        if (empty($identifier)) {
            return false;
        }
        if (isset($this->data[$block][$this->getLoginId()])) {  // Lazy loading ( returns to old records if its already exists ).
            return $this->data[$block][$this->getLoginId()];
        }
        $data = $this->cache->get($this->getBlock($block));

        if (isset($data[$this->getLoginId()])) {
            return $this->data[$block] = $data[$this->getLoginId()];
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
        $aid  = $this->getLoginId();
        $data = $this->cache->get($this->getBlock($block));

        if ( ! isset($data[$aid])) {  // already removed
            return;
        }
        unset($data[$aid]);
        $this->setCredentials($data, null, '__permanent');
    }

    /**
     * Get valid memory segment
     * 
     * @param string $block name
     * 
     * @return string
     */
    protected function getBlock($block)
    {
        return ($block == '__temporary' || $block == '__permanent') ? $this->getMemoryBlockKey($block) : $block;
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
        $key = ($block == '__temporary') ? static::UNVERIFIED_USERS : static::AUTHORIZED_USERS;

        return $this->c['auth.params']['cache.key']. ':' .$block. ':' .$key.$this->getUserId();  // Create unique key
    }


    /**
     * Returns to memory block lifetime
     * 
     * @param array $block __temporary or __permanent
     * 
     * @return integer
     */
    protected function getMemoryBlockLifetime($block = '__temporary')
    {
        if ($block == '__temporary') {
            return (int)$this->c['config']['auth']['cache']['block']['temporary']['lifetime'];
        }
        return (int)$this->c['config']['auth']['cache']['block']['permanent']['lifetime'];
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
     * Re authenticate cached permanent identity
     * 
     * @param array $data cached auth data
     * 
     * @return void
     */
    public function authenticatePermanentIdentity($data)
    {
        /**
         * We override old authentication data
         * that we stored before as permanent
         */
        $data['__isAuthenticated'] = 1;
        $data['__isTemporary'] = 0;
        $data['__type'] = 'Authorized';
        $data['__token'] = $this->c['auth.token']->get();

        $this->loginAsPermanent($data);
    }

    /**
     * Get multiple authenticated sessions
     * 
     * @return array|false
     */
    public function getAllSessions()
    {
        $sessions = array();
        $key = $this->c['auth.params']['cache.key'].':__permanent:Authorized:'.$this->getUserId();
        
        foreach ($this->cache->get($key) as $aid => $val) {
            if (isset($val['__isAuthenticated']) AND $val['__isAuthenticated'] == 0) {
                break;
            }
            $sessions[$aid]['__isAuthenticated'] = $val['__isAuthenticated'];
            $sessions[$aid]['__time'] = $val['__time'];
            $sessions[$aid]['id'] = $this->getUserId();
            $sessions[$aid]['key'] = $key;
        }
        return $sessions;
        
    }

    /**
     * Kill authority of user using auth id
     * 
     * @param string $aid auth id (10 chars)  e.g:  ahtrzflp79
     * 
     * @return boolean
     */
    public function killSession($aid)
    {
        $key = $this->c['auth.params']['cache.key'].':__permanent:Authorized:'.$this->getUserId();
        $data = $this->cache->get($key);
        unset($data[$aid]);
        $this->cache->set($key, $data, $this->getMemoryBlockLifetime('__permanent'));
    }

}

// END Cache.php File
/* End of file Cache.php

/* Location: .Obullo/Authentication/Storage/Cache.php */