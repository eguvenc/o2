<?php

namespace Obullo\Auth;

use Obullo\Utils\Random;

/**
 * O2 Authentication - Security Token
 *
 * @category  Auth
 * @package   Token
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
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
     * Auth config
     * 
     * @var array
     */
    protected $config;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('auth');
    }

    /**
     * Generates random token for o2 auth security cookie
     * 
     * @return void
     */
    public function generate()
    {
        $userAgentMatch = null;
        if ($this->config['security']['cookie']['userAgentMatch']) {
            $request = $this->c->load('return request');
            $userAgent = substr($request->server('HTTP_USER_AGENT'), 0, 50);  // First 50 characters of the user agent
            $userAgentMatch = '.' . hash('adler32', trim($userAgent));
        }
        $token = Random::generate('alnum', 16);
        return $this->token = $token.$userAgentMatch;  // Creates smaller token
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
        $cookie = $this->config['security']['cookie'];
        return isset($_COOKIE[$cookie['name']]) ? $_COOKIE[$cookie['name']] : false;
    }

    /**
     * Refresh unique security token
     * 
     * @return void
     */
    public function refresh()
    {
        $token = $this->generate();
        $cookie = $this->config['security']['cookie'];

        setcookie(
            $cookie['prefix'].$cookie['name'], 
            $token, 
            time() + $cookie['expire'], 
            $cookie['path'], 
            $this->c['config']['cookie']['domain'],   //  Get domain from global config
            $cookie['secure'], 
            $cookie['httpOnly']
        );
        return $token;
    }

    /**
     * Run cookie reminder
     * 
     * @return string token
     */
    public function getRememberToken()
    {
        $token = Random::generate('alnum', 32);
        $cookie = $this->config['login']['rememberMe']['cookie'];

        setcookie(
            $cookie['prefix'].$cookie['name'], 
            $token, 
            time() + $cookie['expire'], 
            $cookie['path'], 
            $this->c['config']['cookie']['domain'],   //  Get domain from global config
            $cookie['secure'], 
            $cookie['httpOnly']
        );
        return $token;
    }


}

// END Token.php File
/* End of file Token.php

/* Location: .Obullo/Auth/Token.php */