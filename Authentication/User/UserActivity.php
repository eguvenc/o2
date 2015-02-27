<?php

namespace Obullo\Authentication\User;

use Obullo\Container\Container;
use Obullo\Authentication\UserService;

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
     * AuthorizedUserIdentity data
     * 
     * @var array
     */
    protected $attributes;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config  = $this->c['config']->load('auth');

        $this->attributes = $this->c['auth.identity']->__activity;
        $this->identifier = $this->c['auth.identity']->getIdentifier();

        // register_shutdown_function($this, 'write');
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
        return $this->c['auth.storage']->update('__activity', $this->attributes);
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
        if (isset($this->attributes[$key])) {
            $this->attributes = $this->c['auth.identity']->__activity;
        }
        return isset($this->attributes[$key]) ? $this->attributes[$key] : false;
    }

    /**
     * Removes one activity item
     * 
     * @param string $key key
     * 
     * @return void
     */
    public function remove($key)
    {
        if (empty($this->identifier)) {
            return false;
        }
        unset($this->attributes[$key]);
        $this->c['auth.storage']->remove('__activity', $key);
        return true;
    }

    /**
     * Removes all user activity
     * 
     * @return boolean
     */
    public function destroy()
    {
        if (empty($this->identifier)) {
            return false;
        }
        unset($this->c['auth.identity']->__activity);
        $this->c['auth.storage']->remove('__activity');
        return true;
    }
}

// END UserActivity.php File
/* End of file UserActivity.php

/* Location: .Obullo/Authentication/User/UserActivity.php */
