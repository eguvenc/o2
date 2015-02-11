<?php

namespace Obullo\Authentication\User;

use Obullo\Container\Container,
    Obullo\Authentication\Token,
    Auth\Identities\GenericUser,
    Auth\Identities\AuthorizedUser,
    Obullo\Authentication\Recaller,
    Obullo\Authentication\UserService;

/**
 * O2 Authentication - User Identity Class
 *
 * @category  Users
 * @package   Identity
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class UserIdentity extends AuthorizedUser
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Authentication config
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
     * Store if user authenticated
     * 
     * @var boolean
     */
    protected $isAuthenticated = null;

    /**
     * Security token refresh seconds
     * 
     * @var int
     */
    protected $tokenRefreshSeconds;

    /**
     * Whether to refresh token
     * 
     * @var bool
     */
    protected $tokenRefresh = false;

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
     * Recaller
     * 
     * @var object
     */
    protected $recaller = null;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config  = $this->c['config']->load('auth');
        $this->storage = $this->c['auth.storage'];

        if ($token = $this->recallerExists()) {   // Remember the user if recaller cookie exists
            $this->recaller = new Recaller($this->c);
            $this->recaller->recallUser($token);
        }
        if ($this->attributes = $this->credentials = $this->storage->getCredentials('__permanent')) {
            parent::__construct($this->c, $this->attributes);
            $this->attributes['__isTemporary'] = 0;
            ksort($this->credentials);

        } elseif ($this->attributes = $this->credentials = $this->storage->getCredentials('__temporary')) {
            parent::__construct($this->c, $this->attributes);
            $this->attributes['__isTemporary'] = 1;
            ksort($this->credentials);
        }
        if ( ! isset($this->attributes['__lastTokenRefresh'])) { // Create default token refresh value
            $this->attributes['__lastTokenRefresh'] = time();
        }

        $this->tokenRefreshSeconds = strtotime('- '.(int)$this->config['security']['cookie']['refresh'].' seconds');
        $this->logger = $this->c['logger'];

        register_shutdown_function(array($this, 'writeClose'));
    }

    /**
     * Check user has identity
     * 
     * Its ok if returns to true otherwise false
     * 
     * @return boolean 
     */
    public function check()
    {
        if ( ! isset($this->attributes['__isAuthenticated'])) {
            return $this->isAuthenticated = false;
        }
        if ( ! is_null($this->isAuthenticated)) {
            return $this->isAuthenticated;
        }
        $this->tokenRefresh = false;
        if ($this->attributes['__isAuthenticated'] == 1 AND $this->tokenRefreshSeconds > $this->attributes['__lastTokenRefresh']) {  // Secutiry token update
            $token = new Token($this->c);
            $this->attributes['__token'] = $token->get();  // Refresh the token and write it to memory
            $this->attributes['__lastTokenRefresh'] = time();
            $this->tokenRefresh = true;
        }
        if ($this->attributes['__isAuthenticated'] == 1 AND $this->isValidToken()) {
            return $this->isAuthenticated = true;
        }
        return $this->isAuthenticated = false;
    }

    /**
     * Opposite of check() function
     * 
     * @return boolean
     */
    public function guest()
    {
        if ($this->check()) {
            return false;
        }
        return true;
    }

    /**
     * Check recaller cookie exists
     * 
     * @return string|boolean false
     */
    public function recallerExists()
    {
        $id = $this->storage->getIdentifier();
        $name = $this->config['login']['rememberMe']['cookie']['name'];

        $cookie = isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
        if (empty($id) AND $token = $cookie) {
            return $token;
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
     * O2 Authentication security token
     * 
     * We generate a uniuqe identity stamp for each users then every auth check operation we check it, 
     * If the user cookie token does not match the memory token we terminate the user.
     * 
     * @return string unique identity stamp
     */
    public function getStorageToken()
    {
        return $this->attributes['__token'];
    }

    /**
     * Read cookie security token
     * 
     * @return string|boolean
     */
    public function getCookieToken()
    {
        $name = $this->config['security']['cookie']['name'];
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
    }

    /**
     * Validate security token
     *
     * if we have $this->tokenRefresh = false don't do validate because of we have no cookie header.
     * 
     * @return bool
     */
    public function isValidToken()
    {
        if ( ! $this->exists() || $this->tokenIsValid || $this->tokenRefresh || ! is_null($this->recaller)) { // If identity data does not exists.
            return $this->tokenIsValid = true;
        }
        if ($this->getCookieToken() == $this->getStorageToken()) {
            return $this->tokenIsValid = true;
        }
        $this->logger->channel('security');
        $this->logger->notice(
            'Invalid auth token identity destroyed.', 
            [
                'identifier' => $this->getIdentifier(),
                'token' => $this->getStorageToken(),
                'cookie' => $this->getCookieToken()
            ]
        );
        $this->storage->deleteCredentials('__permanent'); // Delete user credentials from storage
        return $this->tokenIsValid = false;
    }

    /**
     * Checks new identity data available in storage.
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
     * Returns to unix microtime value.
     * 
     * @return string
     */
    public function getTime()
    {
        return isset($this->attributes['__time']) ? $this->attributes['__time'] : null;
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
        ksort($this->attributes);
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
        $token = new Token($this->c);
        $credentials['__isAuthenticated'] = 0;        // Sets memory auth to "0".
        $credentials['__token'] = $token->refresh();  // Refresh the security token 
        $credentials['__type'] = 'Unauthorized';

        $this->updateRememberToken();
        $this->storage->setCredentials($credentials, null, '__permanent');
    }

    /**
     * Logout User and destroy all identity data
     * 
     * @return void
     */
    public function destroy()
    {
        $this->updateRememberToken();
        $this->storage->deleteCredentials('__permanent');
    }


    /**
     * Update remember token if it exists in the memory and browser header
     * 
     * @return void
     */
    public function updateRememberToken()
    {
        if ($this->getRememberMe() == 1) {  // If user checked rememberMe option

            $rememberMeCookie = $this->config['login']['rememberMe']['cookie']['name'];
            $rememberToken = (isset($_COOKIE[$rememberMeCookie])) ? $_COOKIE[$rememberMeCookie] : false;

            $this->refreshRememberToken(
                new GenericUser(
                    $this->c, 
                    array($this->c['auth.params']['db.identifier'] => $this->getIdentifier(), '__rememberToken' => $rememberToken)
                )
            );
        }
    }

    /**
     * Refresh the rememberMe token
     *
     * @param object $genericUser GenericUser
     * 
     * @return void
     */
    public function refreshRememberToken(GenericUser $genericUser)
    {
        $token = new Token($this->c);
        $this->c['user.model']->updateRememberToken($token->getRememberToken(), $genericUser); // refresh rememberToken
    }
 

    /**
     * Removes rememberMe cookie from user browser
     *
     * @return void
     */
    public function forgetMe()
    {
        $cookie = $this->config['login']['rememberMe']['cookie']; // Delete rememberMe cookie if exists
        if ( ! isset($_COOKIE[$cookie['name']])) {
            return;
        }
        setcookie(
            $cookie['prefix'].$cookie['name'], 
            null,
            -1,
            $cookie['path'],
            $this->c['config']['cookie']['domain'],   //  Get domain from global config
            $cookie['secure'], 
            $cookie['httpOnly']
        );
    }

    /**
     * Writes identity data to storage at end of the process.
     *
     * We compare storage data and variables if we have any changes we push them to storage.
     * 
     * @return void
     */
    public function writeClose()
    {
        if ( ! isset($this->attributes['__isTemporary']) OR ! is_array($this->credentials)) {  //  If user not logged in.
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

// END UserIdentity.php File
/* End of file UserIdentity.php

/* Location: .Obullo/Authentication/User/UserIdentity.php */