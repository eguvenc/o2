<?php

namespace Obullo\Authentication;

use Obullo\Container\Container;

/**
 * O2 Authentication - Abstract Authorized User
 *
 * @category  Authentication
 * @package   AbstractAuthorizedUser
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class AbstractAuthorizedUser
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Service configuration parameters
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
        // $this->c['auth.storage']->set($key, $val);  // save to storage
        return $this->attributes[$key] = $val;
        // return $this->attributes[$key] = $val;
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
        unset($this->attributes[$key]);
        // $this->c['auth.storage']->remove($key);
    }

}

// END AbstractAuthorizedUser.php File
/* End of file AbstractAuthorizedUser.php

/* Location: .Obullo/Authentication/AbstractAuthorizedUser.php */