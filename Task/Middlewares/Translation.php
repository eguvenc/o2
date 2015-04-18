<?php

namespace Http\Middlewares;

use Obullo\Application\Middleware;
use Obullo\Application\Middlewares\SetDefaultLocaleTrait;

class Translation extends Middleware
{
    use SetDefaultLocaleTrait;

    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->setLocale();

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