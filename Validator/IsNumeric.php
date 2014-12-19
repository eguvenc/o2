<?php

namespace Obullo\Validator;

/**
 * IsNumeric Class
 * 
 * @category  Validator
 * @package   IsNumeric
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/validator
 */
Class IsNumeric
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
     * 
     * @return bool
     */    
    public function isValid($str)
    {
        return ( ! is_numeric($str)) ? false : true;
    }
}

// END IsNumeric Class
/* End of file IsNumeric.php */

/* Location: .Obullo/Validator/IsNumeric.php */