<?php

namespace Obullo\Permissions;

/**
 * RbacService Class
 * 
 * @category  Permissions
 * @package   Rbac
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
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
     * Database provider
     *
     * @var object
     */
    protected $db;

    /**
     * Constructor
     *
     * @param object $c  container
     * @param object $db database object
     */
    public function __construct($c, $db)
    {
        $this->c = $c;
        $this->db = $db;
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
        $key = strtolower($class);   // Services: $this->rbac->user, $this->rbac->permissions, $this->rbac->roles .. 

        if (isset($this->{$key})) {  // Lazy loading ( returns to old instance if class already exists ).
            return $this->{$key};
        }
        $Class = '\Obullo\Permissions\Rbac\\'.ucfirst($key);

        return $this->{$key} = new $Class($this->c, $this->db);
    }

}

// END RbacService class
/* End of file RbacService.php */

/* Location: .Obullo/Permissions/RbacService.php */