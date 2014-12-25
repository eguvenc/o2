<?php

/**
 * Obullo
 * 
 * @category  Core
 * @package   Obullo
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/obullo
 */

$start = microtime(true);  // Run Timer
/*
 * ------------------------------------------------------
 *  Before request event
 * ------------------------------------------------------
 */
$c['event']->fire('before.request');

/*
 * ------------------------------------------------------
 *  Load core components
 * ------------------------------------------------------
 */
$router 	= $c->load('router');
$response 	= $c->load('response');
$pageUri    = "{$router->fetchDirectory()} / {$router->fetchClass()} / index";
$controller = PUBLIC_DIR . $router->fetchTopDirectory(DS). $router->fetchDirectory() . DS .'controller'. DS . $router->fetchClass() . '.php';

if ( ! file_exists($controller)) {
    $response->show404($pageUri);
}
require $controller;  // Include the controller file.

$className = '\\'.$router->fetchNamespace().'\\'.$router->fetchClass();

if ( ! class_exists($className, false)) {  // Check method exist or not
    $response->show404($pageUri);
}

$class = new $className;  // Call the controller

$filter = false;
if ($c['config']['controller']['annotation']['reader']) {
    $docs = new Obullo\Blocks\Annotations\Reader\Controller($c, $class);
    $filter = $docs->parse();
}

/*
 * ------------------------------------------------------
 *  Before controller event
 * ------------------------------------------------------
 */
$c['event']->fire('before.controller', array($class, $filter));

if (method_exists($class, 'load')) {
    $class->load();
}
/*
 * ------------------------------------------------------
 *  After controller load method
 * ------------------------------------------------------
 */
$c['event']->fire('on.load', array($class, $filter));

foreach (get_class_methods($class) as $method) {
    if ($method != 'index' AND $method != 'load' AND strpos($method, '_') !== 0) {
        throw new RunTimeException(
            'Just one public method allowed because of Obullo has a principle "One Index Method Per Controller".
            If you want to use private methods try to add underscore prefix ( _methodname ). e.g. <pre>private function _methodname() {}</pre>'
        );
    }
}
if ( ! method_exists($class, 'index')) {  // Check method exist or not
    $response->show404($pageUri);
}
$arguments = array_slice($c['uri']->rsegments, 2);

// Call the requested method. Any URI segments present (besides the directory / class / method) 
// will be passed to the method for convenience
// directory = 0, class = 1,  arguments = 2 (  method always = index )
call_user_func_array(array($class, 'index'), $arguments);

/*
 * ------------------------------------------------------
 *  After controller event
 * ------------------------------------------------------
 */
$c['event']->fire('after.controller', array($class, $filter));


/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
$response->sendOutput();    // Send the final rendered output to the browser

/*
 * ------------------------------------------------------
 *  After controller event
 * ------------------------------------------------------
 */
$c['event']->fire('after.response', array($start));


// END Obullo.php File
/* End of file Obullo.php

/* Location: .Obullo/Obullo.php */