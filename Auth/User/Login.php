<?php

namespace Obullo\Auth\User;

use Auth\Identities\GenericIdentity,
    Auth\Credentials,
    Obullo\Auth\UserService,
    RuntimeException;

/**
 * O2 Authentication - User Login Class
 *
 * @category  Auth
 * @package   Login
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
Class Login
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
     * Storage class
     * 
     * @var object
     */
    protected $storage;

    /**
     * Constructor
     * 
     * @param array $params parameters
     */
    public function __construct(array $params)
    {
        $this->c = $params['c'];
        $this->config = $params['config'];
        $this->storage = $params['storage'];
    }

    /**
     * Enable verifiation before login
     *
     * @return void
     */
    public function enableVerification()
    {
        $this->c['o2.auth.service.adapter']->enableVerification();
    }

    /**
     * Disable verifiation
     *
     * @return void
     */
    public function disableVerification()
    {
        $this->c['o2.auth.service.adapter']->disableVerification();
    }

    /**
     * Validate and Login
     * 
     * @param array   $credentials user data
     * @param boolean $rememberMe  remember me switch
     * 
     * @return object AuthResult object
     */
    public function attempt(array $credentials, $rememberMe = false)
    {
        $credentials['__rememberMe'] = ($rememberMe) ?  1 : 0;
        
        if ( ! isset($credentials[Credentials::IDENTIFIER]) OR ! isset($credentials[Credentials::PASSWORD]) ) {
            $message = sprintf(
                'Login attempt requires "%s" and "%s" credentials.', 
                Credentials::IDENTIFIER,
                Credentials::PASSWORD
            );
            return new AuthResult(
                array(
                    'code' => AuthResult::FAILURE,
                    'identity' => $this->storage->getIdentifier(),
                    'messages' => array($message)
                )
            );
        }
        $authResult = $this->c['o2.auth.service.adapter']->login(new GenericIdentity($credentials));
        return $authResult;
    }
 
    /**
     * Authenticate temporary identity after verification
     * 
     * @return void
     */
    public function authenticateVerifiedIdentity()
    {
        return $this->storage->authenticateTemporaryIdentity();
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
        return $this->c['o2.auth.service.adapter']->authenticate(new GenericIdentity($credentials), false);
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
    public function validateCredentials(UserIdentity $user, array $credentials)
    {
        $plain = $credentials[Credentials::PASSWORD];

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
        return $this->c['o2.auth.service.adapter'];
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
        return $this->c['o2.auth.service.adapter']->getStorage();
    }

}

// END Login.php File
/* End of file Login.php

/* Location: .Obullo/Auth/User/Login.php */