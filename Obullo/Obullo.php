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

/*
 * ------------------------------------------------------
 *  Before request event
 * ------------------------------------------------------
 */
$c['event']->fire('before.request', array($c));

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
 *  Before controller event
 * ------------------------------------------------------
 */
$c['event']->fire('before.controller', array($c));


require $controller;  // call the controller.  $app variable now Available in HERE !!


if ( ! in_array('index', array_keys($app->publicMethods))) {  // Check method exist or not
    $c->load('response')->show404($pageUri);
}
$arguments = array_slice($c->load('uri')->rsegments, 2);

if (array_key_exists('_remap', $app->controllerMethods)) {  // Is there a "remap" function? If so, we call it instead
    $app->_remap('index', $arguments);
} else {

    // Call the requested method. Any URI segments present (besides the directory / class / method) 
    // will be passed to the method for convenience
    // directory = 0, class = 1,  ( arguments = 2) ( @deprecated  method = 2 always = index )
    call_user_func_array(array($app, 'index'), $arguments);
}

/*
 * ------------------------------------------------------
 *  After controller event
 * ------------------------------------------------------
 */
$c['event']->fire('after.controller', array($c));

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
$c->load('response')->sendOutput();    // Send the final rendered output to the browser

/*
 * ------------------------------------------------------
 *  After controller event
 * ------------------------------------------------------
 */
$c['event']->fire('after.response', array($c, $start));


// END Obullo.php File
/* End of file Obullo.php

/* Location: .Obullo/Obullo/Obullo.php */