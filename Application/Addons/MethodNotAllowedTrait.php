<?php

namespace Obullo\Application\Addons;

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

            $this->c['response']->showError(
                sprintf(
                    "Http %s method not allowed.", 
                    ucfirst($currentMethod)
                ),
                405
            );
        }
    }
}

// END MethodNotAllowedTrait File
/* End of file MethodNotAllowedTrait.php

/* Location: .Obullo/Application/Addons/MethodNotAllowedTrait.php */