<?php

namespace Obullo\Application\Middlewares;

trait MethodNotAllowedTrait
{
    /**
     * Check valid route request method is allowed
     *
     * Send 405 Header
     * 
     * @return void
     */
    public function methodIsAllowed()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $currentMethod = strtolower($method);

        if ( ! in_array($currentMethod, $this->params)) {  // Get injected parameters

            $this->c['response']->status(405)->showError(
                sprintf(
                    "Http Error 405 %s method not allowed.", 
                    ucfirst($currentMethod)
                ),
                'Method Not Allowed'
            );
        }
    }
}

// END MethodNotAllowedTrait File
/* End of file MethodNotAllowedTrait.php

/* Location: .Obullo/Application/Middlewares/MethodNotAllowedTrait.php */