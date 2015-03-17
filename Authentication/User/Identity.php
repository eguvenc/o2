<?php

namespace Obullo\Authentication\User;

use Obullo\Container\Container;
use Auth\Identities\GenericUser;
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
class Identity extends AuthorizedUser
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
     * Security token refresh seconds
     *
     * @var int
     */
    protected $tokenRefreshSeconds;

    /**
     * Security token check frequency request / times
     * 
     * @var int
     */
    protected $tokenFrequency;

    /**
     * Recaller
     *
     * @var object
     */
    protected $recaller = null;

    /**
     * Security cookie token value
     * 
     * @var boolean
     */
    protected $cookieToken = false;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->setContainer($c);

        $this->config = $this->c['auth.config'];
        $this->storage = $this->c['auth.storage'];

        if ($rememberToken = $this->recallerExists()) {   // Remember the user if recaller cookie exists
            $this->recaller = new Recaller($this->c);
            $this->recaller->recallUser($rememberToken);
        }
        $frequency = $this->config['security']['cookie']['frequency'];
        $this->tokenFrequency = ($frequency < 3) ? 4 : $frequency + 1;
        $this->tokenRefreshSeconds = strtotime('- ' . (int) $this->config['security']['cookie']['refresh'] . ' seconds');
        $this->logger = $this->c['logger'];

        $this->initialize();
    }

    /**
     * Initializer
     *
     * @return void
     */
    public function initialize()
    {
        $this->cookieToken = $this->c['auth.token']->getCookie();

        if ($this->attributes = $this->storage->getCredentials('__permanent')) {
            $this->__isTemporary = 0;
            $this->setCredentials($this->attributes);

            if ( ! isset($this->__lastTokenRefresh)) {  // Create default token refresh value
                $this->__lastTokenRefresh = time();
                $this->__tokenFrequency = 0;
            }

        } elseif ($this->attributes = $this->storage->getCredentials('__temporary')) {
            $this->setCredentials($this->attributes);
        }
        $this->tokenRefresh();
    }

    /**
     * Refresh security cookie
     *
     * @return void
     */
    protected function tokenRefresh()
    {
        if ($this->check()) {

            if ($this->tokenRefreshSeconds > $this->__lastTokenRefresh) {  // Start secutiry token update operation
                $this->__token = $this->c['auth.token']->get();  // Refresh the token and write it to memory
                $this->__lastTokenRefresh = time();
                $this->__tokenFrequency = 0;   // Reset token check
            } else {
                /**
                 * We could not do token validation every time this cause
                 * cookie read problems and logout the users.
                 * 
                 * This solution increase the token frequency value 
                 * and checks the token for every 3 request.
                 */
                $times = $this->__tokenFrequency;
                if ($times < $this->tokenFrequency) {
                    $this->__tokenFrequency = $times + 1;
                }
                $this->isValidToken();  // Check token validation
            }
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
        if (isset($this->__isAuthenticated) AND $this->__isAuthenticated == 1) {
            return true;
        }
        return false;
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
        return $this->__isTemporary;
    }

    /**
     * Move permanent identity to temporary block
     * 
     * @return void
     */
    public function makeTemporary() 
    {
        $this->storage->makeTemporary();
    }

    /**
     * Move temporary identity to permanent block
     * 
     * @return void
     */
    public function makePermanent() 
    {
        $this->storage->makePermanent();
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
        if (isset($this->__isVerified) AND $this->__isVerified == 1) {
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
    public function getCookieToken()
    {
        return $this->cookieToken;
    }

    /**
     * Validate security token
     *
     * @return bool
     */
    public function isValidToken()
    {
        if ( ! $this->exists() || $this->tokenIsValid || $this->__tokenFrequency != $this->tokenFrequency || ! is_null($this->recaller)) { // If identity data does not exists.
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
                'token'      => $this->getStorageToken(),
                'cookie'     => $this->getCookieToken(),
            ]
        );
        $this->storage->deleteCredentials('__permanent'); // Delete permanent credentials from storage
        return $this->tokenIsValid = false;
    }

    /**
     * Checks new identity data available in storage.
     *
     * @return boolean
     */
    public function exists()
    {
        if (isset($this->__isAuthenticated)) {
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
        return isset($this->__passwordNeedsRehash) ? $this->__passwordNeedsReHash['hash'] : false;
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
        $credentials['__isAuthenticated'] = 0;        // Sets memory auth to "0".
        $credentials['__token']           = $this->c['auth.token']->get();  // Refresh the security token
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
     * Update temporary credentials
     * 
     * @param string $key key
     * @param string $val value
     * 
     * @return void
     */
    public function updateTemporaryCredentials($key, $val)
    {
        $this->c['auth.storage']->update($key, $val);
    }

    /**
     * Update remember token if it exists in the memory and browser header
     *
     * @return void
     */
    public function updateRememberToken()
    {
        if ($this->getRememberMe() == 1) {  // If user checked rememberMe option

            $rememberMeCookie = $this->config['login']['rememberMe']['cookie'];
            $rememberToken = $this->c['cookie']->get($rememberMeCookie['prefix'].$rememberMeCookie['name']);

            $genericUser = new GenericUser;
            $genericUser->setContainer($this->c);
            $genericUser->setCredentials([$this->config['db.identifier'] => $this->getIdentifier(), '__rememberToken' => $rememberToken]);

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
        $this->c['user.model']->updateRememberToken($this->c['auth.token']->getRememberToken(), $genericUser); // refresh rememberToken
    }

    /**
     * Removes rememberMe cookie from user browser
     *
     * @return void
     */
    public function forgetMe()
    {
        $cookie = $this->config['login']['rememberMe']['cookie']; // Delete rememberMe cookie if exists
        if ( ! $this->c['cookie']->get($cookie['name'], $cookie['prefix'])) {
            return;
        }
        $this->c['cookie']->delete($cookie['name'], $cookie['prefix']);
    }

}

// END Identity.php File
/* End of file Identity.php

/* Location: .Obullo/Authentication/User/Identity.php */