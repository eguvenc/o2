<?php

namespace Obullo\Permissions\Rbac\Model;

use Obullo\Permissions\Rbac\Permissions as RbacPerms;

/**
 * Db Permissions
 * 
 * @category  Db
 * @package   Permissions
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
Class Permissions
{
    /**
     * Database instance
     * 
     * @var object
     */
    protected $db;

    /**
     * Permissions\Rbac\Permissions instance
     * 
     * @var object
     */
    protected $perms;

    /**
     * Constructor
     * 
     * @param object $perms rbac permissions instance
     */
    public function __construct(RbacPerms $perms)
    {
        $this->db    = $perms->c['db'];
        $this->perms = $perms;
    }

    /**
     * Get roles sql query
     * 
     * @param int $permId perm id
     * 
     * @return array
     */
    public function getRolesSqlQuery($permId)
    {
        $this->db->prepare(
            'SELECT %s FROM %s WHERE %s = ?',
            array(
                $this->db->protect($this->perm->rolePermRolePrimaryKey),
                $this->db->protect($this->perm->rolePermTableName),
                $this->db->protect($this->perm->rolePermPrimaryKey)
            )
        );
        $this->db->bindValue(1, $permId, PARAM_INT);
        $this->db->execute();
        
        return $this->db->resultArray();
    }

    /**
     * Assign to role
     * 
     * @param int $roleId role id
     * @param int $permId perm id
     * 
     * @return object statement of Pdo
     */
    public function assignRole($roleId, $permId)
    {
        $this->db->prepare(
            'INSERT INTO %s (%s,%s,%s) VALUES (?,?,?)',
            array(
                $this->db->protect($this->perms->rolePermTableName),
                $this->db->protect($this->perms->rolePermRolePrimaryKey),
                $this->db->protect($this->perms->rolePermPrimaryKey),
                $this->db->protect($this->perms->assignmentDate)
            )
        );
        $this->db->bindValue(1, $roleId, PARAM_INT);
        $this->db->bindValue(2, $permId, PARAM_INT);
        $this->db->bindValue(3, time(), PARAM_INT);
        
        return $this->db->execute();
    }

    /**
     * Assign to role
     * 
     * @param int $roleId rbac_role primary key
     * @param int $permId rbac_permission primary key
     * @param int $opId   rbac_operation primary key
     * 
     * @return object statement of Pdo
     */
    public function assignOperation($roleId, $permId, $opId)
    {
        $this->db->prepare(
            'INSERT INTO %s (%s,%s,%s) VALUES (?,?,?)',
            array(
                $this->db->protect($this->perms->opPermTableName),
                $this->db->protect($this->perms->opPermPermPrimaryKey),
                $this->db->protect($this->perms->opPermOpPrimaryKey),
                $this->db->protect($this->perms->opPermRolePrimaryKey)
            )
        );
        $this->db->bindValue(1, $permId, PARAM_INT);
        $this->db->bindValue(2, $opId, PARAM_INT);
        $this->db->bindValue(3, $roleId, PARAM_INT);
        
        return $this->db->execute();
    }

    /**
     * De-assign to role
     * 
     * @param int $roleId role id
     * @param int $permId perm id
     * 
     * @return object statement of Pdo
     */
    public function deAssignRole($roleId, $permId)
    {
        $this->db->prepare(
            'DELETE FROM %s WHERE %s = ? AND %s = ?',
            array(
                $this->db->protect($this->perms->rolePermTableName),
                $this->db->protect($this->perms->rolePermRolePrimaryKey),
                $this->db->protect($this->perms->rolePermPrimaryKey)
            )
        );
        $this->db->bindValue(1, $roleId, PARAM_INT);
        $this->db->bindValue(2, $permId, PARAM_INT);
        
        return $this->db->execute();
    }

    /**
     * De Assigned Role Permissions
     * 
     * @param int $permId permission primary key
     * 
     * @return object statement of Pdo
     */
    public function deAssignRoles($permId)
    {
        if ( ! is_array($permId)) {
            $permId = array(array($this->perms->primaryKey => $permId));
        }
        $permId = array_reverse($permId);

        $this->db->prepare(
            'DELETE FROM %s WHERE %s IN (%s)',
            array(
                $this->db->protect($this->perms->rolePermTableName),
                $this->db->protect($this->perms->rolePermPrimaryKey),
                str_repeat('?,', count($permId) - 1) . '?'
            )
        );
        $i = 1;
        foreach ($permId as $id) {
            $this->db->bindValue($i++, $id[$this->perms->primaryKey], PARAM_INT);
        }
        return $this->db->execute();
    }

    /**
     * De assign operation
     * 
     * @param int $roleId rbac_roles primary key
     * @param int $permId rbac_permissions primary key
     * @param int $opId   rbac_operation primary key
     * 
     * @return object statement of Pdo
     */
    public function deAssignOperation($roleId, $permId, $opId)
    {
        $this->db->prepare(
            'DELETE FROM %s WHERE %s = ? AND %s = ? AND %s = ?',
            array(
                $this->db->protect($this->perms->opPermTableName),
                $this->db->protect($this->perms->opPermPermPrimaryKey),
                $this->db->protect($this->perms->opPermOpPrimaryKey),
                $this->db->protect($this->perms->opPermRolePrimaryKey)
            )
        );
        $this->db->bindValue(1, $permId, PARAM_INT);
        $this->db->bindValue(2, $opId, PARAM_INT);
        $this->db->bindValue(3, $roleId, PARAM_INT);
        
        return $this->db->execute();
    }

    /**
     * De assign operations
     * 
     * @param int $roleId role primary key
     * @param int $permId permission primary key
     * 
     * @return object statement of Pdo
     */
    public function deAssignOperations($roleId, $permId)
    {
        $this->db->prepare(
            'DELETE FROM %s WHERE %s = ? AND %s = ?',
            array(
                $this->db->protect($this->perms->opPermTableName),
                $this->db->protect($this->perms->opPermPermPrimaryKey),
                $this->db->protect($this->perms->opPermRolePrimaryKey),
            )
        );
        $this->db->bindValue(1, $permId, PARAM_INT);
        $this->db->bindValue(2, $roleId, PARAM_INT);

        return $this->db->execute();
    }

    /**
     * De assign all operations
     * 
     * @param int $permId permission primary key
     * 
     * @return object statement of Pdo
     */
    public function deAssignAllOperations($permId)
    {
        if ( ! is_array($permId)) {
            $permId = array(array($this->perms->primaryKey => $permId));
        }
        $permId = array_reverse($permId);

        $this->db->prepare(
            'DELETE FROM %s WHERE %s IN (%s)',
            array(
                $this->db->protect($this->perms->opPermTableName),
                $this->db->protect($this->perms->opPermPermPrimaryKey),
                str_repeat('?,', count($permId) - 1) . '?'
            )
        );
        $i = 1;
        foreach ($permId as $id) {
            $this->db->bindValue($i++, $id[$this->perms->primaryKey], PARAM_INT);
        }
        return $this->db->execute();
    }
}

// END Permissions.php File
/* End of file Permissions.php

/* Location: .Obullo/Permissions/Rbac/Db/Permissions.php */