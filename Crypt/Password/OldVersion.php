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
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/bcrypt
 * @see       http://www.php.net/manual/en/ref.password.php
 */
Class OldVersion
{
    /**
     * Default crypt cost factor.
     *
     * @var int
     */
    protected $cost = 10;

    private $_identifier = '2y';  // Default identifier
    private $_validIdentifiers = array('2a', '2x', '2y'); // All valid hash identifiers

    /**
     * Constructor
     */
    public function __construct()
    {
        if ( ! function_exists('crypt')) {
            throw new RunTimeException('Crypt must be loaded for password_verify to function');
        }
    }

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
        $salt = $this->_generateSalt();
        $options = array();
        return crypt($value, $salt);
    }

    /**
     * Generates the salt string
     *
     * @return string
     */
    private function _generateSalt()
    {
        if ($this->cost < 4 || $this->cost > 31) { // do not increase the work factor                                        // this may cause performance problems.
            $this->cost = $this->_workFactor;
        }
        $input = $this->_getRandomBytes();
        $salt = '$' . $this->_identifier . '$';
        $salt .= str_pad($this->cost, 2, '0', STR_PAD_LEFT);
        $salt .= '$';
        $salt .= substr(strtr(base64_encode($input), '+', '.'), 0, 22);
        return $salt;
    }

    /**
     * OpenSSL's random generator
     *
     * @return string
     */
    private function _getRandomBytes()
    {
        if ( ! function_exists('openssl_random_pseudo_bytes')) {
            throw new RunTimeException('Unsupported hash format.');
        }
        return openssl_random_pseudo_bytes(16);
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
        $this->_validateIdentifier($hashedValue);
        $checkHash = crypt($value, $hashedValue);
        return ($checkHash === $hashedValue);
    }

    /**
     * Validate identifier
     *
     * @param string $hash hash
     * 
     * @return void
     */
    private function _validateIdentifier($hash)
    {
        if ( ! in_array(substr($hash, 1, 2), $this->_validIdentifiers)) {
            throw new RunTimeException('Unsupported hash format.');
        }
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
        // $cost = isset($options['cost']) ? $options['cost'] : $this->cost;
        // return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, array('cost' => $cost));
        return false;
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
        $hash = null;
        return;
    }
}


// END OldPassword class

/* End of file OldPassword.php */
/* Location: .Obullo/Crytpt/Password/OldPassword.php */