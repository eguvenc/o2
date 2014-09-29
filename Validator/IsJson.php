<?php

namespace Obullo\Validator;

/**
 * IsJson Class
 * 
 * @category  Validator
 * @package   IsJson
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/validator
 */
Class IsJson
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
     * is Json
     * 
     * @param string $str string
     * 
     * @return bool
     */    
    public function isValid($str)
    {
        return ( ! is_object(json_decode($str))) ? false : true;
    }
}

// END IsJson Class
/* End of file IsJson.php */

/* Location: .Obullo/Validator/IsJson.php */
