<?php

namespace Obullo\Authentication\Identities;

/**
 * GenericUser Interface
 *
 * @category  Authentication
 * @package   IdentityInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
interface GenericUserInterface
{
    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getIdentifier();

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getPassword();

    /**
     * Dynamically access the user's attributes.
     *
     * @param string $key   key
     * @param mixed  $value value
     * 
     * @return void
     */
    public function __set($key, $value);

    /**
     * Dynamically access the user's attributes.
     *
     * @param string $key ket
     * 
     * @return mixed
     */
    public function __get($key);

    /**
     * Dynamically check if a value is set on the user.
     *
     * @param string $key key
     * 
     * @return bool
     */
    public function __isset($key);

    /**
     * Unset a value on the user.
     *
     * @param string $key key
     * 
     * @return bool
     */
    public function __unset($key);

}

// END GenericUserInterface.php File
/* End of file GenericUserInterface.php

/* Location: .Obullo/Authentication/GenericUserInterface.php */