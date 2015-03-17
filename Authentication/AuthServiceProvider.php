<?php

namespace Obullo\Authentication;

use Obullo\Container\Container;
use Obullo\Authentication\Token;
use Obullo\Authentication\User\Login;
use Obullo\Authentication\User\Config;
use Obullo\Authentication\User\Activity;
use Obullo\Authentication\User\Identity;

/**
 * O2 Authentication - User Service Provider
 *
 * @category  Authentication
 * @package   AuthServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class AuthServiceProvider
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params configuration parameters
     */
    public function __construct(Container $c, $params = array())
    {
        $this->c = $c;

        $this->c['auth.config'] = function () use ($params) {
            return new Config($this->c, array_merge($params, $this->c['config']->load('auth')));
        };

        $this->c['auth.storage'] = function () {
            return new $this->config['cache']['storage'](
                $this->c,
                $this->c['service provider cache']
            );
        };

        $this->c['auth.token'] = function () {
            return new Token($this->c);
        };

        $this->c['auth.adapter'] = function () use ($params) {
            return new $params['db.adapter']($this->c);
        };

        $this->c['user.model'] = function () use ($params) {
            return new $params['db.model']($this->c, $this->c['service provider '.$this->config['db.provider']]);
        };

        $this->c['auth.login'] = function () {
            return new Login($this->c);
        };

        $this->c['auth.identity'] = function () {
            return new Identity($this->c);
        };

        $this->c['auth.activity'] = function () {
            return new Activity($this->c);
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

// END AuthServiceProvider.php File
/* End of file AuthServiceProvider.php

/* Location: .Obullo/Authentication/AuthServiceProvider.php */