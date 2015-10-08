<?php

/**
 * Obullo Hmvc Based Controller.
 * 
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Controller
{
    /**
     * Container
     * 
     * @var object
     */
    private $__container;

    /**
     * Controller instance
     * 
     * @var object
     */
    public static $instance = null;
    
    /**
     * Magic get methods
     * 
     * @param string $key key
     * 
     * @return object Controller
     */
    public function __get($key)
    {
        if ($this->__container == null) {
            global $c;
            $this->__container = &$c;
        }
        if ($key == 'c') {
            return $this->__container;
        }
        if (self::$instance == null || in_array($key, ['uri', 'router', 'view'])) {  // Create new layers for core classes ( Otherwise Layer does not work )
            self::$instance = &$this;
        }
        return $this->__container[$key];
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