<?php

namespace Obullo\Http;

/**
 * Sanitize $_REQUEST, $_POST, $_SERVER, $_GET, $_COOKIE variables
 * 
 * @category  Http
 * @package   Sanitizer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/Sanitizer
 */
Class Sanitizer
{
    /**
     * Sanitize Input Data
     *
     * This is a helper function. It escapes data and
     * standardizes newline characters to \n
     *
     * @param string $str input
     * 
     * @return   string
     */
    public function sanitize($str)
    {
        if (is_array($str)) {
            $newArray = array();
            foreach ($str as $key => $val) {
                $newArray[static::sanitizeKeys($key)] = static::sanitize($val);
            }
            return $newArray;
        }
        $str = static::sanitizeInvisibleCharacters($str); // Remove control characters
        return $str;
    }

    /**
     * Clean Keys
     *
     * This is a helper function. To prevent malicious users
     * from trying to exploit keys we make sure that keys are
     * only named with alpha-numeric text and a few other items.
     *
     * @param string $str input 
     * 
     * @return string
     */
    public static function sanitizeKeys($str)
    {
        if ( ! preg_match("/^[a-z0-9:_\/-]+$/i", $str)) {
            die('Malicious Key Characters.');
        }
        return $str;
    }

    /**
     * Remove Invisible Characters
     *
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @param string  $str        text
     * @param boolean $urlEncoded encode option
     * 
     * @return string
     */
    public static function sanitizeInvisibleCharacters($str, $urlEncoded = true)
    {
        $nonDisplayables = array();  // every control character except newline (dec 10)
        if ($urlEncoded) {           // carriage return (dec 13), and horizontal tab (dec 09)
            $nonDisplayables[] = '/%0[0-8bcef]/';  // url encoded 00-08, 11, 12, 14, 15
            $nonDisplayables[] = '/%1[0-9a-f]/';   // url encoded 16-31
        }
        $nonDisplayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';   // 00-08, 11, 12, 14-31, 127
        do {
            $str = preg_replace($nonDisplayables, '', $str, -1, $count);
        } while ($count);
        return $str;
    }

}

// END Sanitizer.php File
/* End of file Sanitizer.php

/* Location: .Obullo/Http/Sanitizer.php */