<?php

namespace Obullo\Crypt\Password;

use RuntimeException;

/**
 * Password Bcrypt class.
 * 
 * @category  Password
 * @package   Bcrypt
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/bcrypt
 * @see       http://www.php.net/manual/en/ref.password.php
 */
Class NewVersion
{
    /**
     * Default crypt cost factor.
     *
     * @var int
     */
    protected $cost = 10;

    /**
     * Creates a password hash.
     * 
     * @param string $value   value
     * @param array  $options options
     * 
     * @example $2y$07$BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq
     * @see     http://php.net/manual/en/function.password-hash.php
     * @return  returns the hashed password, or false on failure.
     */
    public function hash($value, array $options = array())
    {
        $cost = isset($options['cost']) ? $options['cost'] : $this->cost;
        $hash = password_hash($value, PASSWORD_BCRYPT, array('cost' => $cost));
        if ($hash === false) {
            throw new RuntimeException('Password bcrypt hashing not supported.');
        }
        return $hash;
    }

    /**
     * Verifies that a password matches a hash.
     * 
     * @param string $value       value
     * @param string $hashedValue hashed value
     * 
     * @see    http://www.php.net/manual/en/function.password-verify.php
     * @return returns true if the password and hash match, or false otherwise.
     */
    public function verify($value, $hashedValue)
    {
        return password_verify($value, $hashedValue);
    }

    /**
     * Checks if the given hash matches the given options.
     *
     * @param string $hashedValue hashed value
     * @param array  $options     options
     * 
     * @see    http://www.php.net/manual/en/function.password-needs-rehash.php
     * @return returns true if the hash should be rehashed
     * to match the given algo and options, or false otherwise. 
     */
    public function needsRehash($hashedValue, array $options = array())
    {
        $cost = isset($options['cost']) ? $options['cost'] : $this->cost;
        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, array('cost' => $cost));
    }
    
    /**
     * Returns information about the given hash.
     * 
     * @param string $hash hash password
     * 
     * @see    http://www.php.net/manual/en/function.password-get-info.php
     * @return returns an associative array with three elements:
     */
    public function getInfo($hash)
    {
        return password_get_info($hash);
    }
}


// END Bcrypt class

/* End of file Password.php */
/* Location: .Obullo/Crypt/Password/Password.php */