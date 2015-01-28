<?php

namespace Obullo\Authentication;

use Auth\Identities\GenericUser,
    Obullo\Container\Container;

/**
 * Abstract Adapter
 * 
 * @category  Authentication
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
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
     * Authentication config
     * 
     * @var object
     */
    protected $config;

    /**
     * Verification switch after successfull login
     * 
     * @var string
     */
    protected $verification = false;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $c->load('config')->load('auth');
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
        $cost = $this->config['security']['passwordNeedsRehash']['cost'];
        $password = $this->c->load('service/password');

        if ($password->verify($plain, $hash)) {
            if ($password->needsRehash($hash, array('cost' => $cost))) {
                $value = $password->hash($plain, array('cost' => $cost));
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
    protected function write2Storage(array $credentials)
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
        return $this->c['auth.storage'];
    }

    /**
     * Sets "isAuthenticated" attribute
     *
     * @param array $attributes identity attributes
     * 
     * @return array $attributes
     */
    abstract protected function setAuthType($attributes);

    /**
     * This method attempts to make
     * certain that only one record was returned in the resultset
     *
     * @return bool|Obullo\Authentication\Result 
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

/* Location: .Obullo/Authentication/AbstractAdapter.php */