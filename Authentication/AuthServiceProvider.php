<?php

namespace Obullo\Authentication;

use ArrayAccess;
use Obullo\Container\Container;
use Obullo\Authentication\User\Login;
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
class AuthServiceProvider implements ArrayAccess
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Config array
     * 
     * @var array
     */
    protected $params;    

    /**
     * Create classes
     * 
     * @param object $c      container
     * @param array  $params config parameters
     * 
     * @return object
     */
    public function __construct(Container $c, $params = array())
    {
        $this->c = $c;
        $this->params = $params = array_merge($params, $this->c['config']->load('auth'));

        $this->c['auth.storage'] = function () use ($params) {
            return new $this['cache']['storage']($this->c, $this->c['app']->provider('cache'), $params);
        };
        $this->c['auth.token'] = function () use ($params) {
            return new Token($params['login']['rememberMe']['cookie']);
        };

        $this->c['auth.adapter'] = function () use ($params) {
            return new $params['db.adapter']($this->c);
        };

        $this->c['user.model'] = function () use ($params) {
            return new $params['db.model']($this->c, $this->c['app']->provider($params['db.provider']));
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

    /**
     * Sets a parameter or an object.
     *
     * @param string $key   The unique identifier for the parameter
     * @param mixed  $value The value of the parameter
     *
     * @return void
     */
    public function offsetSet($key, $value)
    { 
        $this->params[$key] = $value;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($key)
    {
        if ( ! isset($this->params[$key])) {
            return false;
        }
        return $this->params[$key];
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return Boolean
     */
    public function offsetExists($key)
    {
        return isset($this->params[$key]);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->params[$key]);
    }

}

// END AuthServiceProvider class
/* End of file AuthServiceProvider.php */

/* Location: .Obullo/Authentication/AuthServiceProvider.php */