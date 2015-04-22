<?php

namespace Obullo\Authentication\User;

use ArrayAccess;

/**
 * O2 Authentication - Control AuthServiceProvider Config Variables
 *
 * @category  Authentication
 * @package   AuthServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class Config implements ArrayAccess
{
    /**
     * Config array
     * 
     * @var array
     */
    protected $array;

    /**
     * Constructor
     *
     * Sets the $config data from the primary config.php file as a class variable
     * 
     * @param array $params config
     */
    public function __construct($params = array())
    {
        $this->array = $params;
    }

    /**
     * Sets a parameter or an object.
     *
     * @param string $key   The unique identifier for the parameter
     * @param mixed  $value The value of the parameter
     *
     * @return void
     */
    public function offsetSet($key, $value)
    { 
        $this->array[$key] = $value;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($key)
    {
        if ( ! isset($this->array[$key])) {
            return false;
        }
        return $this->array[$key];
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return Boolean
     */
    public function offsetExists($key)
    {
        return isset($this->array[$key]);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->array[$key]);
    }

}

// END Config.php File
/* End of file Config.php

/* Location: .Obullo/Authentication/User/Config.php */