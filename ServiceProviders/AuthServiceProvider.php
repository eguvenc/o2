<?php

namespace Obullo\ServiceProviders;

use Obullo\Container\Container;
use Obullo\Authentication\Token;
use Obullo\Authentication\User\Login;
use Obullo\Authentication\User\Config;
use Obullo\Authentication\User\Activity;
use Obullo\Authentication\User\Identity;

/**
 * AuthServiceProvider Class
 * 
 * @category  Log
 * @package   Debug
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service_providers
 */
class AuthServiceProvider implements ServiceProviderInterface
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     *
     * @param object $c container
     *
     * @return void
     */
    public function register(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Create classes
     * 
     * @param array $params config parameters
     * 
     * @return void
     */
    public function factory($params = array())
    {
        $this->c['auth.config'] = function () use ($params) {
            return new Config(array_merge($params, $this->c['config']->load('auth')));
        };

        $this->c['auth.storage'] = function () {
            return new $this->config['cache']['storage'](
                $this->c,
                $this->c['service provider cache']->get(
                    [
                        'driver' => $this->config['cache']['provider']['driver'],
                        'connection' => $this->config['cache']['provider']['connection']
                    ]
                )
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
        return $this->c['auth.'.strtolower($class)]; // Services: $this->user->config, $this->user->login, $this->user->identity, $this->user->activity ..
    }
}

// END AuthServiceProvider class
/* End of file AuthServiceProvider.php */

/* Location: .Obullo/ServiceProviders/AuthServiceProvider.php */