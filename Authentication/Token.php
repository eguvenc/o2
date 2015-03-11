<?php

namespace Obullo\Authentication;

use Obullo\Utils\Random;
use Obullo\Container\Container;

/**
 * O2 Authentication - Security Token
 *
 * @category  Authentication
 * @package   Token
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class Token
{
    /**
     * Container
     *
     * @var object
     */
    protected $c;

    /**
     * Authentication config
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
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
        return Random::generate('alnum', 16);  // Creates smaller token
    }

    /**
     * If token exists don't refresh return to old.
     *
     * @return string token
     */
    public function get()
    {
        $cookie = $this->config['security']['cookie'];
        $cookie['value'] = $this->generate();
        $this->c['cookie']->queue($cookie);

        return $cookie['value'];
    }

    /**
     * Get token from cookie
     *
     * @return string
     */
    public function getCookie()
    {
        $cookie = $this->config['security']['cookie'];
        
        return $this->c['cookie']->get($cookie['prefix'].$cookie['name']);
    }

    /**
     * Run cookie reminder
     *
     * @return string token
     */
    public function getRememberToken()
    {
        $cookie = $this->config['login']['rememberMe']['cookie'];

        $cookie['value'] = Random::generate('alnum', 32);
        $this->c['cookie']->queue($cookie);

        return $cookie['value'];
    }
}

// END Token.php File
/* End of file Token.php

/* Location: .Obullo/Authentication/Token.php */