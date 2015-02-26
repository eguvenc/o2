<?php

namespace Obullo\Authentication;

use Obullo\Container\Container;

/**
 * O2 Authentication - Abstract User Identity
 *
 * @category  Authentication
 * @package   AbstractUserIdentity
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class AbstractUserIdentity
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Credentials
     * 
     * @var array
     */
    protected $attributes = array();

    /**
     * Constructor
     * 
     * @param object $c          container
     * @param array  $attributes attributes
         */
    public function __construct(Container $c, $attributes = array())
    {
        $this->c = $c;
        $this->attributes = $attributes;
    }

    /**
     * Get identifier column
     * 
     * @return string
     */
    public function getColumnIdentifier()
    {
        return $this->c['auth.params']['db.identifier'];
    }

    /**
     * Get password column
     * 
     * @return string
     */
    public function getColumnPassword()
    {
        return $this->c['auth.params']['db.password'];
    }

    /**
     * Returns to "1" user if used remember me
     * 
     * @return integer
     */
    public function getRememberMe() 
    {
        return $this->__rememberMe;
    }
    
    /**
     * Get all attributes
     * 
     * @return array
     */
    public function getArray()
    {
        return $this->attributes;
    }

    /**
     * Dynamically access the user's attributes.
     *
     * @param string $key key
     * 
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : false;
    }

    /**
     * Dynamically set the user's attributes.
     *
     * @param string $key key
     * @param string $val value
     * 
     * @return mixed
     */
    public function __set($key, $val)
    {
        if ($this->__isAuthenticated == 1) {  // Reserved symbol
            $this->c['auth.storage']->update($key, $val);  // save to storage
        }
        return $this->attributes[$key] = $val;
    }

    /**
     * Dynamically check if a value is set on the user.
     *
     * @param string $key key
     * 
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Dynamically unset a value on the user.
     *
     * @param string $key key
     * 
     * @return void
     */
    public function __unset($key)
    {
        if ($this->__isAuthenticated == 1) {
            $this->c['auth.storage']->remove($key);
        }
        unset($this->attributes[$key]);
    }

}

// END AbstractUserIdentity.php File
/* End of file AbstractUserIdentity.php

/* Location: .Obullo/Authentication/AbstractUserIdentity.php */