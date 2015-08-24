<?php

namespace Obullo\Authentication;

use Obullo\Authentication\User\Login;
use Obullo\Authentication\User\Activity;
use Obullo\Authentication\User\Identity;
use Obullo\Container\ContainerInterface;

/**
 * AuthManager Class
 * 
 * @category  Auth
 * @package   Authentication
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class AuthManager
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Create classes
     * 
     * @param object $c container
     * 
     * @return object
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Merge auth configuration & service parameters 
     * then pass parameters to register services.
     * 
     * @param array $params parameters
     *
     * @return void
     */
    public function setParameters(array $params)
    {
        AuthConfig::setParameters($this->c['config'], $params);
        $this->register();
    }

    /**
     * Returns to all parameters
     * 
     * @return array
     */
    public function getParameters()
    {
        return AuthConfig::getParameters();
    }

    /**
     * Register authentication services
     *
     * @return void
     */
    protected function register()
    {
        $parameters = $this->getParameters();

        $this->c['auth.storage'] = function () use ($parameters) {
            return new $parameters['cache']['storage']($this->c['session'], $this->c['app']->provider('cache'), $parameters);
        };
        $this->c['auth.identity'] = function () use ($parameters) {
            return new Identity($this->c, $this->c['session'], $this->c['auth.storage'], $parameters);
        };

        $this->c['auth.login'] = function () use ($parameters) {
            return new Login($this->c, $this->c['event'], $this->c['auth.storage'], $parameters);
        };

        $this->c['auth.activity'] = function () {
            return new Activity($this->c['auth.storage'], $this->c['auth.identity']);
        };

        $this->c['auth.model'] = function () use ($parameters) {
            return new $parameters['db.model']($this->c['app']->provider($parameters['db.provider']['name']), $parameters);
        };

        $this->c['auth.adapter'] = function () use ($parameters) {
            return new $parameters['db.adapter'](
                $this->c,
                $this->c['session'],
                $this->c['auth.storage'],
                $parameters
            );
        };
    }

    /**
     * User service class loader
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