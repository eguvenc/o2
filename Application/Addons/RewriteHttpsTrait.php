<?php

namespace Obullo\Application\Addons;

trait RewriteHttpsTrait
{
    /**
     * Https Rewrite trait
     * 
     * @return void
     */
    public function rewrite()
    {
        if ($this->c['request']->isSecure() == false) {
            $this->c['url']->redirect('https://'.$this->c['router']->getDomain() . $this->c['uri']->getRequestUri());
        }
    }
}

// END RewriteHttpsTrait File
/* End of file RewriteHttpsTrait.php

/* Location: .Obullo/Application/Addons/RewriteHttpsTrait.php */