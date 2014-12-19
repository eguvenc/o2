<?php

namespace Obullo\Validator;

/**
 * Exact Class
 * 
 * @category  Validator
 * @package   Exact
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/validator
 */
Class Exact
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
     * Exact length
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
        return (mb_strlen($str) != $val) ? false : true;   
    }
}

// END Exact class
/* End of file Exact.php */

/* Location: .Obullo/Validator/Exact.php */