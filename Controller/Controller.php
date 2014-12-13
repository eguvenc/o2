<?php

/**
 * Controller class.
 * 
 * @category  Controller
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/controller
 */
Class Controller
{
    public static $instance;                // Controller instance
    public $publicMethods     = array();    // Controller user defined methods. ( @public )
    public $controllerMethods = array();    // Controller user defined methods starts wiht "_" underscore. ( @private )
    public $c, $config, $uri, $router, $logger; // Default packages

    /**
     * Closure function for 
     * construction
     * 
     * @param null $closure object or null
     */
    public function __construct($closure = null)
    {
        global $c;
        $this->c = $c;

        self::$instance = &$this;
        $logger = $c->load('service/logger');  // Assign Default Loaded Packages
                                               // NOTICE:
        $this->config = &$c['config'];         // If we don't use assign by reference this will cause some errors in "Lvc Pattern".
        $this->uri    = &$c['uri'];            // The bug is insteresting, when we work with multiple page not found requests
        $this->router = &$c['router'];         // The objects of Controller keep the last instances of the last request.
        $this->logger = &$logger;              // that means the instance don't do the reset.

        $this->router->initFilters('before');  // Run router before filters

                                               // Keep in your mind we need use pass by reference in some situations.
                                               // @see http://www.php.net/manual/en/language.references.whatdo.php
        // if ($closure != null) {  // Run Construct Method
        //     $closure = Closure::bind($closure, $this, get_class());
        //     $closure($c);
        // }
        foreach ($c->unRegisteredKeys() as $key) {  // On router level some classes does not assigned into controller instance
            if ( ! isset($this->{$key})) {          // forexample view and session class, we need to assign them if they not registered.
                $this->{$key} = &$c[$key];          // Register to controller instance
            }
        };
    }
    
    /**
     * We prevent to set custom variables
     *
     * Forexample this is not allowed $this->user_variable = 'hello'
     * in controller
     * 
     * @param string $key string
     * @param string $val mixed
     *
     * @return void 
     */
    public function __set($key, $val)  // Custom variables is not allowed !!! 
    {
        if ( ! is_object($val)) {
            throw new RunTimeException('Just object type variables allowed in controller.');
        }
        $this->{$key} = $val; // store only app classes & packages 
                              // and object types
    }

    /**
     * Create the controller methods.
     * 
     * @param string  $methodName     method
     * @param closure $methodCallable callable function
     * 
     * @return void
     */
    // public function func($methodName, $methodCallable)
    // {
    //     if (strncmp($methodName, '_', 1) !== 0 AND strpos($methodName, 'callback_') !== 0) {  // ** "One Public Method Per Controller" Rule **
    //         $this->publicMethods[$methodName] = $methodName;
    //         if (sizeof($this->publicMethods) > 1) {
    //             throw new RunTimeException(
    //                 'Just one public method allowed because of framework has a principle "One Public Method Per Controller".
    //                 If you want to add private methods use underscore ( _methodname ). <pre>$app->func(\'_methodname\', function(){});</pre>'
    //             );
    //         }
    //     }
    //     if ( ! is_callable($methodCallable)) {
    //         throw new InvalidArgumentException('Controller error: Second parameter must be callable.');
    //     }
    //     $this->controllerMethods[$methodName] = Closure::bind($methodCallable, $this, get_class());
    // }

    /**
     * Call the controller method
     * 
     * @param string $method method
     * @param array  $args   closure function arguments
     * 
     * @return void
     */
    // public function __call($method, $args)
    // {
    //     if (isset($this->controllerMethods[$method])) {
    //         $return = call_user_func_array($this->controllerMethods[$method], $args);
    //         $this->router->initFilters('after');    // Run router after filters
    //         return $return;
    //     }
    //     throw new RunTimeException(
    //         sprintf(
    //             '%s error: There is no method "%s()" to call in the Controller.',
    //             __CLASS__,
    //             $method
    //         )
    //     );
    // }

}

// END Controller class

/* End of file Controller.php */
/* Location: .Obullo/Controller/Controller.php */