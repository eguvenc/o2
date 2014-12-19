<?php

namespace Obullo\Validator;

/**
 * Required Class
 * 
 * @category  Validator
 * @package   Required
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/validator
 */
Class Required
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
     * Empty or not
     * 
     * @param string $val value
     * 
     * @return bool
     */    
    public function isValid($val)
    {
        return (empty($val) &&  $val != 0) ? false : true;
    }
}

// END Required class
/* End of file Required.php */

/* Location: .Obullo/Validator/Required.php */