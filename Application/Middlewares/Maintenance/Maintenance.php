<?php

namespace Http\Middlewares;

use Obullo\Container\Container;
use Obullo\Application\Middleware;
use Obullo\Application\Middlewares\UnderMaintenanceTrait;

class Maintenance extends Middleware
{
    use UnderMaintenanceTrait;

    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->domainIsDown();

        $this->next->load();
    }

    /**
     *  Call action
     * 
     * @return void
     */
    public function call()
    {
        $this->next->call();
    }
    
}