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
    public $c, $config, $uri, $router, $logger; // Default packages

    /**
     * Constructor
     */
    public function __construct()
    {
        global $c;
        $this->c = $c;

        self::$instance = &$this;
        $logger = $c->load('service/logger');  // Assign Default Loaded Packages
                                               // NOTICE:
        $this->config = &$c['config'];         // If we don't use assign by reference this will cause some errors in "Layers".
        $this->uri    = &$c['uri'];            // The bug is insteresting, when we work with multiple page not found requests
        $this->router = &$c['router'];         // The objects of Controller keep the last instances of the last request.
        $this->logger = &$logger;              // that means the controller instance don't be the reset.

        $this->router->initFilters('before');  // Initialize ( exec ) registered router ( before ) filters

                                               // Keep in your mind we need use pass by reference in some situations.
                                               // @see http://www.php.net/manual/en/language.references.whatdo.php

        foreach ($c->unRegisteredKeys() as $key) {  // On router level ( routes.php ) some classes does not assigned into controller instance
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
            throw new RunTimeException('Just object type variable allowed in controllers.');
        }
        $this->{$key} = $val; // store only app classes & packages 
                              // and object types
    }

}

// END Controller class

/* End of file Controller.php */
/* Location: .Obullo/Controller/Controller.php */