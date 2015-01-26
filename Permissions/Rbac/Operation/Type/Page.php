<?php

namespace Obullo\Permissions\Rbac\Operation\Type;

use ArrayAccess,
    Obullo\Permissions\Rbac\Resource,
    Obullo\Permissions\Rbac\Operation\AbstractOperationType;

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
Class Page extends AbstractOperationType implements ArrayAccess
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
        parent::__construct($c);
    }

    /**
     * Get permissions
     *
     * @return array
     */
    protected function getPermissions()
    {
        return $this->c['rbac.resource']->page->getPermission($this->getOperationName());
    }

    /**
     * Get key
     * 
     * @return string
     */
    protected function getKey()
    {
        return $this->c['rbac.user']->columnPermResource;
    }

    /**
     * Set resource id
     * 
     * @param string $offset offset
     * 
     * @return object self
     */
    public function offsetGet($offset)
    {
        $this->c['rbac.resource']->setId($offset);
        $this->setPermissionName($offset);
        return $this;
    }

    /**
     * Is allowed permission
     * 
     * @return boolean
     */
    public function isAllowed()
    {
        $permissions = $this->getPermissions();

        if (! is_array($permissions)) {
            return false;
        }
        $key = $this->getKey();
        $permName = $this->getPermissionName();

        foreach ($permissions as $perm) {
            if ($permName == $perm[$key]) {
                return true;
            }
        }
        return false;
    }
}


// END Page.php File
/* End of file Page.php

/* Location: .Obullo/Permissions/Rbac/Page.php */
