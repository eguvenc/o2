<?php

namespace Obullo\ServiceProvider;

/**
 * Service Provider Helper
 * 
 * @category  Utilities
 * @package   Uri
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/ServiceProvider
 */
Class Utils
{
    /**
     * Returns to connection id
     * 
     * @param string $string serialized parameters
     * 
     * @return integer
     */
    public static function getConnectionId($string)
    {
        return sprintf("%u", crc32(serialize($string)));
    }

}

// END Utils class

/* End of file Utils.php */
/* Location: .Obullo/ServiceProvider/Utils.php */