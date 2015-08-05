<?php

namespace Obullo\Authentication\User;

use Obullo\Event\EventInterface;
use Auth\Identities\GenericUser;
use Auth\Identities\AuthorizedUser;
use Obullo\Authentication\AuthResult;
use Obullo\Container\Container;
use Obullo\Authentication\User\IdentityInterface;
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
     * @param object $c        \Obullo\Container\Container
     * @param object $event    \Obullo\Event\Event
     * @param object $storage  \Obullo\Authentication\Storage\Storage
     * @param object $identity \Obullo\Authentication\Identity\Identity
     * @param array  $params   Auth config parameters
     */
    public function __construct(Container $c, EventInterface $event, StorageInterface $storage, IdentityInterface $identity, array $params)
    {
        $this->c = $c;
        $this->event = $event;
        $this->storage = $storage;
        $this->params = $params;
        $this->identity = $identity;
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

        $this->event->fire('login.before', array($credentials)); 

        $identifier = $this->params['db.identifier'];
        $password   = $this->params['db.password'];

        if (! isset($credentials[$identifier]) || ! isset($credentials[$password]) ) {
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
        $rememberMeCookie = $this->params['login']['rememberMe']['cookie'];
        $credentials['__rememberToken'] = $this->c['cookie']->get($rememberMeCookie['name'], $rememberMeCookie['prefix']);

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
        $genericUser->setCredentials($credentials);

        $authResult = $this->c['auth.adapter']->login($genericUser);

        $this->identity->initialize();
        $eventResult = $this->event->fire('login.after', array($authResult));  // Returns to overriden auth result object

        return isset($eventResult[0]) ? current($eventResult) : $authResult;           // Event fire returns multiple array response but we use one.
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
        $plain = $credentials[$this->params['db.password']];

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