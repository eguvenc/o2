<?php

namespace Obullo\Permissions\Rbac;

/**
 * Utils Helper
 * 
 * @category  Permissions
 * @package   Utils
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/rbac
 */
Class Utils
{
    /**
     * Hash
     * 
     * @param mixed $data hash data
     * 
     * @return string
     */
    public static function hash($data)
    {
        return hash('adler32', json_encode($data));
    }

    /**
     * Build array
     * 
     * @param mix $data data
     * 
     * @return array
     */
    public static function arrayConvert($data)
    {
        return (! is_array($data)) ? array($data) : $data;
    }

    /**
     * Double array convert
     * 
     * @param mix    $data data
     * @param string $key  key
     * 
     * @return array
     */
    public static function doubleArrayConvert($data, $key)
    {
        return (! is_array($data)) ? array(array($key => $data)) : $data;
    }
}


// END Utils.php File
/* End of file Utils.php

/* Location: .Obullo/Permissions/Rbac/Utils.php */
