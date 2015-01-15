<?php

/**
 * Obullo Core File
 * 
 * @category  Core
 * @package   Obullo
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
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
| Application
|--------------------------------------------------------------------------
*/
$c['app'] = function () use ($c) {
    return new Obullo\Application\Application($c);
};
/*
|--------------------------------------------------------------------------
| Detect current environment
|--------------------------------------------------------------------------
*/
$c['app']->detectEnvironment();
/*
|--------------------------------------------------------------------------
| Build environment constants
|--------------------------------------------------------------------------
*/
define('ENV', $c['app']->getEnv());
define('ENV_PATH', APP .'config'. DS . ENV . DS);
/*
|--------------------------------------------------------------------------
| Environment Component
|--------------------------------------------------------------------------
*/
$c['env'] = function () {
    return new Obullo\Config\Env;
};
/*
|--------------------------------------------------------------------------
| Config Component
|--------------------------------------------------------------------------
*/
$c['config'] = function () use ($c) {
    return new Obullo\Config\Config($c);
};

function env($key, $default = '', $required = false)
    {
        $empty = empty($_ENV[$key]);
        if ($required AND $empty) {
            die('<b>Configuration error: </b>'.$key.' key not found or value is empty in .env.'.ENV.'.php file array.');
        }
        if ($empty AND $default != '') {     // default value
            return $default;
        }
        if ( ! isset($_ENV[$key])) {
            die('<b>Configuration error: </b>'.$key.' key not found in .env.'.ENV.'.php file array.');
        }
        return $_ENV[$key];
    }

/*
|--------------------------------------------------------------------------
| Disable / Ebable Php Native Errors
|--------------------------------------------------------------------------
| This feature is configurable from your main config.php file.
*/
if ($c['config']['error']['reporting']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL | E_STRICT | E_NOTICE);
} else {
    error_reporting(0);
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