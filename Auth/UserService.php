<?php

namespace Obullo\Auth;

/**
 * O2 Authentication - User Service
 *
 * @category  Auth
 * @package   UserService
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
Class UserService
{
    /**
     * Service configuration parameters
     * 
     * @var array
     */
    public $params = array();

    /**
     * Constructor
     * 
     * @param object $c container
     *
     * @return void
     */
    public function __construct($c)
    {
        $config = $c->load('config')->load('auth');

        $Adapter = '\Obullo\Auth\Adapter\\'.$config['adapter'];
        $Storage = '\Obullo\Auth\Storage\\'.ucfirst($config['memory']['storage']);

        $this->params = array(
            'c' => $c,
            'config' => $config,
            'user' => $this,
            'storage' => new $Storage($c)
        );
        
        $c['o2.auth.service.adapter'] = function () use ($c, $Adapter) {
            return new $Adapter($c, $this);
        };
    }

    /**
     * Service class loader magic method
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
        return $this->{$key} = new $Class($this->params);
    }

}

// END UserService.php File
/* End of file UserService.php

/* Location: .Obullo/Auth/UserService.php */