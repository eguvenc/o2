<?php

namespace Obullo\Validator;

/**
 * Alpha Class
 * 
 * @category  Validator
 * @package   Validator
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/validator
 */
class Alpha
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
     * Alpha
     * 
     * @param string $str  string
     * @param string $lang "L" for all, or "Latin", "Arabic", "Old_Turkic" 
     * 
     * @see http://php.net/manual/en/regexp.reference.unicode.php
     *
     * @return bool
     */         
    public function isValid($str, $lang)
    {
        if (empty($lang)) {
            $lang = 'L';    // auto
        }
        if (defined('PCRE_VERSION') AND intval(PCRE_VERSION) < 7) {
            $this->c['logger']->notice('Unicode support disabled your PCRE_VERSION must be >= 7.');
            return ( ! preg_match("/^([-a-z0-9_\-])+$/i", $str)) ? false : true;
        }
        return ( ! preg_match('/^[\p{'.$lang.'}_\-\d]+$/u', $str)) ? false : true;
    }
}

// END Alpha class
/* End of file Alpha.php */

/* Location: .Obullo/Validator/Alpha.php */