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
define('ENV_CONFIG', APP .'config'. DS . 'env'. DS . ENV . DS);
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