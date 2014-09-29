<?php

namespace Obullo\Validator;

/**
 * Max Class
 * 
 * @category  Validator
 * @package   Max
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/validator
 */
Class Max
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
        return (mb_strlen($str) > $val) ? false : true;   
    }
}

// END Max class
/* End of file Max.php */

/* Location: .Obullo/Validator/Max.php */