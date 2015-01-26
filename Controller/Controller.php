<?php

/**
 * Controller class.
 * 
 * @category  Controller
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/controller
 */
Class Controller
{
    public static $instance;                    // Controller instance
    public $c, $config, $uri, $router, $logger; // Core packages
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $c;
        $this->c = &$c;
        self::$instance = &$this;
        $logger = $c->load('logger');  // Assign Default Loaded Packages
                                               // NOTICE:
        $this->config = &$c['config'];         // If we don't use assign by reference this will cause some errors in "Layers".
        $this->uri    = &$c['uri'];            // The bug is insteresting, when we work with multiple page not found requests
        $this->router = &$c['router'];         // The objects of Controller keep the last instances of the last request.
        $this->logger = &$logger;              // that means the controller instance don't be the reset.
                                               // Keep in your mind we need use pass by reference in some situations.
                                               // @see http://www.php.net/manual/en/language.references.whatdo.php

        foreach ($c->unRegisteredKeys() as $key) {  // On router level ( routes.php ) some classes does not assigned into controller instance
            if ( ! isset($this->{$key})) {          // forexample view and session class, we need to assign them if they not registered.
                $this->{$key} = &$c[$key];          // Register to controller instance
            }
        };
    }
    
    /**
     * We prevent to set none object variables
     *
     * Forexample in controller this is not allowed $this->user_variable = 'hello'.
     * 
     * @param string $key string
     * @param string $val mixed
     *
     * @return void 
     */
    public function __set($key, $val)  // Custom variables is not allowed !!! 
    {
        if (is_object($val)) {
            $this->{$key} = $val; // WARNING : Store only app classes & packages 
                                  // and object types if we don't do this every array params of 
                                  // container will be stored in the controller.
        }
    }
}

// END Controller class

/* End of file Controller.php */
/* Location: .Obullo/Controller/Controller.php */