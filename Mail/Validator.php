<?php

namespace Obullo\Mail;

/**
 * Validator Class
 * 
 * @category  Validator
 * @package   Mailer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/mailer
 */
class Validator
{
    /**
     * Error string
     * 
     * @var string
     */
    public $error = null;

    /**
     * Error subject
     *
     * @var string
     */
    public $value = null;

    /**
     * Validate Email Address
     * 
     * @param string $email email
     * 
     * @return bool
     */
    public function validateEmail($email)
    {
        if (! is_array($email)) {
            $this->setError('OBULLO:MAILER:MUST_BE_ARRAY');
            return false;
        }
        foreach ($email as $val) {
            if (! $this->validEmail($val)) {
                $this->setError('OBULLO:MAILER:INVALID_ADDRESS', $val);
                return false;
            }
        }
        return true;
    }

    /**
     * Email Validation
     *
     * @param string $address $email
     * 
     * @return bool
     */
    public function validEmail($address)
    {
        return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? false : true;
    }

    /**
     * Check we have an error
     * 
     * @return boolean
     */
    public function isError()
    {
        if (empty($this->error)) {
            return false;
        }
        return true;
    }

    /**
     * Set validator error
     * 
     * @param string $key error key
     * @param string $val error value
     *
     * @return void
     */
    public function setError($key, $val)
    {
        $this->error = $key;
        $this->value = $val;
    }

    /**
     * Get latest error
     * 
     * @return string error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get error value
     * 
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}