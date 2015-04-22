<?php

namespace Obullo\Authentication\User;

use Obullo\Container\Container;
use Auth\Identities\GenericUser;
use Auth\Identities\AuthorizedUser;
use Obullo\Authentication\AuthResult;

/**
 * O2 Authentication - Login Class
 *
 * @category  Authentication
 * @package   Login
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class Login
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
        $this->config = $this->c['auth.config'];
        $this->columnIdentifier = $this->config['db.identifier'];
        $this->columnPassword   = $this->config['db.password'];
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

        $this->c['event']->fire('login.attempt.before', array($credentials)); 

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
        $rememberMeCookie = $this->config['login']['rememberMe']['cookie'];
        $credentials['__rememberToken'] = $this->c['cookie']->get($rememberMeCookie['prefix'].$rememberMeCookie['name']);

        return $this->createResults($credentials);

    }

    /**
     * Create login attemt and returns to auth result object
     * 
     * @param array $credentials login credentials
     * 
     * @return object AuthResult
     */
    protected function createResults($credentials)
    {
        $genericUser = new GenericUser;
        $genericUser->setContainer($this->c);
        $genericUser->setCredentials($credentials);

        $authResult = $this->c['auth.adapter']->login($genericUser);
        $this->c['user']->identity->initialize();

        $eventResult = $this->c['event']->fire('login.attempt.after', array($authResult));  // Returns to overriden auth result object
        return isset($eventResult[0]) ? current($eventResult) : $authResult;                // Event fire returns multiple array response but we use one.
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
        $genericUser = new GenericUser;
        $genericUser->setContainer($this->c);
        $genericUser->setCredentials($credentials);
        
        return $this->c['auth.adapter']->authenticate($genericUser, false);
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

        return $this->c['password']->verify($plain, $user->getPassword());
    }

    /**
     * Returns to all sessions of valid user
     *
     * One user may have multiple sessions on different 
     * devices.
     * 
     * @return array
     */
    public function getUserSessions()
    {
        return $this->c['auth.storage']->getUserSessions();
    }

}

// END Login.php File
/* End of file Login.php

/* Location: .Obullo/Authentication/User/Login.php */