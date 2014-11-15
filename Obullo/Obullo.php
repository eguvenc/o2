<?php

/**
 * Obullo
 * 
 * @category  Core
 * @package   Obullo
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/obullo
 */
$start = microtime(true);  // Run Timer
$hooks_enabled = false;
/*
|--------------------------------------------------------------------------
| Instantiate the hooks class
|--------------------------------------------------------------------------
*/
// if ($hooks_enabled) {
//     $c->load('hooks')->call('pre_system');
//     // @todo
//     // $['event']->register('before.request');
// }
/*
|--------------------------------------------------------------------------
| Sanitize inputs
|--------------------------------------------------------------------------
*/
$logger = $c->load('service/logger');

if ($c->load('config')['uri']['queryStrings'] == false) {  // Is $_GET data allowed ? If not we'll set the $_GET to an empty array
    $_GET = array(); // @todo turn it to filter when('post' function() { }) 
}
$_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']); // Sanitize PHP_SELF

// Clean $_COOKIE Data
// Also get rid of specially treated cookies that might be set by a server
// or silly application, that are of no use to application anyway
// but that when present will trip our 'Disallowed Key Characters' alarm
// http://www.ietf.org/rfc/rfc2109.txt
// note that the key names below are single quoted strings, and are not PHP variables
unset(
    $_COOKIE['$Version'], 
    $_COOKIE['$Path'], 
    $_COOKIE['$Domain']
);
/*
 * ------------------------------------------------------
 *  Log requests
 * ------------------------------------------------------
 */
$logger->debug('$_REQUEST_URI: ' . $c->load('uri')->getRequestUri(), array(), 10);
$logger->debug('$_COOKIE: ', $_COOKIE, 9);
$logger->debug('$_POST: ', $_POST, 9);
$logger->debug('$_GET: ', $_GET, 9);
$logger->debug('Global POST and COOKIE data sanitized', array(), 10);
/*
 * ------------------------------------------------------
 *  Load core components
 * ------------------------------------------------------
 */
$pageUri    = "{$c->load('router')->fetchDirectory()} / {$c->load('router')->fetchClass()} / index";
$controller = PUBLIC_DIR . $c->load('router')->fetchTopDirectory(DS). $c->load('router')->fetchDirectory() . DS .'controller'. DS . $c->load('router')->fetchClass() . EXT;

if ( ! file_exists($controller)) {
    $c->load('response')->show404($pageUri);
}
/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
// if ($hooks_enabled) {
//     $c->load('hooks')->call('pre_controller');
// }
// $c->load('event')->fire('before.controller');

require $controller;  // call the controller.  $app variable now Available in HERE !!

/*
 * ------------------------------------------------------
 *  Is there a "post_controller_constructor" hook?
 * ------------------------------------------------------
 */
// if ($hooks_enabled) {
//     $c->load('hooks')->call('post_controller_constructor');
// }
// $['event']->fire('before.response');

if ( ! in_array('index', array_keys($app->publicMethods))) {  // Check method exist or not
    $c->load('response')->show404($pageUri);
}
$arguments = array_slice($c->load('uri')->rsegments, 2);

if (array_key_exists('_remap', $app->controllerMethods)) {  // Is there a "remap" function? If so, we call it instead
    $app->_remap('index', $arguments);
} else {

    // Call the requested method. Any URI segments present (besides the directory / class / method) 
    // will be passed to the method for convenience
    // directory = 0, class = 1,  ( arguments = 2) ( @deprecated  method = 2 method always = index )
    call_user_func_array(array($app, 'index'), $arguments);
}
/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 * ------------------------------------------------------
 */
// if ($hooks_enabled) {
//     $c->load('hooks')->call('post_controller');
// }
/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
if ($hooks_enabled) {
    if ($c->load('hooks')->call('display_override') === false) {
        $c->load('response')->sendOutput();  // Send the final rendered output to the browser
    }
} else {
    $c->load('response')->sendOutput();    // Send the final rendered output to the browser
}
/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 * ------------------------------------------------------
 */
// if ($hooks_enabled) {
//     $c->load('hooks')->call('post_system');
// }
// $['event']->register('after.response');

$end = microtime(true) - $start;  // End Timer

$extra = array();
if ($c->load('config')['log']['extra']['benchmark']) {     // Do we need to generate benchmark data ? If so, enable and run it.
    $usage = 'memory_get_usage() function not found on your php configuration.';
    if (function_exists('memory_get_usage') AND ($usage = memory_get_usage()) != '') {
        $usage = round($usage/1024/1024, 2). ' MB';
    }
    $extra = array('time' => number_format($end, 4), 'memory' => $usage);
}
$logger->debug('Final output sent to browser', $extra, -99);

// $['event']->fire('shutdown');

// END Obullo.php File
/* End of file Obullo.php

/* Location: .Obullo/Obullo/Obullo.php */