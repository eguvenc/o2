<?php

namespace Obullo\Authentication;

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
     * @param object $c        container
     * @param object $database provider
     */
    public function __construct($c, $database)
    {
        $this->c = $c;
        $this->config = $c['config']->load('auth');

        $this->register($database);
    }

    /**
     * Register Services
     *
     * @param object $database provider database
     * 
     * @return void
     */
    protected function register($database)
    {
        $this->c['auth.storage'] = function () {
            return new $this->config['memory']['storage']($this->c);
        };
        $this->c['auth.adapter'] = function () {
            return new $this->config['adapter']($this->c, $this);
        };
        $this->c['user.provider'] = function () use ($database) {
            return new $this->config['user']['provider']($this->c, $database);
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
        $key = strtolower($class); // Services: $this->user->login, $this->user->identity, $this->user->activity .. 

        if (isset($this->{$key})) {  // Lazy loading ( returns to old instance if class already exists ).
            return $this->{$key};
        }
        $Class = '\Obullo\Authentication\User\User'.ucfirst($key);
        return $this->{$key} = new $Class($this->c, $this);
    }

}

// END UserService.php File
/* End of file UserService.php

/* Location: .Obullo/Authentication/UserService.php */