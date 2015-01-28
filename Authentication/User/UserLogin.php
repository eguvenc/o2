<?php

namespace Obullo\Authentication\User;

use Auth\Constant,
    Auth\Identities\GenericUser,
    Auth\Identities\AuthorizedUser,
    Obullo\Utils\Random,
    Obullo\Container\Container,
    Obullo\Authentication\AuthResult,
    RuntimeException;

/**
 * O2 Authentication - User Login Class
 *
 * @category  Authentication
 * @package   Login
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class UserLogin
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
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('auth');

        $this->columnIdentifier = $this->c['auth.params']['db.identifier'];
        $this->columnPassword   = $this->c['auth.params']['db.password'];
    }

    /**
     * Enable verifiation before login
     *
     * @return void
     */
    public function enableVerification()
    {
        $this->c['auth.adapter']->enableVerification();
    }

    /**
     * Disable verifiation
     *
     * @return void
     */
    public function disableVerification()
    {
        $this->c['auth.adapter']->disableVerification();
    }

    /**
     * Start the Login Operation ( validate, authenticate, set failure object )
     * 
     * @param array   $credentials user data
     * @param boolean $rememberMe  remember me switch
     * 
     * @return object AuthResult object
     */
    public function attempt(array $credentials, $rememberMe = false)
    {
        $credentials['__rememberMe'] = ($rememberMe) ? 1 : 0;

        if ( ! isset($credentials[$this->columnIdentifier]) OR ! isset($credentials[$this->columnPassword]) ) {
            $message = sprintf(
                'Login attempt requires "%s" and "%s" credentials.', 
                $this->columnIdentifier,
                $this->columnPassword
            );
            return new AuthResult(
                array(
                    'code' => AuthResult::FAILURE,
                    'identifier' => $this->storage->getIdentifier(),
                    'messages' => array($message)
                )
            );
        }
        $rememberMeCookie = $this->config['login']['rememberMe']['cookie']['name'];
        $credentials['__rememberToken'] = (isset($_COOKIE[$rememberMeCookie])) ? $_COOKIE[$rememberMeCookie] : false;
        $authResult = $this->c['auth.adapter']->login(new GenericUser($credentials));
        
        /**
         * Create Login Attempt Event
         */
        $eventResult = $this->c['event']->fire('login.attempt', array($authResult));  // Returns to overriden auth result object
                                                                                      // Event fire returns multiple array response but we use one.
        return isset($eventResult[0]) ? current($eventResult) : $authResult;
    }

    /**
     * Authenticate temporary identity after verification
     * 
     * @return void
     */
    public function authenticateVerifiedIdentity()
    {
        return $this->c['auth.storage']->authenticateTemporaryIdentity();
    }

    /**
     * Validate a user's credentials without authenticate the user.
     *
     * @param array $credentials identities
     * 
     * @return bool
     */
    public function validate(array $credentials = array())
    {
        return $this->c['auth.adapter']->authenticate(new GenericUser($credentials), false);
    }

    /**
     * Public function
     * 
     * Validate a user against the given credentials.
     *
     * @param object $user        user identity
     * @param array  $credentials user credentials
     * 
     * @return bool
     */
    public function validateCredentials(AuthorizedUser $user, array $credentials)
    {
        $plain = $credentials[$this->columnPassword];

        return $this->c->load('service/password')->verify($plain, $user->getPassword());
    }
    
    /**
     * Returns the authentication adapter
     *
     * The adapter does not have a default if the storage adapter has not been set.
     *
     * @return Adapter|null
     */
    public function getAdapter()
    {
        return $this->c['auth.adapter'];
    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return Storage\StorageInterface
     */
    public function getStorage()
    {
        return $this->c['auth.storage'];
    }

}

// END UserLogin.php File
/* End of file UserLogin.php

/* Location: .Obullo/Authentication/User/UserLogin.php */