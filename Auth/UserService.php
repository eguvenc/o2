<?php

namespace Obullo\Auth;

/**
 * O2 Authentication - User Service
 *
 * @category  Auth
 * @package   UserService
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/auth
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
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->config = $c['config']->load('shared/auth');

        $this->register();
    }

    /**
     * Register Services
     * 
     * @return void
     */
    protected function register()
    {
        $Adapter = '\Obullo\Auth\Adapter\\'.$this->config['adapter'];
        $Storage = '\Obullo\Auth\Storage\\'.ucfirst($this->config['memory']['storage']);

        $this->c['auth.storage'] = function () use ($Storage) {
            return new $Storage($this->c);
        };

        $this->c['auth.adapter'] = function () use ($Adapter) {
            return new $Adapter($this->c, $this);
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
        $Class = '\Obullo\Auth\User\\'.ucfirst($key);
        return $this->{$key} = new $Class($this->c, $this);
    }

}

// END UserService.php File
/* End of file UserService.php

/* Location: .Obullo/Auth/UserService.php */