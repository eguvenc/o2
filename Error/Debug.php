<?php

namespace Obullo\Error;

use Obullo\Error\ErrorHandler,
    Obullo\Error\ExceptionHandler;

/**
 * Error Debug Cass
 * Modeled after Symfony Debug package.
 * 
 * @category  Error
 * @package   Debug
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/error
 */
Class Debug
{
    /**
     * Enable disable debugging
     * 
     * @var boolean
     */
    protected static $enabled = false;

    /**
     * Enable debugging & On / Off display errors
     * 
     * @param string  $level         error reporting level
     * @param boolean $displayErrors set php.ini display errors
     * 
     * @return void
     */
    public static function enable($level = null, $displayErrors = true)
    {    
        if (static::$enabled) {
            return;
        }
        static::$enabled = true;
        error_reporting(-1); // Report all PHP errors
        if (defined('STDIN') AND $displayErrors) {
            ini_set('display_errors', 1);
            ini_set('error_reporting', 1); // Enables cli errors on console mode
        }
        ErrorHandler::register($level, $displayErrors);
        ExceptionHandler::register();

        if ($displayErrors AND ( ! ini_get('log_errors') OR ini_get('error_log'))) {
            ini_set('display_errors', 1);
            ini_set('error_reporting', 1); // Enables cli errors on console mode
        }
    }
}

// END Error Debug class

/* End of file Debug.php */
/* Location: .Obullo/Error/Debug.php */