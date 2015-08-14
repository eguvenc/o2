<?php

namespace Obullo\Mail;

/**
 * Mailer Helper
 *
 * @category  Mailer
 * @package   Transport
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mailer
 */
class Utils
{
    /**
     * Convert a String to an Array
     *
     * @param mixed $email email data
     * 
     * @return array
     */
    public static function strToArray($email)
    {
        if (! is_array($email)) {
            if (strpos($email, ',') !== false) {
                $email = preg_split('/[\s,]/', $email, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $email = trim($email);
                settype($email, "array");
            }
        }
        return $email;
    }

    /**
     * Clean Extended Email Address: Joe Smith <joe@smith.com>
     *
     * @param string $email address
     * 
     * @return string
     */
    public static function formatEmail($email)
    {
        if (! is_array($email)) {
            if (strpos($email, '>') > 0 && preg_match('/(?<name>.*?)\<(?<email>.*)\>/', $email, $match)) {
                return array('email' => $match['email'], 'name' => $match['name']);
            } else {
                return array('email' => $email, 'name' => null);
            }
        }
        $formatted = array();
        foreach ($email as $address) {
            if (strpos($address, '>') > 0 && preg_match('/(?<name>.*?)\<(?<email>.*)\>/', $address, $match)) {
                $formatted[] = array('email' => $match['email'], 'name' => $match['name']);
            } else {
                $formatted[] = array('email' => $address, 'name' => null);
            }
        }
        return $formatted;
    }

    /**
     * Clean Extended Email Address: Joe Smith <joe@smith.com>
     *
     * @param string $email address
     * 
     * @return string
     */
    public static function cleanEmail($email)
    {
        if (! is_array($email)) {
            if (preg_match('/\<(.*)\>/', $email, $match)) {
                return $match['1'];
            } else {
                return $email;
            }
        }
        $cleanEmail = array();
        foreach ($email as $addy) {
            if (preg_match('/\<(.*)\>/', $addy, $match)) {
                $cleanEmail[] = $match['1'];
            } else {
                $cleanEmail[] = $addy;
            }
        }
        return $cleanEmail;
    }

}