<?php

namespace Obullo\Log\Handler;

/**
 * Disable Handler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class DisabledHandler
{
    /**
     * If logger disabled all logger
     * methods returns to null.
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
}

// END DisabledHandler Class

/* End of file Disabled.php */
/* Location: .Obullo/Log/Handler/DisabledHandler.php */