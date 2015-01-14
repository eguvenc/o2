<?php

namespace Obullo\Authentication;

use Obullo\Authentication\User\UserActivity,
    Obullo\Authentication\User\UserIdentity,
    Obullo\Authentication\User\UserLogin;

/**
 * O2 Authentication - User Service
 *
 * @category  Authentication
 * @package   UserService
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class UserService
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
    public function __construct($c, $params = array())
    {
        $this->c = $c;
        $this->config = $c['config']->load('auth');

        $this->c['auth.storage'] = function () {
            return new $this->config['cache']['storage']($this->c);
        };
        $this->c['auth.adapter'] = function () {
            return new $this->config['adapter']($this->c, $this);
        };
        $this->c['user.provider'] = function () use ($params) {
            return new $this->config['user']['provider']($this->c, $params);
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
        return $this->c['auth.'.strtolower($class)]; // Services: $this->user->login, $this->user->identity, $this->user->activity .. ]
    }

}

// END UserService.php File
/* End of file UserService.php

/* Location: .Obullo/Authentication/UserService.php */