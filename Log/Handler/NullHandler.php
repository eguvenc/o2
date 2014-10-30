<?php

namespace Obullo\Log\Handler;

/**
 * Disable Logger Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class NullHandler
{
    /**
     * If logger disabled all logger methods returns to null.
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

        return null;
    }
}

// END NullHandler Class

/* End of file NullHandler.php */
/* Location: .Obullo/Log/Handler/NullHandler.php */