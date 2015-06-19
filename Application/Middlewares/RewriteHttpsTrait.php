<?php

namespace Obullo\Application\Middlewares;

trait RewriteHttpsTrait
{
    /**
     * Https Rewrite trait
     * 
     * @return void
     */
    public function rewrite()
    {
        if ($this->request->isSecure() == false) {
            $this->url->redirect('https://'.$this->router->getDomain() . $this->uri->getRequestUri());
        }
    }
}

// END RewriteHttpsTrait File
/* End of file RewriteHttpsTrait.php

/* Location: .Obullo/Application/Middlewares/RewriteHttpsTrait.php */