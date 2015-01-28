<?php

namespace Obullo\Authentication\Storage;

use Auth\Constant,
    Obullo\Utils\Random,
    Obullo\Authentication\Token,
    Obullo\Authentication\AuthResult,
    LogicException;

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
Class Redis
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
     * Authentication configuration array
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
     * Authentication keys which we get with getAllKeys() method
     * 
     * @var array
     */
    public $keys;

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
        $this->config = $c['config']->load('auth');
        
        $this->cache   = $c->load('cache');
        $this->logger  = $c->load('logger');
        $this->session = $c->load('session');
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
        $this->session->set('__'.$this->config['cache']['key'].'/Identifier', $identifier.':'.$this->getRandomId());
    }

    /**
     * Returns to user identifier
     * 
     * @return mixed string|id
     */
    public function getIdentifier()
    {
        return $this->session->get('__'.$this->config['cache']['key'].'/Identifier');
    }

    /**
     * Get id of identifier without random Id value
     * 
     * @return string
     */
    public function getId()
    {
        $identifier = $this->getIdentifier();
        if (empty($identifier)) {
            return '__emptyIdentifier';
        }
        $exp = explode(':', $identifier);
        return $exp[0];
    }

    /**
     * Get random id
     * 
     * @return string
     */
    public function getRandomId()
    {
        $id = $this->session->get('__'.$this->config['cache']['key'].'/RandomId');
        if ($id == false) {
            $id = $this->setRandomId();
            return $id;
        }
        return $id;
    }

    /**
     * Set random auth session id to sessions
     *
     * @param string $id id
     * 
     * @return string
     */
    public function setRandomId($id = null)
    {
        if (empty($id)) {
            $id = Random::generate('alnum.lower', 10);
        }
        $this->session->set('__'.$this->config['cache']['key'].'/RandomId', $id);
        return $id;
    }

    /**
     * Unset identifier from session
     * 
     * @return void
     */
    public function unsetIdentifier()
    {   
        $this->session->remove('__'.$this->config['cache']['key'].'/Identifier');
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

        return $this->config['cache']['key']. ':' .$block. ':' .$constant.$identifier;  // Create unique key
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

        return $this->config['cache']['key']. ':' .$block. ':' .$key.$this->getId();
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
            return (int)$this->config['cache']['block']['temporary']['lifetime'];
        }
        return (int)$this->config['cache']['block']['permanent']['lifetime'];
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
     * Re authenticate cached permanent identity
     * 
     * @param array  $data  cached auth data
     * @param object $token token \Obullo\Authentication\Token
     * 
     * @return void
     */
    public function authenticatePermanentIdentity($data, Token $token)
    {
        /**
         * We override old authentication data
         * that we stored before as permanent
         */
        $data['__isAuthenticated'] = 1;
        $data['__isTemporary'] = 0;
        $data['__type'] = 'Authorized';
        $data['__token'] = $token->get();

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
        $key = $this->config['cache']['key'].':__permanent:Authorized:';
        
        foreach ($this->cache->getAllKeys($key.$identifier.':*') as $val) {
            $exp = explode(':', $val);
            $aid = end($exp);
            $sessions[$aid]['__isAuthenticated'] = $this->cache->hGet($key.$identifier.':'.$aid, '__isAuthenticated');
            $sessions[$aid]['__time'] = $this->cache->hGet($key.$identifier.':'.$aid, '__time');
            $sessions[$aid]['id'] = $identifier;
            $sessions[$aid]['key'] = $key.$identifier.':'.$aid;
            $sessions[$aid]['prefix'] = $key.$identifier;
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
        $this->cache->delete($this->config['cache']['key'].':__permanent:Authorized:'.$this->getId().':'.$aid);
    }

}

// END Redis.php File
/* End of file Redis.php

/* Location: .Obullo/Authentication/Storage/Redis.php */