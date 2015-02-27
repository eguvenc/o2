<?php

namespace Obullo\Authentication\User;

use Obullo\Container\Container;
use Auth\Identities\GenericUser;
use Obullo\Authentication\Token;
use Auth\Identities\AuthorizedUser;
use Obullo\Authentication\Recaller;

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
class UserIdentity extends AuthorizedUser
{
    /**
     * Authentication config
     *
     * @var array
     */
    protected $config;

    /**
     * Logger
     *
     * @var object
     */
    protected $logger;

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
        $this->setContainer($c);
        $this->c['config']->load('auth');
        $this->storage = $this->c['auth.storage'];

        if ($token = $this->recallerExists()) {   // Remember the user if recaller cookie exists
            $this->recaller = new Recaller($this->c);
            $this->recaller->recallUser($token);
        }
        $this->initialize();

        $this->tokenRefreshSeconds = strtotime('- ' . (int) $this->c['config']['auth']['security']['cookie']['refresh'] . ' seconds');
        $this->logger              = $this->c['logger'];
    }

    /**
     * Initializer
     *
     * @return void
     */
    public function initialize()
    {
        if ($this->attributes = $this->storage->getCredentials('__permanent')) {
            $this->__isTemporary = 0;
            $this->setCredentials($this->attributes);

            if (! isset($this->__lastTokenRefresh)) { // Create default token refresh value
                $this->__lastTokenRefresh = time();
            }

        } elseif ($this->attributes = $this->storage->getCredentials('__temporary')) {
            $this->setCredentials($this->attributes);
        }
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
        if (! isset($this->__isAuthenticated)) {
            return $this->isAuthenticated = false;
        }
        if (! is_null($this->isAuthenticated)) {
            return $this->isAuthenticated;
        }
        
        // $this->tokenRefresh = false;

        if ($this->__isAuthenticated == 1 AND $this->tokenRefreshSeconds > $this->__lastTokenRefresh) {  // Secutiry token update
            $token                    = new Token($this->c);
            $this->__token            = $token->get();  // Refresh the token and write it to memory
            $this->__lastTokenRefresh = time();
            $this->tokenRefresh       = true;
        }
        if ($this->__isAuthenticated == 1 and $this->isValidToken()) {
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
        $id   = $this->storage->getIdentifier();
        $name = $this->c['config']['auth']['login']['rememberMe']['cookie']['name'];

        $cookie = isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
        if (empty($id) and $token = $cookie) {
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
        return $this->__isTemporary;
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
        if (isset($this->__isVerified) and $this->__isVerified == 1) {
            $this->isVerified = 1;
            return true;
        }
        return false;
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
        return $this->__token;
    }

    /**
     * Read cookie security token
     *
     * @return string|boolean
     */
    public function getSessionToken()
    {
        // $name = $this->c['config']['auth']['security']['cookie']['name'];
        $cookie = $this->config['security']['cookie'];

        return $this->c['session']->get($cookie['prefix'] . $cookie['name']);
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
        if (! $this->exists() || $this->tokenIsValid || $this->tokenRefresh == true || ! is_null($this->recaller) || ! $this->getSessionToken()) { // If identity data does not exists.
            return $this->tokenIsValid = true;
        }
        if ($this->getSessionToken() == $this->getStorageToken()) {
            return $this->tokenIsValid = true;
        }
        $this->logger->error('Token INVALID' . $this->__token, [ $this->getSessionToken()]);
        $this->logger->channel('security');
        $this->logger->notice(
            'Invalid auth token identity destroyed.',
            [
                'identifier' => $this->getIdentifier(),
                'token'      => $this->getStorageToken(),
                'cookie'     => $this->getSessionToken(),
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
        if (isset($this->__isTemporary)) {
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
        return isset($this->__time) ? $this->__time : null;
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
        return isset($this->__passwordNeedsRehash) ? $this->__passwordNeedsReHash['hash'] : false;
    }

    /**
     * Get user type : UNVERIFIED, AUTHORIZED, GUEST
     *
     * @return string
     */
    public function getType()
    {
        return isset($this->__type) ? $this->__type : 'Guest';
    }

    /**
     * Returns to "1" user if used remember me
     *
     * @return integer
     */
    public function getRememberMe()
    {
        return isset($this->__rememberMe) ? $this->__rememberMe : 0;
    }

    /**
     * Returns to remember token
     *
     * @return integer
     */
    public function getRememberToken()
    {
        return isset($this->__rememberToken) ? $this->__rememberToken : false;
    }

    /**
     * Sets authority of user to "0".
     *
     * @return void
     */
    public function logout()
    {
        $credentials                      = $this->storage->getCredentials('__permanent');
        $token                            = new Token($this->c);
        $credentials['__isAuthenticated'] = 0;        // Sets memory auth to "0".
        $credentials['__token']           = $token->refresh();  // Refresh the security token
        $credentials['__type']            = 'Unauthorized';

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

            $rememberMeCookie = $this->c['config']['auth']['login']['rememberMe']['cookie']['name'];
            $rememberToken    = (isset($_COOKIE[$rememberMeCookie])) ? $_COOKIE[$rememberMeCookie] : false;

            $genericUser = new GenericUser;
            $genericUser->setContainer($this->c);
            $genericUser->setCredentials([$this->c['auth.params']['db.identifier'] => $this->getIdentifier(), '__rememberToken' => $rememberToken]);

            $this->refreshRememberToken($genericUser);
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
        $cookie = $this->c['config']['auth']['login']['rememberMe']['cookie']; // Delete rememberMe cookie if exists
        if (! isset($_COOKIE[$cookie['name']])) {
            return;
        }
        setcookie(
            $cookie['prefix'] . $cookie['name'],
            null,
            -1,
            $cookie['path'],
            $this->c['config']['cookie']['domain'],   //  Get domain from global config
            $cookie['secure'],
            $cookie['httpOnly']
        );
    }
}

// END UserIdentity.php File
/* End of file UserIdentity.php

/* Location: .Obullo/Authentication/User/UserIdentity.php */