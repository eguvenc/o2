<?php

namespace Obullo\Application\Addons;

trait RewriteLocaleTrait
{
    /**
     * Http locale Rewrite trait
     *
     * This feature sends redirect header if we get a request without locale code.
     *
     * Example wrong request: http://example.com/welcome
     * Example redirect     : http://example.com/en/welcome  Send 302 Redirect Header
     * 
     * @return void
     */
    public function rewrite()
    {
        $config = $this->c['config']->load('translator');

        $locale = $this->c['uri']->segment($config['uri']['segmentNumber']);  // Check the segment http://examples.com/en/welcome
        $languages = $config['languages'];

        if ( ! isset($languages[$locale])) {
            $this->c['url']->redirect($this->c['translator']->getLocale() . $this->c['uri']->getRequestUri());
        }
    }
}

// END RewriteLocaleTrait File
/* End of file RewriteLocaleTrait.php

/* Location: .Obullo/Application/Addons/RewriteLocaleTrait.php */