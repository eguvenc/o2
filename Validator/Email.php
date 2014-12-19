<?php

namespace Obullo\Validator;

/**
 * Valid Email Class
 * 
 * @category  Validator
 * @package   ValidEmail
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/validator
 */
Class Email
{
    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Valid Email
     *
     * @param string  $str email
     * @param boolean $dns dns check
     * 
     * @return bool
     */    
    public function isValid($str, $dns = false)
    {
        $isValid = ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? false : true;
        if ($isValid AND $dns) {
            $username = null;
            $domain   = null;
            list($username, $domain) = explode('@', $str);
            if ( ! checkdnsrr($domain, 'MX')) {
                return false;
            }
            return true;
        }
        return $isValid;
    }
}

// END Email class
/* End of file Email.php */

/* Location: .Obullo/Validator/Email.php */