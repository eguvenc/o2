<?php

namespace Obullo\Permissions\Rbac;

use Obullo\Tree\Db,
    Obullo\Permissions\Rbac\User;

/**
 * Roles
 * 
 * @category  Permissions
 * @package   Rbac
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
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
     * Table Constants
     */
    const TABLENAME                  = 'db.tablename';
    const PERM_PRIMARY_KEY           = 'db.primary_key';
    const USER_PRIMARY_KEY           = 'db.user_primary_key';
    const ROLE_PRIMARY_KEY           = 'db.role_primary_key';
    const PERM_TABLENAME             = 'db.tablename';
    const PERM_TEXT                  = 'db.text';
    const ROLE_PERM_TABLENAME        = 'db.tablename';
    const ROLE_PERM_ROLE_PRIMARY_KEY = 'db.role_primary_key';
    const ROLE_PERM_PRIMARY          = 'db.perm_primary_key';
    const OP_PERMS_ROLE_PRIMARY_KEY  = 'db.role_primary_key';
    const PRIMARY_KEY                = 'db.primary_key';
    const PARENT_ID                  = 'db.parent_id';
    const TYPE                       = 'db.type';
    const TEXT                       = 'db.text';
    const LEFT                       = 'db.left';
    const RIGHT                      = 'db.right';

    /**
     * Cache constants
     * Redis supported key format.
     */
    const CACHE_GET_ROLES       = 'Permissions:Rbac:Roles:getRoles'; 
    const CACHE_GET_USERS       = 'Permissions:Rbac:Roles:getUsers';
    const CACHE_GET_ROOT        = 'Permissions:Rbac:Roles:getRoot';
    const CACHE_GET_PERMISSIONS = 'Permissions:Rbac:Roles:getPermissions';
    const CACHE_GET_SIBLINGS    = 'Permissions:Rbac:Roles:getSiblings:';

    /**
     * Tablename
     * 
     * @var string
     */
    public $tableName = 'rbac_roles';

    /**
     * User roles Tablename
     * 
     * @var string
     */
    public $userRolesTableName = 'rbac_user_roles';

    /**
     * Column name user primary key
     * 
     * @var string
     */
    public $columnUserPrimaryKey = 'user_id';

    /**
     * Column name role primary key
     * 
     * @var string
     */
    public $columnUserRolePrimaryKey = 'role_id';

    /**
     * Column name primary key
     * 
     * @var string
     */
    public $primaryKey = 'role_id';

    /**
     * Column name parent id
     * 
     * @var string
     */
    public $parentId = 'parent_id';

    /**
     * Column name text
     * 
     * @var string
     */
    public $text = 'role_name';

    /**
     * Column name role type
     * 
     * @var string
     */
    public $type = 'role_type';

    /**
     * Column name lft
     * 
     * @var string
     */
    public $lft = 'lft';

    /**
     * Column name rgt
     * 
     * @var string
     */
    public $rgt = 'rgt';

    /**
     * TreeDb object
     * 
     * @var object
     */
    public $treeDb;

    /**
     * Cache object
     * 
     * @var object
     */
    public $cache;

    /**
     * Constructor
     *
     * @param object $c      container
     * @param object $db     database object
     * @param array  $config parameters
     */
    public function __construct($c, $db, $config = array())
    {
        $this->c = $c;
        $this->db = $db;
        $this->cache  = $c->load('service/cache');
        $this->treeDb = new Db($c, array('db' => $db));
        
        $this->c->config->load('constants/rbac');  // load rbac constants
        
        $columns = $config['database']['columns'];

        if (count($columns) > 0) {
            $this->tableName  = $columns['roles'][static::TABLENAME];
            $this->primaryKey = $columns['roles'][static::PRIMARY_KEY];
            $this->parentId   = $columns['roles'][static::PARENT_ID];
            $this->text       = $columns['roles'][static::TEXT];
            $this->type       = $columns['roles'][static::TYPE];
            $this->lft        = $columns['roles'][static::LEFT];
            $this->rgt        = $columns['roles'][static::RIGHT];
            $this->userRolesTableName       = $columns['user_roles'][static::TABLENAME];
            $this->columnUserPrimaryKey     = $columns['user_roles'][static::USER_PRIMARY_KEY];
            $this->permtableName            = $columns['permissions'][static::PERM_TABLENAME];
            $this->permPrimaryKey           = $columns['permissions'][static::PERM_PRIMARY_KEY];
            $this->permText                 = $columns['permissions'][static::PERM_TEXT];
            $this->columnUserRolePrimaryKey = $columns['user_roles'][static::ROLE_PRIMARY_KEY];
            $this->rolePermTableName        = $columns['role_permissions'][static::ROLE_PERM_TABLENAME];
            $this->rolePermRolePrimaryKey   = $columns['role_permissions'][static::ROLE_PERM_ROLE_PRIMARY_KEY];
            $this->rolePermPrimaryKey       = $columns['role_permissions'][static::ROLE_PERM_PRIMARY];
            $this->opPermsTableName         = $columns['op_permissions'][static::TABLENAME];
            $this->opPermsRolePrimaryKey    = $columns['op_permissions'][static::OP_PERMS_ROLE_PRIMARY_KEY];
        }
        
        $this->treeDb->setTablename($this->tableName);
        $this->treeDb->setPrimaryKey($this->primaryKey);
        $this->treeDb->setParentId($this->parentId);
        $this->treeDb->setText($this->text);
        $this->treeDb->setLft($this->lft);
        $this->treeDb->setRgt($this->rgt);
    }
    
    /**
     * Add root
     * 
     * @param string $roleName role name
     * @param array  $extra    extra data
     * 
     * @return void
     */
    public function addRoot($roleName, $extra = array())
    {
        $this->deleteCache();
        $this->treeDb->addTree((string)$roleName, (array)$extra);
    }

    /**
     * Add node
     * 
     * @param int   $roleId   role id
     * @param int   $roleName role name
     * @param array $extra    extra data
     * 
     * @return void
     */
    public function add($roleId, $roleName, $extra = array())
    {
        $this->deleteCache();
        $this->treeDb->addChild((int)$roleId, (string)$roleName, (array)$extra);
    }

    /**
     * Append node
     * 
     * @param int   $roleId   role id
     * @param int   $roleName role name
     * @param array $extra    extra data
     * 
     * @return void
     */
    public function append($roleId, $roleName, $extra = array())
    {
        $this->deleteCache();
        $this->treeDb->appendChild((int)$roleId, (string)$roleName, (array)$extra);
    }

    /**
     * Move as first node
     * 
     * @param int $sourceId source role id
     * @param int $targetId target role id
     * 
     * @return void
     */
    public function moveAsFirst($sourceId, $targetId)
    {
        $this->deleteCache();
        $this->treeDb->moveAsFirstChild((int)$sourceId, (string)$targetId);
    }

    /**
     * Move as last node
     * 
     * @param int $sourceId source role id
     * @param int $targetId target role id
     * 
     * @return void
     */
    public function moveAsLast($sourceId, $targetId)
    {
        $this->deleteCache();
        $this->treeDb->moveAsLastChild((int)$sourceId, (string)$targetId);
    }

    /**
     * Move as next sibling
     * 
     * @param int $sourceId source role id
     * @param int $targetId target role id
     * 
     * @return void
     */
    public function moveAsNextSibling($sourceId, $targetId)
    {
        $this->deleteCache();
        $this->treeDb->moveAsNextSibling((int)$sourceId, (string)$targetId);
    }

    /**
     * Move as prev sibling
     * 
     * @param int $sourceId source role id
     * @param int $targetId target role id
     * 
     * @return void
     */
    public function moveAsPrevSibling($sourceId, $targetId)
    {
        $this->deleteCache();
        $this->treeDb->moveAsPrevSibling((int)$sourceId, (string)$targetId);
    }

    /**
     * Get all roles
     * 
     * @param string $select     select column
     * @param int    $expiration expiration time
     * 
     * @return array
     */
    public function getRoles($select = null, $expiration = 7200)
    {
        $key         = static::CACHE_GET_ROLES;
        $select      = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $resultArray = $this->cache->get($key);
        if ($resultArray == false) {  // If not exist in the cache
            $queryResultArray = $this->treeDb->getAllTree((string)$select); // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
    }

    /**
     * Get root
     * 
     * @param string $select     select column
     * @param int    $expiration expiration time
     * 
     * @return array
     */
    public function getRoot($select = null, $expiration = 7200)
    {
        $key         = static::CACHE_GET_ROOT;
        $select      = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $resultArray = $this->cache->get($key);

        if ($resultArray == false) { // If not exist in the cache
            $queryResultArray = $this->treeDb->getRoot((string)$select); // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
    }

    /**
     * Get siblings
     * 
     * @param int    $roleId     role id
     * @param string $select     select column
     * @param string $expiration cache
     * 
     * @return array
     */
    public function getSiblings($roleId, $select = null, $expiration = 7200)
    {
        $key         = static::CACHE_GET_SIBLINGS . $roleId;
        $select      = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $resultArray = $this->cache->get($key);
        if ($resultArray == false) {  // If not exist in the cache
            $queryResultArray = $this->treeDb->getSiblings((int)$roleId, (string)$select); // do sql query
            $resultArray = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
    }

    /**
     * Get users
     * 
     * @param int    $roleId     role id
     * @param string $select     select column
     * @param string $expiration cache
     * 
     * @return array
     */
    public function getUsers($roleId, $select = null, $expiration = 7200)
    {
        $key         = static::CACHE_GET_USERS . $roleId;
        $select      = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $resultArray = $this->cache->get($key);
        if ($resultArray == false) {  // If not exist in the cache
            $queryResultArray = $this->getUsersSqlQuery((int)$roleId, (string)$select); // do sql query
            $resultArray = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
    }

    /**
     * Get permissions
     * 
     * @param int    $roleId     role id
     * @param string $select     select column
     * @param string $expiration cache
     * 
     * @return array
     */
    public function getPermissions($roleId, $select = null, $expiration = 7200)
    {
        $key         = static::CACHE_GET_PERMISSIONS . $roleId;
        $select      = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $resultArray = $this->cache->get($key);
        if ($resultArray == false) {  // If not exist in the cache
            $queryResultArray = $this->getPermissionsSqlQuery((int)$roleId, (string)$select); // do sql query
            $resultArray = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
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
        $db = $this->c->load('db');
        $db->prepare(
            'SELECT %s FROM %s WHERE %s = ?',
            array(
                $db->protect($this->rolePermPrimaryKey),
                $db->protect($this->rolePermTableName),
                $db->protect($this->rolePermRolePrimaryKey)
            )
        );
        $db->bindValue(1, $roleId, PARAM_INT);
        $db->execute();
        return $db->resultArray();
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
        $db = $this->c->load('db');
        $db->prepare(
            'SELECT %s FROM %s WHERE %s = ?',
            array(
                $db->protect($this->columnUserPrimaryKey),
                $db->protect($this->userRolesTableName),
                $db->protect($this->columnUserRolePrimaryKey)
            )
        );
        $db->bindValue(1, $roleId, PARAM_INT);
        $db->execute();
        return $db->resultArray();
    }

    /**
     * Update node
     * 
     * @param int   $roleId role id
     * @param array $data   data
     * 
     * @return boolean
     */
    public function update($roleId, $data = array())
    {
        $this->deleteCache();
        return $this->treeDb->updateNode($roleId, $data);
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
            $roleId = array(array($this->primaryKey => $roleId));
        }
        $roleId = array_reverse($roleId);

        $this->deleteCache();
        $this->db->prepare(
            'DELETE FROM %s WHERE %s IN (%s)',
            array(
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->rolePermRolePrimaryKey),
                str_repeat('?,', count($roleId) - 1) . '?'
            )
        );
        $i = 0;
        foreach ($roleId as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->primaryKey], PARAM_INT);
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
            $roleId = array(array($this->primaryKey => $roleId));
        }
        $roleId = array_reverse($roleId);

        $this->deleteCache();
        $this->db->prepare(
            'DELETE FROM %s WHERE %s IN (%s)',
            array(
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserRolePrimaryKey),
                str_repeat('?,', count($roleId) - 1) . '?'
            )
        );
        $i = 0;
        foreach ($roleId as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->primaryKey], PARAM_INT);
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
            $roleId = array(array($this->primaryKey => $roleId));
        }
        $roleId = array_reverse($roleId);

        $this->deleteCache();
        $this->db->prepare(
            'DELETE FROM %s WHERE %s IN (%s)',
            array(
                $this->db->protect($this->opPermsTableName),
                $this->db->protect($this->opPermsRolePrimaryKey),
                str_repeat('?,', count($roleId) - 1) . '?'
            )
        );
        $i = 0;
        foreach ($roleId as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->primaryKey], PARAM_INT);
        }
        return $this->db->execute();
    }

    /**
     * Remove permission
     * 
     * @param int $roleId role id
     * 
     * @return boolean
     */
    public function delete($roleId)
    {
        $this->deleteCache();
        $this->deleteOperationsByRoleId((int)$roleId);
        $this->deleteRoleFromUsers((int)$roleId);
        $this->deleteRolePermissions((int)$roleId);
        $this->treeDb->deleteNode((int)$roleId);
    }

    /**
     * Delete cache
     * 
     * @return void
     */
    public function deleteCache()
    {
        $this->cache->delete(User::CACHE_GET_ROLES);
        $this->cache->delete(User::CACHE_HAS_PAGE_PERMISSION);
        $this->cache->delete(User::CACHE_HAS_OBJECT_PERMISSION);
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

/* Location: .Obullo/Permissions/Rbac/Roles.php */