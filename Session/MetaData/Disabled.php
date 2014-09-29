<?php

namespace Obullo\Session\MetaData;

/**
 * MetaData Disabled Class
 * 
 * @category  Session
 * @package   MetaData
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/session
 */
Class Disabled
{
    /**
     * If metadata disabled all logger
     * methods returns to array.
     * 
     * @param string $method    name
     * @param array  $arguments array
     * 
     * @return null
     */
    public function __call($method, $arguments)
    {
        $method    = null;
        $arguments = array();
        return false;
    }

    /**
     * Disabled isValid function
     * 
     * @return boolean true
     */
    public function isValid()
    {
        return true;
    }
}

// END Disabled.php File
/* End of file Disabled.php

/* Location: .Obullo/Session/MetaData/Disabled.php */