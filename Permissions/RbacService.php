<?php

namespace Obullo\Permissions;

/**
 * RbacService Class
 * 
 * @category  Permissions
 * @package   Rbac
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/permissions
 */
Class RbacService
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Config parameters
     * 
     * @var object
     */
    protected $config;

    /**
     * Database provider
     *
     * @var object
     */
    protected $db;

    /**
     * Constructor
     *
     * @param object $c      container
     * @param object $db     database object
     * @param array  $config configuration
     */
    public function __construct($c, $db, $config = array())
    {
        $this->c = $c;
        $this->db = $db;
        $this->config = $config;
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
        $key = strtolower($class);   // Services: $this->user->login, $this->user->identity, $this->user->activity .. 

        if (isset($this->{$key})) {  // Lazy loading ( returns to old instance if class already exists ).
            return $this->{$key};
        }
        $Class = '\Obullo\Permissions\Rbac\\'.ucfirst($key);

        return $this->{$key} = new $Class($this->c, $this->db, $this->config);
    }

}

// END RbacService class
/* End of file RbacService.php */

/* Location: .Obullo/Permissions/RbacService.php */