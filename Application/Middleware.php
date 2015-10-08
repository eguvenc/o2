<?php

namespace Obullo\Application;

use LogicException;
use Obullo\Container\ContainerInterface;

/**
 * Abstract Middleware
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
abstract class Middleware
{
    /**
     * Reference to the next downstream middleware
     * 
     * @var mixed
     */
    protected $next;

    /**
     * Container
     * 
     * @var object
     */
    private $__container;

    /**
     * Container
     * 
     * @param string $key key
     * 
     * @return object
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
        return $this->__container[$key];
    }

    /**
     * Set next middleware
     *
     * This method injects the next downstream middleware into
     * this middleware so that it may optionally be called
     * when appropriate.
     *
     * @param Http\Middleware $next next middleware
     *
     * @return void
     */
    final public function setNextMiddleware($next)
    {
        $this->next = $next;
    }

    /**
     * Get next middleware
     *
     * This method retrieves the next downstream middleware
     * previously injected into this middleware.
     *
     * @return \Http\Middleware
     */
    final public function getNextMiddleware()
    {
        return $this->next;
    }
    
    /**
     * Perform actions specific to this middleware and optionally
     * call the next downstream middleware.
     * 
     * @return void
     */
    abstract public function call();

}