<?php

namespace Obullo\Permissions\Rbac\Model;

/**
 * Model Roles
 * 
 * @category  Model
 * @package   Roles
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/tree
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
     * @param object $c  container
     * @param object $db database
     */
    public function __construct($c, $db)
    {
        $this->c = $c;
        $this->db = $db;
        $this->roles = $this->c['rbac.roles'];
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
    public function deletePermissions($roleId)
    {
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
    public function deleteUsers($roleId)
    {
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
    public function deleteOperations($roleId)
    {
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

    /**
     * Get PDO Statement Object
     * 
     * @return array
     */
    public function getStatement()
    {
        return $this->db->getStatement();
    }
}

// END Roles.php File
/* End of file Roles.php

/* Location: .Obullo/Permissions/Rbac/Model/Roles.php */