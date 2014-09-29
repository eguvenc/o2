<?php

namespace Obullo\Crypt\Password;

/**
 * Password Bcrypt class.
 * 
 * @category  Password
 * @package   Bcrypt
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/bcrypt
 * @see       http://www.php.net/manual/en/ref.password.php
 */
Class Bcrypt
{
    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $Class = (version_compare(phpversion(), '5.5.0', '<')) ? 'Obullo\Crypt\Password\OldVersion' : 'Obullo\Crypt\Password\NewVersion';
        $this->driver = new $Class($c);
    }

    /**
     * Creates a password hash.
     * 
     * @param string $value   value
     * @param array  $options options
     * 
     * @example $2y$07$BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq
     * @return  returns the hashed password, or false on failure.
     */
    public function hash($value, array $options = array())
    {
        return $this->driver->hash($value, $options);
    }

    /**
     * Verifies that a password matches a hash.
     * 
     * @param string $value       value
     * @param string $hashedValue hashed value
     * 
     * @return returns true if the password and hash match, or false otherwise.
     */
    public function verify($value, $hashedValue)
    {
        return $this->driver->verify($value, $hashedValue);
    }

    /**
     * Checks if the given hash matches the given options.
     *
     * @param string $hashedValue hashed value
     * @param array  $options     options
     * 
     * @return returns true if the hash should be rehashed
     * to match the given algo and options, or false otherwise. 
     */
    public function needsRehash($hashedValue, array $options = array())
    {
        return $this->driver->needsRehash($hashedValue, $options);
    }
    
    /**
     * Returns information about the given hash.
     * 
     * @param string $hash hash password
     * 
     * @return returns an associative array with three elements:
     */
    public function getInfo($hash)
    {
        return $this->driver->getInfo($hash);
    }
}


// END Bcrypt class

/* End of file Bcrypt.php */
/* Location: .Obullo/Crypt/Password/Bcrypt.php */
