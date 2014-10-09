<?php

namespace Obullo\Auth;

/**
 * Abstract Adapter
 * 
 * @category  Auth
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
abstract class AbstractAdapter
{
     /**
     * None authorized user
     */
    const GUEST = 'Guest';

    /**
     * Login success but verification is not completed ( if verification enabled ).
     */
    const UNVERIFIED = 'Unverified';

    /**
     * Successfully authorized user
     */
    const AUTHORIZED = 'Authorized';

    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Verification switch after successfull login
     * 
     * @var string
     */
    protected $verification = false;

    /**
     * Security token
     * 
     * @var string
     */
    protected $token = null;

    /**
     * Constructor
     * 
     * @param object $c container
     *
     * @return void
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Enable verifiation of user after successful login
     *
     * @return void
     */
    public function enableVerification()
    {
        $this->verification = true;
    }

    /**
     * Disable verifiation of user after successful login
     *
     * @return void
     */
    public function disableVerification()
    {
        $this->verification = false;
    }

    /**
     * Returns to user verification status
     * 
     * @return boolean
     */
    public function isEnabledVerification()
    {
        return (bool)$this->verification;
    }

    /**
     * Regenerate the session id
     *
     * @param bool $deleteOldSession whether to delete old session id
     * 
     * @return void
     */
    public function regenerateSessionId($deleteOldSession = true)
    {
        return $this->session->regenerateId($deleteOldSession);
    }

    /**
     * Generates random token for o2 auth security cookie
     * 
     * @return void
     */
    public function generateToken()
    {
        if ($this->token != null) {  // If we have already token don't regenerate it.
            return $this->token;
        }
        $token = $this->c->load('return utils/random')->generate('alnum', 16);
        $userAgent = trim(substr($this->c->load('request')->server('HTTP_USER_AGENT'), 0, 50));  // First 50 characters of the user agent
        return $this->token = $token .'.'. hash('adler32', $userAgent);  // Smallest token
    }

    /**
     * Refresh unique security token
     * 
     * @return void
     */
    public function refreshToken()
    {
        $token = $this->generateToken();
        $cookie = $this->config['security']['cookie'];

        $this->c->load('cookie')->set(
            $cookie['name'],
            $token,
            $cookie['expire'],
            $this->c['config']['cookie']['domain'],        //  Get domain from global config
            $cookie['path'],
            $cookie['prefix'],
            $cookie['secure'],
            $cookie['httpOnly']
        );
        return $token;
    }

    /**
     * Run cookie reminder
     * 
     * @return void
     */
    protected function refreshRememberMe()
    {
        $token = $this->c->load('return utils/random')->generate('alnum', 32);
        $cookie = $this->config['login']['rememberMe']['cookie'];

        $this->c->load('cookie')->set(
            $cookie['name'],
            $token,
            $cookie['expire'],
            $this->c['config']['cookie']['domain'],        //  Get domain from global config
            $cookie['path'],
            $cookie['prefix'],
            $cookie['secure'],
            $cookie['httpOnly']
        );
        $this->modelUser->refreshRememberMeToken($token, $this->user->identity);
    }

    /**
     * Check password is hashed or not ?
     *
     * $2y$10$0ICQkMUZBEAUMuyRYDlXe.PaOT4LGlbj6lUWXg6w3GCOMbZLzM7bm
     * 
     * @param string $hash hashed password
     * 
     * @return boolean
     */
    public function isHashedPassword($hash)
    {
        if (strlen($hash) > 50) {
            return true;
        }
        return false;
    }

    /**
     * Verify password hash
     * 
     * @param string $plain plain  password
     * @param string $hash  hashed password
     * 
     * @return boolean | array
     */
    public function verifyPassword($plain, $hash)
    {
        $passwordHash = array();
        if ($this->c->load('service/password')->verify($plain, $hash)) {
            if ($this->c->load('service/password')->needsRehash($hash, array('cost' => $this->config['security']['passwordNeedsRehash']['cost']))) {
                $value = $this->password->hash($plain, array('cost' => $this->config['security']['passwordNeedsRehash']['cost']));
                return $passwordHash = array('hash' => $value);
            }
            return true;
        }
        return false;
    }

    /**
     * If we want to use verification methods ( call, sms, email etc. ) we 
     * use $this->user->login->enableVerification(); before the login method.
     * 
     * @param array $credentials user identities
     * 
     * @return void
     */
    protected function push2Storage(array $credentials)
    {
        if ($this->isEnabledVerification()) {
            $this->storage->loginAsTemporary($credentials);
        } else {
            $this->storage->loginAsPermanent($credentials);
        }
    }

    /**
     * Returns to storage instance
     * 
     * @return object
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Sets "isAuthenticated" attribute
     *
     * @param array $attributes identity attributes
     * 
     * @return array $attributes
     */
    abstract protected function setAuthentication($attributes);

    /**
     * This method attempts to make
     * certain that only one record was returned in the resultset
     *
     * @return bool|Obullo\Auth\Result 
     */
    abstract protected function validateResultSet();

    /**
     * This method attempts to validate that
     * the record in the resultset is indeed a record that matched the
     * identity provided to this adapter.
     *
     * @return AuthResult
     */
    abstract protected function validateResult();
}

// END AbstractAdapter.php File
/* End of file AbstractAdapter.php

/* Location: .Obullo/Auth/AbstractAdapter.php */