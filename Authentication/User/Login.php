<?php

namespace Obullo\Authentication\User;

use Obullo\Event\EventInterface;
use Auth\Identities\GenericUser;
use Auth\Identities\AuthorizedUser;
use Obullo\Authentication\AuthResult;
use Obullo\Authentication\AuthConfig;
use Obullo\Container\ContainerInterface;
use Obullo\Authentication\Storage\StorageInterface;

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
     * Constructor
     *
     * @param object $c       \Obullo\Container\Container
     * @param object $event   \Obullo\Event\Event
     * @param object $storage \Obullo\Authentication\Storage\Storage
     * @param array  $params  Auth config parameters
     */
    public function __construct(ContainerInterface $c, EventInterface $event, StorageInterface $storage, array $params)
    {
        $this->c = $c;
        $this->event = $event;
        $this->storage = $storage;
        $this->params = $params;
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
        $this->ignoreRecaller();  // Ignore recaller if user has remember cookie
        $credentials['__rememberMe'] = ($rememberMe) ? 1 : 0;

        $credentials = $this->formatCredentials($credentials);
        if ($credentials == false) {
            $message = sprintf(
                'Login attempt requires "%s" and "%s" credentials.', 
                $credentials['db.identifier'],
                $credentials['db.password']
            );
            return new AuthResult(AuthResult::FAILURE, null, $message);
        }
        /**
         * Create Event: login.before
         */
        $this->event->fire('login.before', array($credentials)); 

        /**
         * Create AuthResult Object
         */
        return $this->createResults($credentials);
    }

    /**
     * Returns to true if remember me cookie exists
     * 
     * @return boolean
     */
    public function hasRememberMe()
    {
        $name = $this->params['login']['rememberMe']['cookie']['name'];
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
    }

    /**
     * Remove recaller cookie and ignore recaller
     * functionality.
     * 
     * @return void
     */
    public function ignoreRecaller()
    {
        if ($this->hasRememberMe()) {
            $this->c['session']->set('Auth/IgnoreRecaller', 1);
        }
    }

    /**
     * Combine credentials with real column names
     * 
     * @param array $credentials id & password data
     * 
     * @return boolean
     */
    public function formatCredentials(array $credentials)
    {   
        $i = AuthConfig::get('db.identifier');
        $p = AuthConfig::get('db.password');

        if (isset($credentials[$i]) && isset($credentials[$p])) {
            return $credentials;
        } elseif (isset($credentials['db.identifier']) && isset($credentials['db.password'])) {
            $credentials[$i] = $credentials['db.identifier'];
            $credentials[$p] = $credentials['db.password'];
            return $credentials;
        }
        return false;
    }

    /**
     * Create login attemtp and returns to auth result object
     * 
     * @param array $credentials login credentials
     * 
     * @return object AuthResult
     */
    protected function createResults($credentials)
    {
        $genericUser = new GenericUser;
        $genericUser->setCredentials($credentials);

        /**
         * Login Query
         * 
         * @var object
         */
        $authResult = $this->c['auth.adapter']->login($genericUser);

        /**
         * Generate User Identity
         */
        $this->c['auth.identity']->initialize();

        /**
         * Create Event: login.after
         *
         * Returns to overriden auth result object
         * 
         * @var object
         */
        $eventResult = $this->event->fire('login.after', array($authResult));

        /**
         * Event fire returns multiple array response but we use one.
         */
        return isset($eventResult[0]) ? current($eventResult) : $authResult;
    }

    /**
     * Validate a user's credentials without authenticate the user.
     *
     * @param string $tablename   modelname
     * @param array  $credentials identities
     * 
     * @return bool
     */
    public function validate($tablename, array $credentials = array())
    {        
        $this->setQueryModel($tablename);
        $credentials = $this->formatCredentials($credentials);

        $genericUser = new GenericUser;
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
        $password = AuthConfig::get('db.password');
        $plain = $credentials[$password];

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
        return $this->storage->getUserSessions();
    }

}