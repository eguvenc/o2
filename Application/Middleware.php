<?php

namespace Obullo\Application;

use Obullo\Container\Container;

/**
 * Abstract Middleware
 * 
 * @category  Http
 * @package   Midddleware
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http
 */
abstract class Middleware
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Reference to the primary application instance
     * 
     * @var object
     */
    protected $app;

    /**
     * Reference to the next downstream middleware
     * 
     * @var mixed
     */
    protected $next;

    /**
     * Set Container
     * 
     * @param object $c container
     *
     * @return void
     */
    public function setContainer($c)
    {
        $this->c = $c;
    }

    /**
     * Set application
     * 
     * @param object $application obullo class
     *
     * @return void
     */
    final public function setApplication($application)
    {
        $this->app = $application;
    }

    /**
     * Get application
     *
     * This method retrieves the application previously injected
     * into this middleware.
     *
     * @return \Obullo\Obullo
     */
    final public function getApplication()
    {
        return $this->app;
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
     * Perform loader specific to this middleware and optionally
     * call the next loader of the controller.
     * 
     * @return void
     */
    abstract public function load();

    /**
     * Perform actions specific to this middleware and optionally
     * call the next downstream middleware.
     * 
     * @return void
     */
    abstract public function call();

}

// END Middleware.php File
/* End of file Middleware.php

/* Location: .Obullo/Application/Middleware.php */