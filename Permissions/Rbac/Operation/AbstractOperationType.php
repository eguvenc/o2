<?php

namespace Obullo\Permissions\Rbac\Operation;

/**
 * Abstract Operation
 * 
 * @category  Rbac
 * @package   AbstractOperationType
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/permissions
 */
abstract class AbstractOperationType
{
    /**
     * Operation name
     * 
     * @var string
     */
    protected $operationName  = '';

    /**
     * Permission name
     * 
     * @var string
     */
    protected $permissionName = '';

    /**
     * Gey key
     * 
     * @return string key
     */
    abstract protected function getKey();

    /**
     * Get permissions
     * 
     * @return array
     */
    abstract protected function getPermissions();

    /**
     * Is allowed
     * 
     * @return boolean
     */
    abstract public function isAllowed();

    /**
     * Set permission name
     * 
     * @param string $name permission name
     * 
     * @return void
     */
    public function setPermissionName($name)
    {
        $this->permissionName = $name;
    }

    /**
     * Set operation name
     * 
     * @param string $name operation name
     * 
     * @return void
     */
    public function setOperationName($name)
    {
        $this->operationName = $name;
    }

    /**
     * Get operation name
     * 
     * @return string
     */
    public function getOperationName()
    {
        return $this->operationName;
    }

    /**
     * Get permission name
     * 
     * @return string
     */
    public function getPermissionName()
    {
        return $this->permissionName;
    }

}


// END AbstractOperationType.php File
/* End of file AbstractOperationType.php

/* Location: .Obullo/Permissions/Rbac/AbstractOperationType.php */
