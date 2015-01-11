<?php

namespace Obullo\Permissions\Rbac\Model;

use Obullo\Permissions\Rbac\Roles as RbacRoles;

/**
 * Db Roles
 * 
 * @category  Db
 * @package   Roles
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/tree
 * 
 * What is the Rbac?
 * @see       https://www.sans.org/reading-room/whitepapers/sysadmin/role-based-access-control-nist-solution-1270
 *
 * http://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.84.9866&rep=rep1&type=pdf
 */
Class Roles
{
    /**
     * Database instance
     * 
     * @var object
     */
    protected $db;

    /**
     * Permissions\Rbac\Roles instance
     * 
     * @var object
     */
    protected $roles;

    /**
     * Constructor
     * 
     * @param object $roles rbac roles instance
     */
    public function __construct(RbacRoles $roles)
    {
        $this->db    = $roles->c['db'];
        $this->roles = $roles;
    }

    /**
     * Get permissions sql query
     * 
     * @param int $roleId role id
     * 
     * @return array
     */
    public function getPermissionsSqlQuery($roleId)
    {
        $this->db->prepare(
            'SELECT %s FROM %s WHERE %s = ?',
            array(
                $this->db->protect($this->roles->rolePermPrimaryKey),
                $this->db->protect($this->roles->rolePermTableName),
                $this->db->protect($this->roles->rolePermRolePrimaryKey)
            )
        );
        $this->db->bindValue(1, $roleId, PARAM_INT);
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Get users sql query
     * 
     * @param int $roleId role id
     * 
     * @return array
     */
    public function getUsersSqlQuery($roleId)
    {
        $this->db->prepare(
            'SELECT %s FROM %s WHERE %s = ?',
            array(
                $this->db->protect($this->roles->columnUserPrimaryKey),
                $this->db->protect($this->roles->userRolesTableName),
                $this->db->protect($this->roles->columnUserRolePrimaryKey)
            )
        );
        $this->db->bindValue(1, $roleId, PARAM_INT);
        $this->db->execute();
        
        return $this->db->resultArray();
    }

    /**
     * Delete Role Perm Assign
     * 
     * @param int $roleId role primary key
     * 
     * @return object statement of Pdo
     */
    public function deleteRolePermissions($roleId)
    {
        if ( ! is_array($roleId)) {
            $roleId = array(array($this->roles->primaryKey => $roleId));
        }
        $roleId = array_reverse($roleId);

        $this->db->prepare(
            'DELETE FROM %s WHERE %s IN (%s)',
            array(
                $this->db->protect($this->roles->rolePermTableName),
                $this->db->protect($this->roles->rolePermRolePrimaryKey),
                str_repeat('?,', count($roleId) - 1) . '?'
            )
        );
        $i = 1;
        foreach ($roleId as $id) {
            $this->db->bindValue($i++, $id[$this->roles->primaryKey], PARAM_INT);
        }

        return $this->db->execute();
    }

    /**
     * Delete Assign User Roles
     * 
     * @param int $roleId role primary key
     * 
     * @return object statement of Pdo
     */
    public function deleteRoleFromUsers($roleId)
    {
        if ( ! is_array($roleId)) {
            $roleId = array(array($this->roles->primaryKey => $roleId));
        }
        $roleId = array_reverse($roleId);

        $this->db->prepare(
            'DELETE FROM %s WHERE %s IN (%s)',
            array(
                $this->db->protect($this->roles->userRolesTableName),
                $this->db->protect($this->roles->columnUserRolePrimaryKey),
                str_repeat('?,', count($roleId) - 1) . '?'
            )
        );
        $i = 1;
        foreach ($roleId as $id) {
            $this->db->bindValue($i++, $id[$this->roles->primaryKey], PARAM_INT);
        }

        return $this->db->execute();
    }

    /**
     * Delete Assign User Roles
     * 
     * @param int $roleId role primary key
     * 
     * @return object statement of Pdo
     */
    public function deleteOperationsByRoleId($roleId)
    {
        if ( ! is_array($roleId)) {
            $roleId = array(array($this->roles->primaryKey => $roleId));
        }
        $roleId = array_reverse($roleId);

        $this->db->prepare(
            'DELETE FROM %s WHERE %s IN (%s)',
            array(
                $this->db->protect($this->roles->opPermsTableName),
                $this->db->protect($this->roles->opPermsRolePrimaryKey),
                str_repeat('?,', count($roleId) - 1) . '?'
            )
        );
        $i = 1;
        foreach ($roleId as $id) {
            $this->db->bindValue($i++, $id[$this->roles->primaryKey], PARAM_INT);
        }

        return $this->db->execute();
    }
}

// END Roles.php File
/* End of file Roles.php

/* Location: .Obullo/Permissions/Rbac/Db/Roles.php */