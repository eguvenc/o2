<?php

namespace Obullo\Permissions\Rbac\Operation\Type;

use ArrayAccess;
use Obullo\Permissions\Rbac\Resource;
use Obullo\Permissions\Rbac\Operation\AbstractOperationType;

/**
 * Resource Object Permission
 * 
 * @category  Resource
 * @package   Object
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/permissions
 */
class Object extends AbstractOperationType implements ArrayAccess
{
    /**
     * Permissions
     * 
     * @var array
     */
    protected $permissions = array();

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;

        $this->c['rbac.resource'] = function () {
            return new Resource($this->c); 
        };
    }

    /**
     * Get permissions
     * 
     * @param string $permName permission name
     *
     * @return array
     */
    protected function getPermissions($permName = '')
    {
        if ($permName === null) {
            return $this->c['rbac.resource']->object[$this->getPermissionName()]
                ->getPermissions(
                    $this->getOperationName()
                );
        }
        return $this->c['rbac.resource']->object[$this->getPermissionName()]
            ->getPermissions(
                $permName,
                $this->getOperationName()
            );
    }

    /**
     * Get key
     * 
     * @return string
     */
    protected function getKey()
    {
        return $this->c['rbac.user']->columnPermText;
    }

    /**
     * Permission name
     * 
     * @param string $perm name
     * 
     * @return object self
     */
    public function offsetGet($perm)
    {
        $this->setPermissionName($perm);
        return $this;
    }

    /**
     * Is allowed permission
     * 
     * @param string $permName permission name
     * 
     * @return boolean
     */
    public function isAllowed($permName = null)
    {
        $permissions = $this->getPermissions($permName);

        if (! is_array($permissions)) {
            return false;
        }
        $key      = $this->getKey();
        $permName = ($permName === null) ? $this->getPermissionName() : $permName;

        foreach ($permissions as $perm) {
            if ($permName == $perm[$key]) {
                return true;
            }
        }
        return false;
    }
}


// END Object.php File
/* End of file Object.php

/* Location: .Obullo/Permissions/Rbac/Object.php */
