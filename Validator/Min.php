<?php

namespace Obullo\Validator;

/**
 * Min Class
 * 
 * @category  Validator
 * @package   Min
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/validator
 */
Class Min
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
     * Minimum length
     * 
     * @param string $str string
     * @param string $val value
     * 
     * @return bool
     */    
    public function isValid($str, $val)
    {
        if (preg_match('/[^0-9]/', $val)) {
            return false;
        }
        return (mb_strlen($str) < $val) ? false : true;
    }
}

// END Min class
/* End of file Min.php */

/* Location: .Obullo/Validator/Min.php */