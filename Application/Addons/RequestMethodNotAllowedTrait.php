<?php

namespace Obullo\Application\Addons;

trait RequestMethodNotAllowedTrait
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
        $currentMethod = strtolower($this->c['request']->method());

        if ( ! in_array($currentMethod, $this->allowedMethods)) {

            $this->c['response']->showError(
                sprintf(
                    "Http %s method not allowed.", 
                    ucfirst($this->c['request']->method())
                ),
                405
            );
        }
    }
}

// END RequestMethodNotAllowedTrait File
/* End of file RequestMethodNotAllowedTrait.php

/* Location: .Obullo/Application/Addons/RequestMethodNotAllowedTrait.php */