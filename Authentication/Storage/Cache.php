<?php

namespace Obullo\Authentication\Storage;

use Obullo\Container\Container;
use Obullo\Authentication\AuthResult;
use Obullo\Authentication\AbstractStorage;

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
    protected $session;         // Session class
    protected $identifier;      // Identify of user ( username, email * .. )
    protected $logger;          // Logger
    protected $killSignal = array();  // Unique session login ids

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c) 
    {
        $this->c = $c;
        $this->c['config']->load('auth');
        $this->cache = $c['service provider cache']->get(
            [
                'driver' => $this->c['config']['auth']['cache']['provider']['driver'],
                'connection' => $this->c['config']['auth']['cache']['provider']['connection']
            ]
        );
        $this->logger  = $this->c['logger'];
        $this->session = $this->c['session'];

        register_shutdown_function(array($this, 'close'));
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
     * Remove identity item
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
            $this->logger->debug('Auth identifier not matched with __temporary cache key.', array('identifier' => $this->getIdentifier(), 'key' => $this->getMemoryBlockKey('__temporary')));
            return false;
        }
        $credentials = $this->getCredentials('__temporary');
        if ($credentials == false) {  // If already permanent
            return;
        }
        $credentials['__isAuthenticated'] = 1;
        $credentials['__isTemporary'] = 0;
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
        return $this->c['auth.params']['cache.key']. ':' .$block. ':' .$this->getUserId();  // Create unique key
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
     * @return array
     */
    public function getAllSessions()
    {
        $sessions   = array();
        $dbSessions = $this->cache->get($this->getMemoryBlockKey('__permanent'), false);
        
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
     * Kill authority of user using auth id
     * 
     * @param string $lid login id (10 chars)  e.g:  ahtrzflp79
     * 
     * @return boolean
     */
    public function killSession($lid)
    {
        $this->killSignal[$lid] = $lid;
    }

    /**
     * Do finish operations
     * 
     * @return void
     */
    public function close()
    {
        if (empty($this->killSignal)) {
            return;
        }
        $data = $this->cache->get($this->getMemoryBlockKey('__permanent'));
        foreach ($this->killSignal as $lid) {
            unset($data[$lid]);
        }
        $this->cache->set($this->getMemoryBlockKey('__permanent'), $data, $this->getMemoryBlockLifetime('__permanent'));
    }

}

// END Cache.php File
/* End of file Cache.php

/* Location: .Obullo/Authentication/Storage/Cache.php */