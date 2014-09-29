<?php

namespace Obullo\Auth\User;

use Auth\Identities\UserIdentity,
    Obullo\Auth\UserService;

/**
 * O2 Authentication - User Identity Class
 *
 * @category  Users
 * @package   Identities
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
Class Identity extends UserIdentity
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Auth config
     * 
     * @var array
     */
    protected $config;

    /**
     * Memory Storage
     * 
     * @var object
     */
    protected $storage;

    /**
     * Adapter
     * 
     * @var object
     */
    protected $adapter;

    /**
     * Keep in memory if user already is verified
     * 
     * @var mixed
     */
    protected $isVerified = null;

    /**
     * If token already valid don't validate it again.
     * 
     * @var boolean
     */
    protected $tokenIsValid = false;

    /**
     * Security token refresh seconds
     * 
     * @var int
     */
    protected $tokenRefreshSeconds;

    /**
     * Valid credentails
     * 
     * @var array
     */
    protected $credentials;

    /**
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Constructor
     * 
     * @param array $params object parameters
     */
    public function __construct(array $params)
    {
        $this->c = $params['c'];

        $this->config = $params['config'];
        $this->storage = $params['storage'];
        $this->adapter = $params['adapter'];

        if ($this->attributes = $this->credentials = $this->storage->getCredentials('__permanent')) {
            parent::__construct($this->attributes);
            $this->attributes['__isTemporary'] = 0;

        } elseif ($this->attributes = $this->credentials = $this->storage->getCredentials('__temporary')) {
            parent::__construct($this->attributes);
            $this->attributes['__isTemporary'] = 1;
        }
        if ( ! isset($this->attributes['__lastTokenRefresh'])) { // Create default token refresh value
            $this->attributes['__lastTokenRefresh'] = time();
        }

        $this->tokenRefreshSeconds = strtotime('- '.(int)$this->config['security']['token']['refresh'].' seconds');
        $this->logger = $this->c->load('return service/logger');

        register_shutdown_function(array($this, 'writeClose'));
    }

    /**
     * Get User has auth access 
     * 
     * its ok if returns to true otherwise false
     * 
     * @return boolean 
     */
    public function isAuthenticated()
    {          
        $tokenRefresh = false;
        if ($this->tokenRefreshSeconds > $this->attributes['__lastTokenRefresh']) {
            $this->attributes['__token'] = $this->adapter->refreshToken();  // Refresh the token and write it to memory
            $this->attributes['__lastTokenRefresh'] = time();
            $tokenRefresh = true;
        }
        if (isset($this->attributes['__isAuthenticated']) AND $this->attributes['__isAuthenticated'] == 1) {

            if ( ! $this->isValidToken($tokenRefresh)) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Returns to "1" if user authenticated on temporary memory block otherwise "0".
     * 
     * @return boolean
     */
    public function isTemporary()
    {
        return $this->attributes['__isTemporary'];
    }

    /**
     * Opposite of isAuthenticated function
     * 
     * @return boolean
     */
    public function isGuest()
    {
        if ($this->isAuthenticated()) {
            return false;
        }
        return true;
    }

    /**
     * Check user is verified after succesfull login
     * 
     * @return boolean
     */
    public function isVerified()
    {
        if ($this->isVerified != null) { // We store it into variable for application performance
            return 1;
        }
        if ($this->attributes['__isVerified'] == 1) {
            $this->isVerified = 1;
            return true;
        }
        return false;
    }

    /**
     * Set user roles ( !! Run this function just one time )
     * 
     * @param mixed $roles string|array
     * 
     * @return void
     */
    public function setRoles($roles)
    {
        $arrayRoles = (is_array($roles)) ? $roles : array($roles);
        $this->attributes['__roles'] = json_encode($arrayRoles);
    }

    /**
     * Get roles of user
     * 
     * @return array
     */
    public function getRoles()
    {
        if ( ! isset($this->attributes['__roles'])) {
            return array();
        }
        if (is_string($this->attributes['__roles'])) {
            return json_decode($this->attributes['__roles'], true);
        }
        return $this->attributes['__roles'];
    }

    /**
     * o2 Auth security token
     * 
     * We generate a uniuqe identity stamp for each users then every auth check operation we check it, 
     * If the user cookie token does not match the memory token we deny the user.
     * 
     * @return string unique identity stamp
     */
    protected function getToken()
    {
        return $this->attributes['__token'];
    }

    /**
     * Validate security token
     *
     * @param boolean $tokenRefresh don't do validate if we have token refresh request because of we have no cookie header.
     * 
     * @return bool
     */
    public function isValidToken($tokenRefresh)
    {
        if ( ! $this->exists() || $this->tokenIsValid || $tokenRefresh) { // If identitiy data does not exists.
            return true;
        }
        $cookie = $this->c->load('cookie')->get($this->config['security']['token']['name']);

        if ($cookie == $this->getToken()) {
            return $this->tokenIsValid = true;
        }
        $this->storage->deleteCredentials('__permanent'); // Delete user credentials from storage

        $this->logger->channel('security');
        $this->logger->notice('Auth token does not matched credentials removed.', array('identifer' => $this->getIdentifier()));

        return $this->tokenIsValid = false;
    }

    /**
     * Check new identity data available.
     * 
     * @return boolean
     */
    public function exists()
    {
        if (isset($this->attributes['__isTemporary'])) {  
            return true;
        }
        return false;
    }

    /**
     * Set all identity attributes
     * 
     * @param array $attributes identity array
     *
     * @return $object identity
     */
    public function setArray(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Get all identity attributes
     * 
     * @return array
     */
    public function getArray()
    {
        return $this->attributes;
    }

    /**
     * Get the password needs rehash array.
     *
     * @return mixed false|string new password hash
     */
    public function getPasswordNeedsReHash()
    {
        return isset($this->attributes['__passwordNeedsRehash']['hash']) ? $this->attributes['__passwordNeedsRehash']['hash'] : false;
    }

    /**
     * Get user type : UNVERIFIED, AUTHORIZED, GUEST
     * 
     * @return string
     */
    public function getType()
    {
        return isset($this->attributes['__type']) ? $this->attributes['__type'] : 'Guest';
    }

    /**
     * Returns to "1" user if used remember me
     * 
     * @return integer
     */
    public function getRememberMe() 
    {
        return $this->attributes['__rememberMe'];
    }

    /**
     * Sets authority of user to "0".
     * 
     * @return void 
     */
    public function logout()
    {
        $credentials = $this->storage->getCredentials('__permanent');

        $credentials['__isAuthenticated'] = 0; // Sets memory auth to "0".
        $credentials['__token'] = $this->adapter->refreshToken();
        $credentials['__type'] = 'Unauthorized';

        $this->storage->setCredentials($credentials, null, '__permanent');
    }

    /**
     * Deletes all identity data
     * 
     * @return boolean
     */
    public function destroy()
    {
        return $this->storage->deleteCredentials('__permanent');
    }

    /**
     * Writes identity data to storage at end of the process.
     *
     * We compare storage and php memory variables if we have any changes we push them to storage.
     * 
     * @return void
     */
    public function writeClose()
    {
        if ( ! isset($this->attributes['__isTemporary']) OR  ! is_array($this->credentials)) {  //  If user not logged in.
            return;
        }
        $oldCredentials = json_encode($this->credentials);
        $newCredentials = json_encode($this->getArray());

        $block = ($this->attributes['__isTemporary'] == 1) ? '__temporary' : '__permanent';

        if ($oldCredentials != $newCredentials) {
            $this->storage->setCredentials($this->getArray(), null, $block);
        }
    }

}

// END Identity.php File
/* End of file Identity.php

/* Location: .Obullo/Auth/User/Identity.php */