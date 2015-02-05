<?php

namespace Obullo\Application\Addons;

trait RewriteLocaleTrait
{
    /**
     * Http locale Rewrite trait
     *
     * Send 302 Header
     * 
     * @return void
     */
    public function rewrite()
    {
        $locale = $this->c['cookie']->get('locale');
        $languages = $this->c['config']->load('translator')['languages'];

        if ( ! isset($languages[$locale]) OR $locale == false) {
            $locale = $this->c['translator']->getLocale();
        }
        $this->c['url']->redirect($locale. '/' . $this->c['uri']->getUriString());
    }
}

// END RewriteLocaleTrait File
/* End of file RewriteLocaleTrait.php

/* Location: .Obullo/Application/Addons/RewriteLocaleTrait.php */