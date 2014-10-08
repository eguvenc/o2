<?php

namespace Obullo\Utils;

/**
 * Generates Random String
 * 
 * @category  Utilities
 * @package   Random
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/utils
 */
Class Random
{
    /**
     * Container
     *
     * @var object
     */
    public $c;

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
    * Create a Random String
    *
    * Useful for generating passwords or hashes.
    *
    * @param string  $type type of random string. Options: alnum, alnum.upper, alnum.lower, numeric, nozero, unique
    * @param integer $len  number of characters
    * 
    * @return string
    */
    public function generate($type = 'alnum', $len = 8)
    {        
        switch($type) {
        case 'basic'    :
            return mt_rand();
          break;
        case 'alnum'    :
        case 'alnum.lower' :
        case 'alnum.upper' :
        case 'numeric'  :
        case 'nozero'   :
        case 'alpha'    :
        case 'alpha.lower' :
        case 'alpha.upper' :
            switch ($type) {
            case 'alpha'        : $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha.lower'  : $pool = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case 'alpha.upper'  : $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alnum'        : $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alnum.lower'  : $pool = '123456789abcdefghijklmnopqrstuvwxyz';
                break;
            case 'alnum.upper'  : $pool = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'numeric'      : $pool = '0123456789';
                break;
            case 'nozero'       : $pool = '123456789';
                break;
            }
            $str = '';
            for ($i=0; $i < $len; $i++) {
                $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
            }
            return $str;
          break;
        }
    }

}

// END Random Class
/* End of file Random.php

/* Location: .Obullo/Utils/Random.php */