<?php

namespace Obullo\Permissions\Rbac\Model;

use Obullo\Permissions\Rbac\User as RbacUser;

/**
 * Db User
 * 
 * @category  Db
 * @package   User
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
Class User
{
    /**
     * Database instance
     * 
     * @var object
     */
    protected $db;

    /**
     * Permissions\Rbac\User instance
     * 
     * @var object
     */
    protected $user;

    /**
     * Constructor
     * 
     * @param object $user rbac user instance
     */
    public function __construct(RbacUser $user)
    {
        $this->db   = $user->c['db'];
        $this->user = $user;
    }

    /**
     * Assign
     * 
     * @param int $userId user id
     * @param int $roleId role id
     * 
     * @return object statement of Pdo
     */
    public function assign($userId, $roleId)
    {
        $this->db->prepare(
            'INSERT INTO %s (%s,%s, %s) VALUES (?,?,?)',
            array(
                $this->user->userRolesTableName,
                $this->user->columnUserPrimaryKey,
                $this->user->columnUserRolePrimaryKey,
                $this->user->columnAssignmentDate
            )
        );
        $this->db->bindValue(1, $userId, PARAM_INT);
        $this->db->bindValue(2, $roleId, PARAM_INT);
        $this->db->bindValue(3, time(), PARAM_INT);

        return $this->db->execute();
    }

    /**
     * De-assign
     * 
     * @param int $userId user id
     * @param int $roleId role id
     * 
     * @return boolean
     */
    public function deAssign($userId, $roleId)
    {
        $this->db->prepare(
            'DELETE FROM %s WHERE %s = ? AND %s = ?',
            array(
                $this->user->userRolesTableName,
                $this->user->columnUserPrimaryKey,
                $this->user->columnUserRolePrimaryKey
            )
        );
        $this->db->bindValue(1, $userId, PARAM_INT);
        $this->db->bindValue(2, $roleId, PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Delete Assign User Roles
     * 
     * @param int $userId user id
     * 
     * @return object statement of Pdo
     */
    public function deleteRoleFromUsers($userId)
    {
        $this->db->prepare(
            'DELETE FROM %s WHERE %s = ?',
            array(
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserPrimaryKey)
            )
        );
        $this->db->bindValue(1, $userId, PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Run sql query
     * 
     * @param string $objectName object name.
     * @param string $permName   permission name.
     * @param string $extra      extra
     * 
     * @return array data otherwise false.
     */
    public function hasChildPermissionSqlQuery($objectName, $permName, $extra = '')
    {
        $roleIds = $this->user->getRoleIds();
        $this->db->prepare(
            'SELECT %s.%s,%s.%s,%s.%s,%s.%s,%s.%s
                FROM %s
                INNER JOIN %s
                ON %s.%s = %s.%s
                INNER JOIN %s
                ON %s.%s = %s.%s
                INNER JOIN %s
                ON %s.%s = %s.%s
                INNER JOIN %s
                ON %s.%s = %s.%s
                WHERE %s.%s = ?
                AND %s.%s IN (%s)
                AND %s.%s = ?
                AND %s.%s = ?
                AND %s.%s IN (%s)
                AND %s.%s IN (%s)
                AND %s.%s IN (SELECT %s FROM %s WHERE %s = ?)' . $extra,
            array(
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermText),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermPrimaryKey),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermRolePrimaryKey),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermResource),
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->columnOpText),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermPrimaryKey),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermPrimaryKey),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermRolePrimaryKey),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserRolePrimaryKey),
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermPrimaryKey),
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->columnRolePermPrimaryKey),
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->columnOpPermOpPrimaryKey),
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->columnOpPrimaryKey),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermResource),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermText),
                str_repeat('?,', count($permName) - 1) . '?',
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermType),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserPrimaryKey),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?',
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->columnOpRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?',
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermParentId),
                $this->db->protect($this->user->columnPermPrimaryKey),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermText)
            )
        );
        $i = 1;
        $this->db->bindValue($i++, $this->user->getResourceId(), PARAM_STR);
        foreach ($permName as $name) {
            $this->db->bindValue($i++, $name, PARAM_STR);
        }
        $this->db->bindValue($i++, 'object', PARAM_STR);
        $this->db->bindValue($i++, $this->user->getUserId(), PARAM_INT);

        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], PARAM_INT);
        }
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], PARAM_INT);
        }
        $this->db->bindValue($i, $objectName, PARAM_STR);
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Run sql query
     * 
     * @return array data otherwise false.
     */
    public function hasRoleSqlQuery()
    {
        $roleIds = $this->user->getRoleIds();
        $this->db->prepare(
            'SELECT %s.%s,%s.%s,%s.%s
                FROM %s
                INNER JOIN %s
                ON %s.%s = %s.%s
                WHERE %s.%s = ?
                AND %s.%s IN (%s)',
            array(
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserPrimaryKey),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserRolePrimaryKey),
                $this->db->protect($this->user->rolesTableName),
                $this->db->protect($this->user->columnRoleText),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->rolesTableName),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserRolePrimaryKey),
                $this->db->protect($this->user->rolesTableName),
                $this->db->protect($this->user->columnRolePrimaryKey),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserPrimaryKey),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?'
            )
        );
        $i = 1;
        $this->db->bindValue($i++, $this->user->getUserId(), PARAM_INT);
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], PARAM_INT);
        }
        $this->db->execute();

        return $this->db->rowArray();
    }

    /**
     * Run sql query
     * 
     * @return array data otherwise false.
     */
    public function getRolesSqlQuery()
    {
        $this->db->prepare(
            'SELECT %s
                FROM %s
                WHERE %s = ?',
            array(
                $this->db->protect($this->user->columnUserRolePrimaryKey),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserPrimaryKey)
            )
        );
        $this->db->bindValue(1, $this->user->getUserId(), PARAM_INT);
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Run sql query
     * 
     * @param string $roleIds role ids
     * 
     * @return array data otherwise false.
     */
    public function getPermissionsSqlQuery($roleIds)
    {
        $this->db->prepare(
            'SELECT
                %s.%s
                FROM
                %s
                WHERE %s IN (%s)',
            array(
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermPrimaryKey),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?'
            )
        );
        $i = 1;
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnRolePermRolePrimaryKey], PARAM_INT);
        }
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Run sql query
     * 
     * @param string $permResource permission page resource ('admin/advertising')
     * @param string $extra        extra sql data
     * 
     * @return array data otherwise false.
     */
    public function hasPagePermissionSqlQuery($permResource, $extra)
    {
        $roleIds = $this->user->getRoleIds();
        $this->db->prepare(
            'SELECT %s.%s,%s.%s,%s.%s,%s.%s,%s.%s
                FROM %s
                INNER JOIN %s
                ON %s.%s = %s.%s
                INNER JOIN %s
                ON %s.%s = %s.%s
                INNER JOIN %s
                ON %s.%s = %s.%s
                INNER JOIN %s
                ON %s.%s = %s.%s
                WHERE %s.%s = ?
                AND %s.%s = ?
                AND %s.%s = ?
                AND %s.%s IN (%s)
                AND %s.%s IN (%s)' . $extra,
            array(
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermText),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermPrimaryKey),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermRolePrimaryKey),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermResource),
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->columnOpText),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermPrimaryKey),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermPrimaryKey),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermRolePrimaryKey),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserRolePrimaryKey),
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermPrimaryKey),
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->columnRolePermPrimaryKey),
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->columnOpPermOpPrimaryKey),
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->columnOpPrimaryKey),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermResource),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermType),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserPrimaryKey),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?',
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->columnOpRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?'
            )
        );
        $this->db->bindValue(1, $permResource, PARAM_STR);
        $this->db->bindValue(2, 'page', PARAM_STR);
        $this->db->bindValue(3, $this->user->getUserId(), PARAM_INT);
        $i = 4;
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], PARAM_INT);
        }
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], PARAM_INT);
        }
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Run sql query
     * 
     * @param string $permName object name
     * @param string $extra    extra data
     * 
     * @return array data otherwise false
     */
    public function hasObjectPermissionSqlQuery($permName, $extra)
    {
        $roleIds = $this->user->getRoleIds();
        $this->db->prepare(
            'SELECT %s.%s,%s.%s,%s.%s,%s.%s,%s.%s
                FROM %s
                INNER JOIN %s
                ON %s.%s = %s.%s
                INNER JOIN %s
                ON %s.%s = %s.%s
                INNER JOIN %s
                ON %s.%s = %s.%s
                INNER JOIN %s
                ON %s.%s = %s.%s
                WHERE %s.%s = ?
                AND %s.%s IN (%s)
                AND %s.%s = ?
                AND %s.%s = ?
                AND %s.%s IN (%s)
                AND %s.%s IN (%s)' . $extra,
            array(
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermText),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermPrimaryKey),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermRolePrimaryKey),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermResource),
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->columnOpText),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermPrimaryKey),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermPrimaryKey),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermRolePrimaryKey),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserRolePrimaryKey),
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermPrimaryKey),
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->columnRolePermPrimaryKey),
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->columnOpPermOpPrimaryKey),
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->columnOpPrimaryKey),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermResource),
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermText),
                str_repeat('?,', count($permName) - 1) . '?',
                $this->db->protect($this->user->permTableName),
                $this->db->protect($this->user->columnPermType),
                $this->db->protect($this->user->userRolesTableName),
                $this->db->protect($this->user->columnUserPrimaryKey),
                $this->db->protect($this->user->rolePermTableName),
                $this->db->protect($this->user->columnRolePermRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?',
                $this->db->protect($this->user->opPermTableName),
                $this->db->protect($this->user->columnOpRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?'
            )
        );
        $i = 1;
        $this->db->bindValue($i++, $this->user->getResourceId(), PARAM_STR);
        foreach ($permName as $name) {
            $this->db->bindValue($i++, $name, PARAM_STR);
        }
        $this->db->bindValue($i++, 'object', PARAM_STR);
        $this->db->bindValue($i++, $this->user->getUserId(), PARAM_INT);

        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], PARAM_INT);
        }
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], PARAM_INT);
        }
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Run sql query
     * 
     * @return array data otherwise false
     */
    public function roleCountSqlQuery()
    {
        $this->db->prepare(
            'SELECT * FROM %s WHERE %s = ?',
            array(
                $this->user->userRolesTableName,
                $this->user->columnUserPrimaryKey
            )
        );
        $this->db->bindValue(1, $this->user->getUserId(), PARAM_INT);
        $this->db->execute();
        
        return $this->db->count();
    }
}

// END User.php File
/* End of file User.php

/* Location: .Obullo/Permissions/Rbac/Db/User.php */