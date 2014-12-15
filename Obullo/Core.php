<?php

/**
 * Obullo Core File
 * 
 * @category  Core
 * @package   Obullo
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/obullo
 */
/*
|--------------------------------------------------------------------------
| Dependency Container
|--------------------------------------------------------------------------
*/
$c = new Obullo\Container\Container;
/*
|--------------------------------------------------------------------------
| Config File
|--------------------------------------------------------------------------
*/
require OBULLO_CONFIG;
/*
|--------------------------------------------------------------------------
| App Component
|--------------------------------------------------------------------------
*/
$c['app'] = function () use ($c) {
    return new Obullo\App\App($c);
};
/*
|--------------------------------------------------------------------------
| Detect current environment
|--------------------------------------------------------------------------
*/
define('ENV', $c['app']->detectEnvironment());
define('ENV_PATH', APP .'config'. DS . 'env'. DS . ENV . DS);

/*
|--------------------------------------------------------------------------
| Core Functions
|--------------------------------------------------------------------------
*/

/**
 * Gets environment variable from $_ENV global
 * 
 * @param string $var key
 * 
 * @return string value
 */
function envget($var)
{
    if (empty($_ENV[$var])) {
        die('<b>Configuration error: </b>'.$var.' key not found or value is empty in .env.'.ENV.'.php file array.');
    }
    return $_ENV[$var];
}

/**
 * Include environment config file
 * 
 * @param string $file name
 * 
 * @return array
 */
function envfile($file)
{
    $return = include ENV_PATH .$file;
    if ($return == false) {
        configurationError();
    }
    return $return;
}

/**
 * Startup Configuration Error
 * 
 * @param string $errorStr optional message
 * 
 * @return void exit
 */
function configurationError($errorStr = null)
{
    $error = error_get_last();
    $message = (is_null($errorStr)) ? $error['message'] : $errorStr;
    die('<b>Configuration error:</b> '.$message. ' line: '.$error['line']);
}
/*
|--------------------------------------------------------------------------
| Config Component
|--------------------------------------------------------------------------
*/
$c['config'] = function () {
    return new Obullo\Config\Config;
};
/*
|--------------------------------------------------------------------------
| Default Disable All Errors
|--------------------------------------------------------------------------
| Also disable console errors. If debug enabled we release the errors.
| Error reporting only can manage from your main config.php file you
| shouldn't use error_reporting() in your index.php file.
*/
error_reporting(0);
/*
|--------------------------------------------------------------------------
| Allows Php Native Errors
|--------------------------------------------------------------------------
| This feature is configurable from your main config.php file.
*/
if ($c['config']['error']['reporting']) {
    error_reporting(E_ALL | E_STRICT | E_NOTICE);
    ini_set('display_errors', 1);
}
/*
|--------------------------------------------------------------------------
| Set Default Time Zone Identifer. @link http://www.php.net/manual/en/timezones.php
|--------------------------------------------------------------------------                                        
| Set the default timezone identifier for date function ( Server Time ).
|
*/
date_default_timezone_set($c['config']['locale']['date']['php_date_default_timezone']);
/*
|--------------------------------------------------------------------------
| If Debug Enabled Register Application Error Handlers
|--------------------------------------------------------------------------
| If framework debug feature enabled we register error & exception handlers.
*/
if ($c['config']['error']['debug'] AND $c['config']['error']['reporting'] == false) {
    Obullo\Error\Debug::enable(E_ALL | E_NOTICE | E_STRICT);
}

// END Core.php File
/* End of file Core.php

/* Location: .Obullo/Obullo/Core.php */