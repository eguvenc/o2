<?php

namespace Obullo\Auth\User;

use Auth\Identities\UserIdentitiy,
    Auth\Identities\GenericIdentity,
    Obullo\Auth\UserService;

/**
 * O2 Authentication - Online Users Activity Class
 *
 * @category  Auth
 * @package   Activity
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
Class Activity
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
     * User identifier ( id or username )
     * 
     * @var mixed
     */
    protected $identifier;

    /**
     * User service
     * 
     * @var object
     */
    protected $user;

    /**
     * Service cache
     * 
     * @var object
     */
    protected $cache;

    /**
     * Constructor
     * 
     * @param array $params object parameters
     */
    public function __construct(array $params)
    {
        $this->c = $params['c'];
        $this->config = $params['config']['activity'];
        $this->user = $params['user'];
        $this->storage = $this->user->params['storage'];
        $this->cache = $this->c->load('return service/cache');
        $this->session = $this->c->load('return session');
        $this->request = $this->c->load('return request');

        $this->attributes = array(
            'sid' => $this->session->get('session_id'),
            'lastActivity' => time()
        );
        $this->identifier = $this->user->identity->getIdentifier();
    }

    /**
     * Add activity data to user
     *
     * @param string|int $key key
     * @param string|int $val value
     * 
     * @return object this
     */
    public function set($key = null, $val = null)
    {
        if (empty($this->identifier) OR empty($key)) {
            return false;
        }
        $this->attributes[$key] = $val;
        return $this;
    }

    /**
     * Get an attribute value
     * 
     * @param string $key key
     * 
     * @return void
     */
    public function get($key)
    {
        return $this->attributes[$key];
    }

    /**
     * Remove user activity
     * 
     * @return boolean
     */
    public function remove()
    {
        if (empty($this->identifier)) {
            return false;
        }
        unset($this->user->identity->__activity);
        return true;
    }

    /**
     * Get multiple authenticated sessions
     * 
     * @return array|false
     */
    public function getAuthSessions()
    {
        if (empty($this->identifier)) {
            return false;
        }
        $sessions = array();
        $key = 'Auth:__permanent:Authorized:';
        foreach ($this->cache->getAllKeys($key.$this->identifier.':*') as $val) {
            $exp = explode(':', $val);
            $aid = end($exp);
            $sessions[$aid]['id'] = $this->identifier;
            $sessions[$aid]['key'] = $key.$this->identifier.':'.$aid;
            $sessions[$aid]['prefix'] = $key.$this->identifier;
        }
        return $sessions;
    }

    /**
     * Kill authority of user using auth id
     * 
     * @param string $aid microtime
     * 
     * @return boolean
     */
    public function killAuthSession($aid)
    {
        if (empty($this->identifier)) {
            return false;
        }
        $this->cache->delete('Auth:__permanent:Authorized:'.$this->identifier.':'.$aid);
    }

    /**
     * Check whether to user is online 
     * 
     * @return boolean
     */
    public function isSignedIn()
    {
        if ($this->user->identity->isAuthenticated()) {
            return true;
        }
        return false;
    }

    /**
     * Check whether to user is "not" online 
     * 
     * @return boolean
     */
    public function isSignedOut()
    {
        if ($this->user->identity->isGuest()) {
            return true;
        }
        return false;
    }

    /**
     * Update user activity
     * 
     * @return void
     */
    public function update()
    {
        if (empty($this->identifier)) {
            return false;
        }
        if ($this->config['singleSignOff'] AND $this->isSignedIn()) {  // Single sign-off is the property whereby a single action of signing out 
                                                                       // terminates access to multiple agents.
            $sessions = $this->getAuthSessions();
            if (sizeof($sessions) < 1) {  // If user have more than one auth session continue to destroy them.
                return;
            }
            $sessionKeys = array_keys($sessions);  // Keep the last session
            $lastSession = max($sessionKeys);      // Get the highest integer
            unset($sessions[$lastSession]);  // Don't touch the current session

            // Array ( [ 1413449703.5931] => Array ( 
            //     [id] => user@example.com 
            //     [key] => Auth:__permanent:Authorized:user@example.com:1413449703.5931 
            //     [prefix] => Auth:__permanent:Authorized:user@example.com ) 
            // )

            foreach (array_keys($sessions) as $aid) { // Destroy all other sessions
                $this->killAuthSession($aid);
            }
        }
        $this->user->identity->__activity = $this->attributes;  // Update activity data
    }

}

// END Activity.php File
/* End of file Activity.php

/* Location: .Obullo/Auth/User/Activity.php */