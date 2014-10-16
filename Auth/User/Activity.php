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

        $this->identifier = $this->user->identity->getIdentifier();
    }

    /**
     * Add activity data to user
     *
     * @return void
     */
    public function add()
    {
        if (empty($this->identifier)) {
            return false;
        }
        $this->attributes = array(
            'sid' => $this->session->get('session_id'),
            'lastActivity' => time()
        );
        if ($this->config['singleSignOff'] AND $this->isSignedIn()) {  // Single sign-off is the property whereby a single action of signing out 
                                                                       // terminates access to multiple agents.
            $sessions = $this->getAuthSessions();
            if (sizeof($sessions) < 1) {  // If we user have more than one auth session continue and destroy them.
                return;
            }
            $sessionKeys = array_keys($sessions);  // Keep last session
            $lastSession = end($sessionKeys);
            unset($sessions[$lastSession]);  // Don't touch the last session

            foreach ($sessions as $aid) {    // Destroy all other sessions
                $this->killAuthSession($aid);
            }
        }
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
        $i = 0;
        foreach ($this->cache->getAllKeys($key.$this->identifier.':*') as $val) {
            ++$i;
            $exp = explode(':', $val);
            $aid = end($exp);
            $sessions[$i]['aid'] = $aid;
            $sessions[$i]['uid'] = $this->identifier;
            $sessions[$i]['key'] = $key.$this->identifier.':'.$aid;
            $sessions[$i]['prefix'] = $key.$this->identifier;
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
        return $this->cache->delete('Auth:__permanent:Authorized:'.$this->identifier.':'.$aid);
    }

    /**
     * Check whether to user is online 
     * 
     * @return boolean
     */
    public function isSignedIn()
    {
        if ($this->user->identity->isAuthenticated() AND isset($this->attributes['sid'])) {
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
        if ($this->user->identity->isGuest() OR  ! isset($this->attributes['sid'])) {
            return true;
        }
        return false;
    }

    /**
     * Set an attribute to activity data
     * 
     * @param string $key key
     * @param mixed  $val value
     *
     * @return void
     */
    public function setAttribute($key, $val)
    {
        $this->attributes[$key] = $val;
    }

    /**
     * Get an attribute value
     * 
     * @param string $key key
     * 
     * @return void
     */
    public function getAttribute($key)
    {
        return $this->attributes[$key];
    }

    /**
     * Update user activity
     * 
     * @return void
     */
    public function refresh()
    {
        if (empty($this->identifier)) {
            return false;
        }
        $this->user->identity->__activity = $this->attributes;
    }

}

// END Activity.php File
/* End of file Activity.php

/* Location: .Obullo/Auth/User/Activity.php */