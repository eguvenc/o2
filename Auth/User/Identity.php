<?php

namespace Obullo\Auth\User;

use Auth\Model\User,
    Auth\Credentials,
    Obullo\Auth\Token,
    Obullo\Auth\UserService,
    Auth\Identities\UserIdentity,
    Auth\Identities\GenericIdentity;

/**
 * O2 Authentication - User Identity Class
 *
 * @category  Users
 * @package   Identity
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
     * If user already is verified
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
        // $this->adapter = $params['adapter'];

        if ($token = $this->recallerExists()) {  // Remember the user if recaller cookie exists
            $this->recallUser($token);
        }
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
        $this->tokenRefreshSeconds = strtotime('- '.(int)$this->config['security']['cookie']['refresh'].' seconds');
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
        if ( ! isset($this->attributes['__isAuthenticated'])) {
            return false;
        }
        $tokenRefresh = false;
        if ($this->tokenRefreshSeconds > $this->attributes['__lastTokenRefresh']) {  // Secutiry token update
            $token = new Token($this->c, $this->config);
            $this->attributes['__token'] = $token->refresh();  // Refresh the token and write it to memory
            $this->attributes['__lastTokenRefresh'] = time();
            $tokenRefresh = true;
        }
        if ($this->attributes['__isAuthenticated'] == 1 AND $this->isValidToken($tokenRefresh)) {
            return true;
        }
        return false;
    }

    /**
     * Check recaller cookie exists
     * 
     * @return string|boolean false
     */
    protected function recallerExists()
    {
        $id = $this->storage->getIdentifier();
        $name = $this->config['login']['rememberMe']['cookie']['name'];

        if (empty($id) AND $token = $this->c->load('cookie')->get($name)) {
            return $token;
        }
        return false;
    }

    /**
     * Recall user identity using remember token
     * 
     * @param string $token remember me cookie
     * 
     * @return void
     */
    protected function recallUser($token)
    {
        $modelUser = new User($this->c, $this->storage);
        $resultRowArray = $modelUser->execRecallerQuery($token);

        if ( ! is_array($resultRowArray)) {           // If login query not success.
            $this->storage->setIdentifier('Guest');   // Mark user as guest

            $cookie = $this->config['login']['rememberMe']['cookie']; // Delete rememberMe cookie
            $this->c->load('cookie')->delete(
                $cookie['name'],
                $this->c['config']['cookie']['domain'], //  Get domain from global config
                $cookie['path'],
                $cookie['prefix']
            );
            return;
        }
        $id = $resultRowArray[Credentials::IDENTIFIER];
        $this->storage->setIdentifier($id);
        
        $genericUser = new GenericIdentity(array(Credentials::IDENTIFIER => $id));

        $adapter = $this->c['o2.auth.service.adapter'];
        $adapter->generateUser($genericUser, $resultRowArray, $modelUser, true);
        $modelUser->refreshRememberMeToken($adapter->getRememberToken(), $genericUser);
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
        if (isset($this->attributes['__isVerified']) AND $this->attributes['__isVerified'] == 1) {
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
        $cookie = $this->c->load('cookie')->get($this->config['security']['cookie']['name']);

        if ($cookie == $this->getToken()) {
            return $this->tokenIsValid = true;
        }
        $this->storage->deleteCredentials('__permanent'); // Delete user credentials from storage

        $this->logger->channel('security');
        $this->logger->notice('Auth token does not matched credentials removed.', array('identifier' => $this->getIdentifier()));

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
        return isset($this->attributes['__rememberMe']) ? $this->attributes['__rememberMe'] : 0;
    }

    /**
     * Returns to remember token
     * 
     * @return integer
     */
    public function getRememberToken() 
    {
        return isset($this->attributes['__rememberToken']) ? $this->attributes['__rememberToken'] : false;
    }

    /**
     * Sets authority of user to "0".
     * 
     * @return void 
     */
    public function logout()
    {
        $credentials = $this->storage->getCredentials('__permanent');

        $token = new Token($this->c, $this->config);
        $credentials['__isAuthenticated'] = 0;        // Sets memory auth to "0".
        $credentials['__token'] = $token->refresh();  // Refresh the security token 
        $credentials['__type'] = 'Unauthorized';

        if ($credentials['__rememberMe'] == 1) {             // If user checked rememberMe option
            $modelUser = new User($this->c, $this->storage);
            $modelUser->refreshRememberMeToken($this->c['o2.auth.service.adapter']->getRememberToken(), new GenericIdentity($credentials)); // refresh rememberToken
        }
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