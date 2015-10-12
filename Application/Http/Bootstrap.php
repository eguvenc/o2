<?php

use Obullo\Container\Loader;
use Obullo\Container\Container;
use Obullo\Container\ContainerInterface;

use Obullo\Config\Config;
use Obullo\Application\Http;
use Obullo\Http\ServerRequestFactory;
use Obullo\Http\Response;

/**
 * Detect environment
 * 
 * @return array
 */
$detectEnvironment = function () {
    static $env = null;
    if ($env != null) {
        return $env;
    }
    $hostname = gethostname();
    $envArray = include ROOT .'app/environments.php';
    foreach (array_keys($envArray) as $current) {
        if (in_array($hostname, $envArray[$current])) {
            $env = $current;
            break;
        }
    }
    if ($env == null) {
        die('We could not detect your application environment, please correct your app/environments.php hostnames.');
    }
    return $env;
};
$env = $detectEnvironment();

/**
 * Container
 * 
 * @var object
 */
$c = new Container(new Loader("app/".$env."/service", LOADER)); // Bind services to container

/**
 * Include application
 */
require OBULLO .'Application/Http.php';

/**
 * Config
 */
$c['config'] = function () use ($c, $env) {
    return new Config($c, $env);
};

/**
 * Application
 */
$c['app'] = function () use ($c, $env) {
    return new Http($c, $env);
};

require APP .'components.php';