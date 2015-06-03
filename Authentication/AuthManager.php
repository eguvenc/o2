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
 * @copyright 2009-2014 Obullo
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
    public function setConfiguration(array $params)
    {
        AuthConfig::setConfiguration($this->c['config'], $params);

        $serviceParams = AuthConfig::get();
        $this->register($serviceParams);
    }

    /**
     * Register authentication services
     * 
     * @param array $serviceParams service parameters
     *
     * @return void
     */
    protected function register(array $serviceParams)
    {
        $this->c['auth.storage'] = function () use ($serviceParams) {
            return new $serviceParams['cache']['storage']($this->c['session'], $this->c['app']->provider('cache'), $serviceParams);
        };
        $this->c['auth.identity'] = function () use ($serviceParams) {
            return new Identity($this->c, $this->c['session'], $this->c['auth.storage'], $serviceParams);
        };

        $this->c['auth.login'] = function () use ($serviceParams) {
            return new Login($this->c, $this->c['event'], $this->c['auth.storage'], $this->c['auth.identity'], $serviceParams);
        };

        $this->c['auth.activity'] = function () use ($serviceParams) {
            return new Activity($this->c);
        };

        $this->c['auth.token'] = function () use ($serviceParams) {
            return new Token($this->c['cookie'], $serviceParams);
        };

        $this->c['auth.model'] = function () use ($serviceParams) {
            return new $serviceParams['db.model']($this->c['app']->provider($serviceParams['db.provider']), $serviceParams);
        };

        $this->c['auth.adapter'] = function () use ($serviceParams) {
            return new $serviceParams['db.adapter'](
                $this->c,
                $this->c['session'],
                $this->c['auth.storage'],
                $this->c['auth.identity'],
                $serviceParams
            );
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

// END AuthManager class
/* End of file AuthManager.php */

/* Location: .Obullo/Authentication/AuthManager.php */