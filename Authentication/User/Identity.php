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
 * @copyright 2009-2015 Obullo
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
     * Recaller
     *
     * @var object
     */
    protected $recaller = null;

    /**
     * If user already is verified
     *
     * @var mixed
     */
    protected $isVerified = null;

    /**
     * Keeps unique session login ids to destroy them
     * in destruct method.
     * 
     * @var array
     */
    protected $killSignal = array();

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
        $this->logger = $this->c['logger'];

        $this->initialize();

        if ($this->config['session']['unique']) {
            register_shutdown_function(array($this, 'close'));
        }
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
            $this->attributes['__activity']['last'] = time();
            $this->setCredentials($this->attributes);
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
        $name = $this->config['login']['rememberMe']['cookie']['name'];
        $token = isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
     
        if ( ! $this->storage->hasIdentifier() AND ctype_alnum($token) AND strlen($token) == 32) {  // Check recaller cookie value is alfanumeric
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
        if (is_array($this->attributes)) {
            ksort($this->attributes);
        }
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
        $credentials = $this->storage->getCredentials('__permanent');
        $credentials['__isAuthenticated'] = 0;        // Sets memory auth to "0".

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
    public function updateTemporary($key, $val)
    {
        $this->storage->update($key, $val, '__temporary');
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
            $rememberToken = $this->c['cookie']->get($rememberMeCookie['name'], $rememberMeCookie['prefix']);

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

    /**
     * Kill authority of user using auth id
     * 
     * @param integer $loginId e.g: 87060e89
     * 
     * @return boolean
     */
    public function killSignal($loginId)
    {
        $this->killSignal[$loginId] = $loginId;
    }

    /**
     * Do finish operations
     * 
     * @return void
     */
    public function close()
    {
        // $this->storage->resetTimeout();  // Increase permanent 

        if (empty($this->killSignal)) {
            return;
        }
        foreach ($this->killSignal as $loginId) {
            $this->storage->killSession($loginId);
        }
    }

}

// END Identity.php File
/* End of file Identity.php

/* Location: .Obullo/Authentication/User/Identity.php */