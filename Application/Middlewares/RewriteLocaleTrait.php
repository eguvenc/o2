<?php

namespace Obullo\Application\Middlewares;

use RuntimeException;

trait RewriteLocaleTrait
{
    /**
     * On / Off rewrite
     * 
     * @var boolean
     */
    public $stop = false;

    /**
     * Ignored methods
     * 
     * @var array
     */
    public $excludedMethods = array();

    /**
     * Ignore these methods
     * 
     * @param array $methods get, post, put, delete
     * 
     * @return void
     */
    public function excludeMethods(array $methods)
    {
        $this->excludedMethods = $methods;

        $method = strtolower($this->c['request']->method());
        if (in_array($method, $this->excludedMethods)) {  // Except methods
            $this->stop();
        }
    }

    /**
     * On / off rewrite
     * 
     * @param boolean $stop on / off
     * 
     * @return void
     */
    public function stop($stop = true)
    {
        $this->stop = $stop;
    }

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
        $locale = $this->c['app']->uri->segment($config['uri']['segmentNumber']);  // Check the segment http://examples.com/en/welcome

        if ($this->stop) {
            return;
        }
        $languages = $config['languages'];
        $middlewareNames = $this->c['app']->getMiddlewares();

        if ( ! isset($middlewareNames['Http\Middlewares\Translation'])) {
            throw new RuntimeException(
                sprintf(
                    'RewriteLocale middleware requires Translation middleware. Run this task. <pre>%s</pre>Then add this code to app/middlewares.php <pre>%s</pre>',
                    'php task middleware add --name=Translation',
                    '$c[\'app\']->middleware(new Http\Middlewares\Translation);'
                )
            );
        }
        if ( ! isset($languages[$locale])) {
            $this->c['url']->redirect($this->c['translator']->getLocale() . $this->c['uri']->getRequestUri());
        }
    }
}

// END RewriteLocaleTrait File
/* End of file RewriteLocaleTrait.php

/* Location: .Obullo/Application/Middlewares/RewriteLocaleTrait.php */