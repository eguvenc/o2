<?php

namespace Obullo\Permissions\Rbac\Model;

use Pdo;

/**
 * Model User
 * 
 * @category  Model
 * @package   User
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/rbac
 */
class User
{
    /**
     * Database instance
     * 
     * @var object
     */
    public $db;

    /**
     * Permissions\Rbac\User instance
     * 
     * @var object
     */
    protected $user;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c    = $c;
        $this->user = $this->c['rbac.user'];
        $this->db   = $this->c['service provider database']->get(['connection' => 'rbac']);
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
        $this->db->bindValue(1, $userId, Pdo::PARAM_INT);
        $this->db->bindValue(2, $roleId, Pdo::PARAM_INT);
        $this->db->bindValue(3, time(), Pdo::PARAM_INT);

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
        $this->db->bindValue(1, $userId, Pdo::PARAM_INT);
        $this->db->bindValue(2, $roleId, Pdo::PARAM_INT);

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
        $this->db->bindValue(1, $userId, Pdo::PARAM_INT);

        return $this->db->execute();
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
        $this->db->bindValue($i++, $this->user->getId(), Pdo::PARAM_INT);
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], Pdo::PARAM_INT);
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
        $this->db->bindValue(1, $this->user->getId(), Pdo::PARAM_INT);
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
            $this->db->bindValue($i++, $id[$this->user->columnRolePermRolePrimaryKey], Pdo::PARAM_INT);
        }
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Run sql query
     * 
     * SQL QUERY:
     * *****************************************************************************************************************
     * SELECT  `rbac_permissions`.`rbac_permission_name`,                                                              *
     *         `rbac_permissions`.`rbac_permission_id`,                                                                *
     *         `rbac_role_permissions`.`rbac_roles_rbac_role_id`,                                                      *
     *         `rbac_permissions`.`rbac_permission_resource`,                                                          *
     *         `rbac_operations`.`rbac_operation_name`                                                                 *
     * FROM `rbac_permissions`                                                                                         *
     * INNER JOIN `rbac_role_permissions`                                                                              *
     *     ON `rbac_permissions`.`rbac_permission_id` = `rbac_role_permissions`.`rbac_permissions_rbac_permission_id`  *
     * INNER JOIN `rbac_user_roles`                                                                                    *
     *     ON `rbac_role_permissions`.`rbac_roles_rbac_role_id` = `rbac_user_roles`.`rbac_roles_rbac_role_id`          *
     * INNER JOIN `rbac_op_permissions`                                                                                *
     *     ON `rbac_permissions`.`rbac_permission_id` = `rbac_op_permissions`.`rbac_permissions_rbac_permission_id`    *
     * INNER JOIN `rbac_operations`                                                                                    *
     *     ON `rbac_op_permissions`.`rbac_operations_rbac_operation_id` = `rbac_operations`.`rbac_operation_id`        *
     * WHERE `rbac_permissions`.`rbac_permission_resource` = 'user/create'                                             *
     *     AND `rbac_permissions`.`rbac_permission_type` = 'page'                                                      *
     *     AND `rbac_user_roles`.`users_user_id` = 1                                                                   *
     *     AND `rbac_role_permissions`.`rbac_roles_rbac_role_id` IN (1)                                                *
     *     AND `rbac_op_permissions`.`rbac_roles_rbac_role_id` IN (1)                                                  *
     *     AND `rbac_operations`.`rbac_operation_name` IN ('view')                                                     *
     * *****************************************************************************************************************
     * 
     * @param mix $opName operation name
     * 
     * @return array data otherwise false.
     */
    public function hasPagePermissionSqlQuery($opName)
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
                AND %s.%s IN (%s)
                AND %s.%s IN (%s)',
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
                str_repeat('?,', count($roleIds) - 1) . '?',
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->columnOpText),
                str_repeat('?,', count($opName) - 1) . '?'
            )
        );
        $this->db->bindValue(1, $this->c['rbac.resource']->getId(), Pdo::PARAM_STR);
        $this->db->bindValue(2, 'page', Pdo::PARAM_STR);
        $this->db->bindValue(3, $this->user->getId(), Pdo::PARAM_INT);
        $i = 4;
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], Pdo::PARAM_INT);
        }
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], Pdo::PARAM_INT);
        }
        foreach ($opName as $op) {
            $this->db->bindValue($i++, $op, Pdo::PARAM_STR);
        }
        $this->db->execute();
        
        return $this->db->resultArray();
    }

    /**
     * Run sql query
     * 
     * SQL QUERY:
     * *****************************************************************************************************************
     * SELECT  `rbac_permissions`.`rbac_permission_name`,                                                              *
     *         `rbac_permissions`.`rbac_permission_id`,                                                                *
     *         `rbac_role_permissions`.`rbac_roles_rbac_role_id`,                                                      *
     *         `rbac_permissions`.`rbac_permission_resource`,                                                          *
     *         `rbac_operations`.`rbac_operation_name`                                                                 *
     * FROM `rbac_permissions`                                                                                         *
     * INNER JOIN `rbac_role_permissions`                                                                              *
     *     ON `rbac_permissions`.`rbac_permission_id` = `rbac_role_permissions`.`rbac_permissions_rbac_permission_id`  *
     * INNER JOIN `rbac_user_roles`                                                                                    *
     *     ON `rbac_role_permissions`.`rbac_roles_rbac_role_id` = `rbac_user_roles`.`rbac_roles_rbac_role_id`          *
     * INNER JOIN `rbac_op_permissions`                                                                                *
     *     ON `rbac_permissions`.`rbac_permission_id` = `rbac_op_permissions`.`rbac_permissions_rbac_permission_id`    *
     * INNER JOIN `rbac_operations`                                                                                    *
     *     ON `rbac_op_permissions`.`rbac_operations_rbac_operation_id` = `rbac_operations`.`rbac_operation_id`        *
     * WHERE `rbac_permissions`.`rbac_permission_resource` = 'users'                                                   *
     *     AND `rbac_permissions`.`rbac_permission_name` IN ('userCreateForm','userEditForm')                          *
     *     AND `rbac_permissions`.`rbac_permission_type` = 'object'                                                    *
     *     AND `rbac_user_roles`.`users_user_id` = 1                                                                   *
     *     AND `rbac_role_permissions`.`rbac_roles_rbac_role_id` IN (1)                                                *
     *     AND `rbac_op_permissions`.`rbac_roles_rbac_role_id` IN (1)                                                  *
     *     AND `rbac_operations`.`rbac_operation_name` IN ('view','insert')                                            *
     * *****************************************************************************************************************
     * 
     * @param array $permName object name
     * @param array $opName   operation name
     * 
     * @return array data otherwise false
     */
    public function hasObjectPermissionSqlQuery($permName, $opName)
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
                AND %s.%s IN (%s)',
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
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->columnOpText),
                str_repeat('?,', count($opName) - 1) . '?'
            )
        );
        $i = 1;
        $this->db->bindValue($i++, $this->c['rbac.resource']->getId(), Pdo::PARAM_STR);
        foreach ($permName as $name) {
            $this->db->bindValue($i++, $name, Pdo::PARAM_STR);
        }
        $this->db->bindValue($i++, 'object', Pdo::PARAM_STR);
        $this->db->bindValue($i++, $this->user->getId(), Pdo::PARAM_INT);

        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], Pdo::PARAM_INT);
        }
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], Pdo::PARAM_INT);
        }
        foreach ($opName as $op) {
            $this->db->bindValue($i++, $op, Pdo::PARAM_STR);
        }
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Run sql query
     *
     * SQL QUERY:
     * *****************************************************************************************************************
     * SELECT  `rbac_permissions`.`rbac_permission_name`,                                                              *
     *         `rbac_permissions`.`rbac_permission_id`,                                                                *
     *         `rbac_role_permissions`.`rbac_roles_rbac_role_id`,                                                      *
     *         `rbac_permissions`.`rbac_permission_resource`,                                                          *
     *         `rbac_operations`.`rbac_operation_name`                                                                 *
     * FROM `rbac_permissions`                                                                                         *
     * INNER JOIN `rbac_role_permissions`                                                                              *
     *     ON `rbac_permissions`.`rbac_permission_id` = `rbac_role_permissions`.`rbac_permissions_rbac_permission_id`  *
     * INNER JOIN `rbac_user_roles`                                                                                    *
     *     ON `rbac_role_permissions`.`rbac_roles_rbac_role_id` = `rbac_user_roles`.`rbac_roles_rbac_role_id`          *
     * INNER JOIN `rbac_op_permissions`                                                                                *
     *     ON `rbac_permissions`.`rbac_permission_id` = `rbac_op_permissions`.`rbac_permissions_rbac_permission_id`    *
     * INNER JOIN `rbac_operations`                                                                                    *
     *     ON `rbac_op_permissions`.`rbac_operations_rbac_operation_id` = `rbac_operations`.`rbac_operation_id`        *
     * WHERE `rbac_permissions`.`rbac_permission_resource` = 'user/create'                                             *
     *     AND `rbac_permissions`.`rbac_permission_name` IN ('username','password')                                    *
     *     AND `rbac_permissions`.`rbac_permission_type` = 'object'                                                    *
     *     AND `rbac_user_roles`.`users_user_id` = 1                                                                   *
     *     AND `rbac_role_permissions`.`rbac_roles_rbac_role_id` IN (1)                                                *
     *     AND `rbac_op_permissions`.`rbac_roles_rbac_role_id` IN (1)                                                  *
     *     AND `rbac_permissions`.`rbac_permission_parent_id` IN                                                       *
     *     (SELECT `rbac_permission_id` FROM `rbac_permissions` WHERE `rbac_permission_name` = 'userCreateForm')       *
     *     AND `rbac_operations`.`rbac_operation_name` IN ('view')                                                     *
     * *****************************************************************************************************************
     * 
     * @param string $objectName object name.
     * @param array  $permName   permission name.
     * @param array  $opName     operation name
     * 
     * @return array data otherwise false.
     */
    public function hasElementPermissionSqlQuery($objectName, $permName, $opName)
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
                AND %s.%s IN (SELECT %s FROM %s WHERE %s = ?) AND %s.%s IN (%s)',
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
                $this->db->protect($this->user->columnPermText),
                $this->db->protect($this->user->opTableName),
                $this->db->protect($this->user->columnOpText),
                str_repeat('?,', count($opName) - 1) . '?',
            )
        );
        $i = 1;
        $this->db->bindValue($i++, $this->c['rbac.resource']->getId(), Pdo::PARAM_STR);
        foreach ($permName as $name) {
            $this->db->bindValue($i++, $name, Pdo::PARAM_STR);
        }
        $this->db->bindValue($i++, 'object', Pdo::PARAM_STR);
        $this->db->bindValue($i++, $this->user->getId(), Pdo::PARAM_INT);

        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], Pdo::PARAM_INT);
        }
        foreach ($roleIds as $id) {
            $this->db->bindValue($i++, $id[$this->user->columnUserRolePrimaryKey], Pdo::PARAM_INT);
        }
        $this->db->bindValue($i++, $objectName, Pdo::PARAM_STR);

        foreach ($opName as $op) {
            $this->db->bindValue($i++, $op, Pdo::PARAM_STR);
        }

        $this->db->execute();
        // echo $this->db->lastQuery();
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
        $this->db->bindValue(1, $this->user->getId(), Pdo::PARAM_INT);
        $this->db->execute();
        
        return $this->db->count();
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


// END User.php File
/* End of file User.php

/* Location: .Obullo/Permissions/Rbac/Model/User.php */
