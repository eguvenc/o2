<?php

namespace Obullo\Error;

use Obullo\Error\ErrorHandler;
use Obullo\Error\ExceptionHandler;

/**
 * Error Debug Cass ( Modeled after Symfony Debug package  )
 * 
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Debug
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
        if (defined('STDIN') && $displayErrors) {
            ini_set('display_errors', 1);
            ini_set('error_reporting', 1); // Enables cli errors on console mode
        }
        ErrorHandler::register($level, $displayErrors);
        ExceptionHandler::register();

        if ($displayErrors && ( ! ini_get('log_errors') || ini_get('error_log'))) {
            ini_set('display_errors', 1);
            ini_set('error_reporting', 1); // Enables cli errors on console mode
        }
    }
}