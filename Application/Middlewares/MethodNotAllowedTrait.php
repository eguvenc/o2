<?php

namespace Obullo\Application\Middlewares;

trait MethodNotAllowedTrait
{
    /**
     * Check valid route request method is allowed
     *
     * Send 405 Header
     *
     * @param array $params allowed methods
     * 
     * @return void
     */
    public function check(array $params)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $currentMethod = strtolower($method);

        if (! in_array($currentMethod, $params)) {  // Check method is allowed

            $this->response->withStatus(405)->showError(
                sprintf(
                    "Http Error 405 %s method not allowed.", 
                    ucfirst($currentMethod)
                ),
                'Method Not Allowed'
            );
        }
    }
}