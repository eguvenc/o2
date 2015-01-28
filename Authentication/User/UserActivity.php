<?php

namespace Obullo\Authentication\User;

use Obullo\Container\Container,
    Obullo\Authentication\UserService;

/**
 * O2 Authentication - Online Users Activity Class
 *
 * @category  Authentication
 * @package   Activity
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class UserActivity
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Service cache
     * 
     * @var object
     */
    protected $cache;

    /**
     * Authentication config
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
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->storage = $this->c['auth.storage'];
        $this->config  = $this->c['config']->load('auth');
        $this->cache   = $this->c->load('cache');
        $this->session = $this->c->load('session');
        $this->request = $this->c->load('request');

        $this->attributes = $this->c['auth.identity']->__activity;
        $this->identifier = $this->c['auth.identity']->getIdentifier();
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
        unset($this->c['auth.identity']->__activity);
        return true;
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
        if ($this->config['activity']['uniqueSession'] AND $this->c['auth.identity']->check()) {        // Unique Session is the property whereby a single action of activity
            $this->c['event']->fire('auth.unique'); // Listener ( @see app/classes/Event/User.php  )    // terminates access to multiple sessions.
        }
        $this->c['auth.identity']->__activity = $this->attributes;  // Update activity data
    }

}

// END UserActivity.php File
/* End of file UserActivity.php

/* Location: .Obullo/Authentication/User/UserActivity.php */