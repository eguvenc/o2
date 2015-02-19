<?php

namespace Obullo\Authentication\Storage;

use LogicException;
use Obullo\Container\Container;
use Obullo\Authentication\Token;
use Obullo\Authentication\AuthResult;
use Obullo\Authentication\AbstractStorage;

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
Class Redis extends AbstractStorage
{
    public $keys;               // Authentication keys which we get getAllKeys method
    protected $c;               // Container
    protected $cache;           // Cache class
    protected $session;         // Session class
    protected $auth;            // Authentication configuration array
    protected $data = array();  // User credentials data
    protected $identifier;      // Identify of user ( username, email * .. )
    protected $logger;          // Logger

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c) 
    {
        $this->c = $c;
        $this->auth  = $c['config']->load('auth');
        $this->logger  = $this->c['logger'];
        $this->session = $this->c['session'];

        $this->cache = $this->c['service provider '.$this->auth['cache']['provider']['name']]->get(
            [
                'driver' => $this->auth['cache']['provider']['driver'], 
                'serializer' => $this->auth['cache']['provider']['serializer']
            ]
        );
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
        $this->keys[$block] = $this->cache->getAllKeys($this->getKey($block).':*');
        if (isset($this->keys[$block][0])) {
            return $this->keys[$block];
        }
        return false;
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
        $this->cache->hMSet($this->getMemoryBlockKey('__temporary'), $credentials, $this->getMemoryBlockLifetime('__temporary'));
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
        $this->cache->hMSet($this->getMemoryBlockKey('__permanent'), $credentials, $this->getMemoryBlockLifetime('__permanent'));
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
        $this->cache->hSet($this->getMemoryBlockKey('__permanent'), $key, $val);
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
        $this->cache->hDel($this->getMemoryBlockKey('__permanent'), $key);
    }

    /**
     * Makes temporary credentials as permanent and authenticate the user.
     * 
     * @return mixed false|array
     */
    public function authenticateTemporaryIdentity()
    {
        if ($this->isEmpty('__temporary')) {
            $this->logger->debug('Auth identifier not matched with __temporary redis key.', array('identifier' => $this->getIdentifier(), 'key' => $this->getMemoryBlockKey('__temporary')));
            return false;
        }
        $credentials = $this->getCredentials('__temporary');
        $credentials['__isAuthenticated'] = 1;
        $credentials['__isVerified'] = 1;
        $credentials['__type'] = 'Authorized';

        if ($this->setCredentials($credentials, null, '__permanent')) {
            $this->deleteCredentials('__temporary');
            return $credentials;
        }
        $this->logger->warning('Auth temporary data could not stored as __permanent.', array('identifier' => $this->getIdentifier()));
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
        $this->data[$block] = $oldCredentials;
        if ( ! empty($pushData) AND is_array($pushData)) {
            $this->data[$block] = array_merge($oldCredentials, $pushData);
        }
        $lifetime = ($ttl == null) ? $this->getMemoryBlockLifetime($block) : (int)$ttl;
        $this->logger->debug('Auth temporary data redis key.', array('key' => $this->getMemoryBlockKey($block)));

        return $this->cache->hMSet($this->getMemoryBlockKey($block), $this->data[$block], $lifetime);
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
        if (isset($this->data[$block])) {  // Lazy loading ( returns to old records if its already exists ).
            return $this->data[$block];
        }
        return $this->data[$block] = $this->cache->hGetAll($this->getBlock($block));
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
        $constant = ($block == '__temporary') ? static::UNVERIFIED_USERS : static::AUTHORIZED_USERS; 
        $id = $this->getIdentifier();
        $identifier = empty($id) ? '__emptyIdentifier' : $id;

        return $this->c['auth.params']['cache.key']. ':' .$block. ':' .$constant.$identifier;  // Create unique key
    }

    /**
     * Returns to storage prefix key of identity data
     *
     * @param string $block memory block
     * 
     * @return string
     */
    public function getKey($block = '__temporary')
    {
        $key = ($block == '__temporary') ? static::UNVERIFIED_USERS : static::AUTHORIZED_USERS;
        return $this->c['auth.params']['cache.key']. ':' .$block. ':' .$key.$this->getUserId();
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
            return (int)$this->auth['cache']['block']['temporary']['lifetime'];
        }
        return (int)$this->auth['cache']['block']['permanent']['lifetime'];
    }

    /**
     * Match the user credentials.
     * 
     * @return object|false
     */
    public function query()
    {
        if ( ! $this->isEmpty('__permanent')) {  // If user has cached auth return to data otherwise false
            $data = (array)$this->cache->hGetAll($this->getMemoryBlockKey('__permanent'));   // We convert it to object otherwise page loading time 
                                                                                      //  over 0.1500 seconds ..
            if (count($data) == 0) {
                return false;
            }
            return $data;
        }
        return false;
    }

    /**
     * Re authenticate cached permanent identity, we override 
     * old authentication data that we stored before as permanent
     * 
     * @param array  $data  cached auth data
     * @param object $token token \Obullo\Authentication\Token
     * 
     * @return void
     */
    public function authenticatePermanentIdentity($data, Token $token)
    {
        $data['__isAuthenticated'] = 1;
        $data['__isTemporary'] = 0;
        $data['__type'] = 'Authorized';
        $data['__token'] = $token->get();  // update token

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
        $identifier = $this->getId();
        $key = $this->c['auth.params']['cache.key'].':__permanent:Authorized:';
        
        foreach ($this->cache->getAllKeys($key.$identifier.':*') as $val) {
            $exp = explode(':', $val);
            $aid = end($exp);
            $sessions[$aid]['__isAuthenticated'] = $this->cache->hGet($key.$identifier.':'.$aid, '__isAuthenticated');
            $sessions[$aid]['__time'] = $this->cache->hGet($key.$identifier.':'.$aid, '__time');
            $sessions[$aid]['id'] = $identifier;
            $sessions[$aid]['key'] = $key.$identifier.':'.$aid;
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
        $this->cache->delete($this->c['auth.params']['cache.key'].':__permanent:Authorized:'.$this->getUserId().':'.$aid);
    }

}

// END Redis.php File
/* End of file Redis.php

/* Location: .Obullo/Authentication/Storage/Redis.php */