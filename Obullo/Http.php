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

$class 	= new $className;  // Call the controller
$method = $router->fetchMethod();

$filter = false;
if ($c['config']['controller']['annotation']['reader']) {
    $docs = new Obullo\Annotations\Reader\Controller($c, $class, $method);
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
if ( ! method_exists($class, $router->fetchMethod()) OR $router->fetchMethod() == 'load') { // load method reserved
    $response->show404($pageUri);
}
$arguments = array_slice($c['uri']->rsegments, 3);

/**
 *  Call the requested method. Any URI segments present 
 *  (besides the directory / class / method)  will be passed to the method for convenience
 *  directory = 0, class = 1,  arguments = 2 , method = 3
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