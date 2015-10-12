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
        $method = $this->request->getMethod();
        $currentMethod = strtolower($method);

        if (! in_array($currentMethod, $params)) {  // Check method is allowed

            $this->response->showError(
                sprintf(
                    "Http Error 405 %s method not allowed.", 
                    ucfirst($currentMethod)
                ),
                405,
                'Method Not Allowed'
            );
        }
    }
}