<?php

namespace Obullo\Authentication\User;

use Obullo\Container\ContainerInterface;

/**
 * O2 Authentication - Online Users Activity Class
 *
 * @category  Authentication
 * @package   Activity
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class Activity
{
    /**
     * Storage
     * 
     * @var object
     */
    protected $storage;

    /**
     * Identity
     * 
     * @var object
     */
    protected $identity;

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
    public function __construct(ContainerInterface $c)
    {
        $this->storage = $c['auth.storage'];
        $this->identity = $c['auth.identity'];

        $this->attributes = $this->identity->_activity;
        $this->identifier = $this->identity->getIdentifier();
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
        if (empty($this->identifier) || empty($key)) {
            return false;
        }
        $this->attributes[$key] = $val;
        return $this->storage->update('__activity', $this->attributes);
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
            $this->attributes = $this->identity->__activity;
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
        $this->storage->remove('__activity', $key);
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
        unset($this->identity->__activity);
        $this->storage->remove('__activity');
        return true;
    }
}

// END Activity.php File
/* End of file Activity.php

/* Location: .Obullo/Authentication/User/Activity.php */
