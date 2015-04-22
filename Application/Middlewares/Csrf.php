<?php

namespace Http\Middlewares;

use Obullo\Container\Container;
use Obullo\Application\Middleware;

class Csrf extends Middleware
{
    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->next->load();
    }

    /**
     *  Call action
     * 
     * @return void
     */ 
    public function call()
    {
        $verify = $this->c['csrf']->verify();

        if ($this->c['request']->isAjax() AND ! $verify) {      // Build your ajax errors
            
            echo $this->c['response']->json(
                [
                    'success' => 0,
                    'message' => 'The action you have requested is not allowed.'
                ]
            );

        } elseif ( ! $verify) {     // Build your http errors

            $this->c['response']->showError(
                'The action you have requested is not allowed.', 
                401, 
                'Access Denied'
            );
        }
        $this->next->call();
    }
    
}