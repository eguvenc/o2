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
 * @license   http://opensource.org/licenses/MIT MIT license
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
     * @param object $c    container
     * @param object $user user service
     */
    public function __construct($c, $user)
    {
        $this->c = $c;
        $this->user = $user;
        $this->storage = $this->c['auth.storage'];
        $this->config = $this->c['config']->load('shared/auth');
        $this->cache = $this->c->load('return service/cache');
        $this->session = $this->c->load('return session');
        $this->request = $this->c->load('return request');

        $this->attributes = $this->user->identity->__activity;
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
        $key = $this->config['memory']['key'].':__permanent:Authorized:';
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
        $this->cache->delete($this->config['memory']['key'].':__permanent:Authorized:'.$this->identifier.':'.$aid);
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
        if ($this->config['activity']['singleSignOff'] AND $this->user->identity->check()) {  // Single sign-off is the property whereby a single action of signing out 
                                                                                              // terminates access to multiple login sessions.
            $sessions = $this->getAuthSessions();

            if (sizeof($sessions) < 1) {  // If user have more than one auth session continue to destroy them.
                return;
            }
            $sessionKeys = array();  // Keep the last session
            foreach ($sessions as $key => $val) {
                $time = $this->cache->hGet($val['key'], '__time');
                $sessionKeys[$time] = $key;
            }
            $lastSession = max(array_keys($sessionKeys));      // Get the highest integer
            $removalSession = $sessionKeys[$lastSession];
            unset($sessions[$removalSession]);  // Don't touch the current session
            /*
            Array ( 
                [ojj4ihn4m5] => Array ( 
                        [id] => user@example.com 
                        [key] => Auth:__permanent:Authorized:user@example.com:ojj4ihn4m5 
                        [prefix] => Auth:__permanent:Authorized:user@example.com ) 
                ) 
            */
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