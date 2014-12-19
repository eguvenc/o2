<?php

namespace Obullo\Validator;

/**
 * Matches Class
 * 
 * @category  Validator
 * @package   Matches
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/validator
 */
Class Matches
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
     * Match one field to another
     * 
     * @param string $str   string
     * @param string $field field
     * 
     * @return bool
     */    
    public function isValid($str, $field)
    {   
        if ( ! isset($_REQUEST[$field])) {
            return false;                
        }
        return ($str !== $_REQUEST[$field]) ? false : true;
    }
}

// END Matches class
/* End of file EMatches.php */

/* Location: .Obullo/Validator/Matches.php */