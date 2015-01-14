<?php

namespace Obullo\Permissions;

use Obullo\Permissions\Rbac\User,
    Obullo\Permissions\Rbac\Roles,
    Obullo\Permissions\Rbac\Resource,
    Obullo\Permissions\Rbac\Permissions;

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
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;

        $this->c['rbac.user'] = function () {
            return new User($this->c); 
        };
        $this->c['rbac.roles'] = function () {
            return new Roles($this->c); 
        };
        $this->c['rbac.resource'] = function () {
            return new Resource($this->c); 
        };
        $this->c['rbac.permissions'] = function () {
            return new Permissions($this->c); 
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
        return $this->c['rbac.'. strtolower($class)]; // Services: $this->rbac->user, $this->rbac->resource, $this->rbac->roles .. 
    }

}

// END RbacService class
/* End of file RbacService.php */

/* Location: .Obullo/Permissions/RbacService.php */