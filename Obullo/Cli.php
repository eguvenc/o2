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

$_SERVER['REQUEST_TIME_START'] = microtime(true);
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
$router     = $c['router'];
$response   = $c['response'];
$pageUri    = "{$router->fetchDirectory()} / {$router->fetchClass()} / {$router->fetchMethod()}";
$controller = CONTROLLERS . $router->fetchModule(DS).$router->fetchDirectory(). DS .$router->fetchClass(). '.php';

require $controller;  // Include the controller file.

$className = '\\'.$router->fetchNamespace().'\\'.$router->fetchClass();

if ( ! class_exists($className, false)) {  // Check method exist or not
    $response->show404($pageUri);
}

$class = new $className;  // Call the controller

$filter = false;
if ($c['config']['controller']['annotation']['reader']) {
    $docs = new Obullo\Annotations\Reader\Controller($c, $class);
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

/*
 * ------------------------------------------------------
 *  Dispatcher
 * ------------------------------------------------------
 */
$argumentSlice = 3;
if ( ! method_exists($class, $router->fetchMethod()) OR $router->fetchMethod() == 'load') { // load method reserved
    $argumentSlice = 2;
    $router->setMethod('index');    // If we have index method run it in cli mode. This feature enables task functionality.
}
$arguments = array_slice($c['uri']->rsegments, $argumentSlice);

/**
 * ------------------------------------------------------
 *  Call the requested method. Any URI segments present (besides the directory / class / method)  will be passed to the method for convenience
 *  directory = 0, class = 1,  arguments = 2 (  method always = index )
 *  ------------------------------------------------------
 */
call_user_func_array(array($class, $router->fetchMethod()), $arguments);

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
$c['event']->fire('after.response');


// END Obullo.php File
/* End of file Obullo.php

/* Location: .Obullo/Obullo.php */