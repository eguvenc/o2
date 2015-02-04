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
/*
 * ------------------------------------------------------
 *  Application before filters
 * -----------------------------------------------------
 */
$c['app']->initFilters('before');
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

$annotationFilter = false;
if ($c['config']['annotation']['controller']) {
    $docs = new Obullo\Annotations\Reader\Controller($c, $class, $method);
    $annotationFilter = $docs->parse();
}
/*
 * ------------------------------------------------------
 *  Before controller filters
 * ------------------------------------------------------
 */
$router->initFilters('before', $annotationFilter);  // Initialize ( exec ) registered router ( before ) filters

if (method_exists($class, 'load')) {
    $class->load();
}
/*
 * ------------------------------------------------------
 *  After controller load method
 * ------------------------------------------------------
 */
$router->initFilters('load', $annotationFilter); 
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
 *  After controller filters
 * ------------------------------------------------------
 */
$router->initFilters('after', $annotationFilter);
/*
 * ------------------------------------------------------
 *  Application after filters
 * ------------------------------------------------------
 */
$c['app']->initFilters('after');
/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
$response->output();    // Send ( print ) the final rendered output to the browser
/*
 * ------------------------------------------------------
 *  Finish controller filters
 * ------------------------------------------------------
 */
$router->initFilters('finish', $annotationFilter);
/*
 * ------------------------------------------------------
 *  Application finish filters
 * ------------------------------------------------------
 */
$c['app']->initFilters('finish');


// END Obullo.php File
/* End of file Obullo.php

/* Location: .Obullo/Obullo.php */