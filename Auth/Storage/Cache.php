<?php

namespace Obullo\Auth\Storage;

use Auth\Credentials,
    Obullo\Auth\AuthResult;

 /*
 * Cache Storage Features
 *  
 * 1 - Permanent Users : To prevent sql queries for every login we keeps authorized user's identities in storage with lifetime ( default 1 hour, configurable from auth config file ).
 * 2 - Temporary Users : If verification enabled we keeps succesfull user logins as "__temporary" in storage ( cache ) with an expiration time. After that verification we move them to permanent storage.
 * 3 - Reminder : If remember me enabled, it keeps authorized user's in the storage using rememberMeSeconds as expiration.
 * 4 - Deletion : Allows to remote control rememberMe logins using storage.
 */
 
use LogicException;

/**
 * O2 Authentication - Memory Storage
 * 
 * @category  Auth
 * @package   Storage
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
Class Cache implements StorageInterface
{
    /**
     * Cache storage unverified users key
     */   
    const UNVERIFIED_USERS = 'Unverified:';

    /**
     * Cache storage authorized users key
     */   
    const AUTHORIZED_USERS = 'Authorized:';

    /**
     * Errors
     * 
     * @var array
     */
    protected $errors = array();

    /**
     * Cache class
     * 
     * @var object
     */
    protected $cache;

    /**
     * Session class
     * 
     * @var object
     */
    protected $session;

    /**
     * Auth configuration array
     * 
     * @var array
     */
    protected $config;

    /**
     * User credentials data
     * 
     * @var array
     */
    protected $data = array();

    /**
     * Identify of user ( username, email * .. )
     * 
     * @var string
     */
    protected $identifier;

    /**
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c) 
    {
        $this->c = $c;
        $this->cache = $c->load('service/cache');
        $this->logger = $c->load('service/logger');
        $this->session = $c->load('session');
        $this->config = $c->load('config')->load('auth');
        $this->identifier = $this->getIdentifier();
    }
    
    /**
     * Sets identifier value to session
     *
     * @param string $identifier user id
     * 
     * @return void
     */
    public function setIdentifier($identifier)
    {
        $this->session->set('__Auth/Storage/RecallerId', $identifier);
    }

    /**
     * Returns to user identifier
     * 
     * @return mixed string|id
     */
    public function getIdentifier()
    {
        $sessionRecallerId = $this->c->load('session')->get('__Auth/Storage/RecallerId');

        return ($sessionRecallerId == false) ? $this->getRecallerId() : $sessionRecallerId;
    }

    /**
     * Get recaller id from storage
     * 
     * @return mixed int|string|null
     */
    public function getRecallerId()
    {
        if ($credentials = $this->getCredentials('__permanent')) {
            return $credentials[Credentials::IDENTIFIER];
        }
        return null;
    }

    /**
     * Returns true if temporary credentials does "not" exists
     *
     * @param string $block __temporary or __permanent
     * 
     * @return bool
     */
    public function isEmpty($block = '__permanent')
    {
        return $this->cache->keyExists($this->getMemoryBlockKey($block));
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
        $this->cache->set($this->getMemoryBlockKey('__temporary'), $credentials, $this->getMemoryBlockLifetime('__temporary'));
    }

    /**
     * Register credentials to temporary storage
     * 
     * @param array $credentials user identities
     * 
     * @return void
     */
    public function loginAsPermanent(array $credentials)
    {
        $this->cache->set($this->getMemoryBlockKey('__permanent'), $credentials, $this->getMemoryBlockLifetime('__permanent'));
    }

    /**
     * Makes temporary credential attributes as permanent and authenticate the user.
     * 
     * @return mixed false|array
     */
    public function authenticateTemporaryIdentity()
    {
        if ($this->isEmpty('__temporary')) {
            $this->logger->error('Auth user identifier not found in __temporary memory block.', array('identifier' => $this->getIdentifier()));
            return false;
        }
        $credentials = $this->getCredentials('__temporary');
        $credentials['__isAuthenticated'] = 1;
        $credentials['__isVerified'] = 1;

        if ($this->setCredentials($credentials, '__permanent')) {
            $this->deleteCredentials('__temporary');
            return $credentials;
        }
        $this->logger->error('Auth temporary data could not authenticated as __permanent.', array('identifier' => $this->getIdentifier()));
        return false;
    }
    
    /**
     * Update credentials
     * 
     * @param array  $oldCredentials user identity old data
     * @param mixed  $pushData       push to identity data
     * @param string $block          storage persistence type permanent / temporary
     * 
     * @return boolean
     */
    public function setCredentials(array $oldCredentials, $pushData = null, $block = '__temporary')
    {
        $this->data[$block] = $oldCredentials;
        if ( ! empty($pushData)) {
            $this->data[$block] = array_merge($oldCredentials, $pushData);
        }
        return $this->cache->set($this->getMemoryBlockKey($block), $this->data[$block], $this->getMemoryBlockLifetime($block));
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
        if (isset($this->data[$block])) {  // Lazy loading ( returns to old records if its already exists ).
            return $this->data[$block];
        }
        return $this->data[$block] = $this->cache->get($this->getMemoryBlockKey($block));
    }

    /**
     * Deletes memory block
     *
     * @param string $block name
     * 
     * @return void
     */
    public function deleteCredentials($block = '__temporary')
    {
        return $this->cache->delete($this->getMemoryBlockKey($block));
    }

    /**
     * Creates storage key for temporary guest data
     *
     * @param string $block name
     * 
     * @return string
     */
    public function getMemoryBlockKey($block = '__temporary')
    {
        $constant = ($block == '__temporary') ? static::UNVERIFIED_USERS : static::AUTHORIZED_USERS; 

        return 'Auth:'.$block.':'.$constant.$this->identifier;  // Create unique key
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
            return (int)$this->config['memory']['block']['temporary']['lifetime'];
        }
        return (int)$this->config['memory']['block']['permanent']['lifetime'];
    }

    /**
     * Match the user credentials.
     * 
     * @return array|false
     */
    public function query()
    {
        return $this->getCredentials('__permanent');
    }

}

// END Cache.php File
/* End of file Cache.php

/* Location: .Obullo/Auth/Storage/Cache.php */