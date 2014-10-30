<?php

namespace Obullo\Auth;

/**
 * O2 Authentication - Security Token
 *
 * @category  Auth
 * @package   Token
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
Class Token
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Security token cache
     * 
     * @var string
     */
    protected $token = null;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Generates random token for o2 auth security cookie
     * 
     * @return void
     */
    public function generate()
    {
        $request = $this->c->load('return request');
        $utils = $this->c->load('return utils/random');
        $token = $utils->generate('alnum', 16);
        $userAgent = substr($request->server('HTTP_USER_AGENT'), 0, 50);  // First 50 characters of the user agent

        return $this->token = $token .'.'. hash('adler32', trim($userAgent));  // Creates smaller token
    }

    /**
     * If token exists don't refresh return to old.
     * 
     * @return string token
     */
    public function get()
    {
        if ($this->token != null) {  // If we have already token don't regenerate it.
            return $this->token;
        }
        return $this->refresh();
    }

    /**
     * Get token from cookie
     * 
     * @return string
     */
    public function getCookie()
    {
        $cookie = $this->c['config']->load('auth')['security']['cookie'];
        return $this->c->load('cookie')->get($cookie['name']);
    }

    /**
     * Refresh unique security token
     * 
     * @return void
     */
    public function refresh()
    {
        $token = $this->generate();
        $cookie = $this->c['config']->load('auth')['security']['cookie'];

        $this->c->load('cookie')->set(
            $cookie['name'],
            $token,
            $cookie['expire'],
            $this->c['config']['cookie']['domain'],        //  Get domain from global config
            $cookie['path'],
            $cookie['prefix'],
            $cookie['secure'],
            $cookie['httpOnly']
        );
        return $token;
    }

}

// END Token.php File
/* End of file Token.php

/* Location: .Obullo/Auth/Token.php */