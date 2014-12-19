<?php

namespace Obullo\Validator;

/**
 * IsBool Class
 * 
 * @category  Validator
 * @package   IsBool
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/validator
 */
Class IsBool
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
     * Is Boolean
     * 
     * @param string $str string
     * 
     * @return bool
     */    
    public function isValid($str)
    {
        return ( is_bool($str) || $str == 0 || $str == 1 ) ? true : false;
    }
}

// END IsBool Class
/* End of file IsBool.php */

/* Location: .Obullo/Validator/IsBool.php */