<?php

namespace Obullo\Authentication;

use Obullo\Container\Container,
    Obullo\Authentication\User\UserActivity,
    Obullo\Authentication\User\UserIdentity,
    Obullo\Authentication\User\UserLogin;

/**
 * O2 Authentication - User Service Provider
 *
 * @category  Authentication
 * @package   UserServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class UserServiceProvider
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Service configuration parameters
     * 
     * @var array
     */
    protected $config = array();

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params configuration parameters
     */
    public function __construct(Container $c, $params = array())
    {
        $this->c = $c;
        $this->config = $c['config']->load('auth');

        $this->c['auth.storage'] = function () {
            return new $this->config['cache']['storage']($this->c);
        };
        $this->c['auth.adapter'] = function () {
            return new $this->config['adapter']($this->c, $this);
        };
        $this->c['user.model'] = function () use ($params) {
            return new $this->config['user']['model']($this->c, $params);
        };
        $this->c['auth.identity'] = function () use ($params) {
            return new UserIdentity($this->c, $this);
        };
        $this->c['auth.activity'] = function () use ($params) {
            return new UserActivity($this->c, $this);
        };
        $this->c['auth.login'] = function () use ($params) {
            return new UserLogin($this->c, $this);
        };
    }

    /**
     * Service class loader
     * 
     * @param string $class name
     * 
     * @return object | null
     */
    public function __get($class)
    {
        return $this->c['auth.'.strtolower($class)]; // Services: $this->user->login, $this->user->identity, $this->user->activity ..
    }

}

// END UserServiceProvider.php File
/* End of file UserServiceProvider.php

/* Location: .Obullo/Authentication/UserServiceProvider.php */