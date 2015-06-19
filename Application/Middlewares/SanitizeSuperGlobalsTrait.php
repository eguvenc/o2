<?php

namespace Obullo\Application\Middlewares;

trait SanitizeSuperGlobalsTrait
{
    /**
     * Sanitizer
     * 
     * @return void
     */
    public function sanitize()
    {
        if ($this->config['uri']['queryStrings'] == false) {  // Is $_GET data allowed ? 
            $_GET = array();
        }
        $_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']); // Sanitize PHP_SELF

        // Clean $_COOKIE Data
        // Also get rid of specially treated cookies that might be set by a server
        // or silly application, that are of no use to application anyway
        // but that when present will trip our 'Disallowed Key Characters' alarm
        // http://www.ietf.org/rfc/rfc2109.txt
        // note that the key names below are single quoted strings, and are not PHP variables
        unset(
            $_COOKIE['$Version'],
            $_COOKIE['$Path'],
            $_COOKIE['$Domain']
        );
    }
}

// END SanitizeSuperGlobalsTrait File
/* End of file SanitizeSuperGlobalsTrait.php

/* Location: .Obullo/Application/Middlewares/SanitizeSuperGlobalsTrait.php */