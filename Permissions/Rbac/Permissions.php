<?php

namespace Obullo\Permissions\Rbac;

use Obullo\Tree\Db,
    Obullo\Permissions\Rbac\User;

/*
Alternatively, the above could be written:
allow('guest', null, 'view');
array('edit', 'submit', 'revise'));
array('publish', 'archive', 'delete'));
*/

/**
 * User Permissions
 * 
 * @category  Permissions
 * @package   Permissions
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
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
     * Table constants
     */
    const TABLENAME                  = 'db.tablename';
    const OP_PERM_TABLENAME          = 'db.tablename';
    const OP_PERM_PRIMARY_KEY        = 'db.perm_primary_key';
    const OP_PERM_OP_PRIMARY_KEY     = 'db.op_primary_key';
    const OP_PERM_ROLE_PRIMARY_KEY   = 'db.role_primary_key';
    const ROLE_PERM_TABLENAME        = 'db.tablename';
    const ROLE_PERM_ROLE_PRIMARY_KEY = 'db.role_primary_key';
    const ROLE_PRIMARY_KEY           = 'db.role_primary_key';
    const ROLE_PERM_PRIMARY          = 'db.perm_primary_key';
    const PARENT_ID                  = 'db.parent_id';
    const PRIMARY_KEY                = 'db.primary_key';
    const PERM_TYPE                  = 'db.type';
    const ROLE_ID                    = 'db.primary_key';
    const RESOURCE                   = 'db.resource';
    const TEXT                       = 'db.text';
    const LEFT                       = 'db.left';
    const RIGHT                      = 'db.right';
    const PERM_MODULE_NAME           = 'db.perm_module_name';
    const ASSIGNMENT_DATE            = 'db.assignment_date';

    /**
     * Cache constants
     */
    const CACHE_GET_PERMISSONS = 'Permissions:Rbac:Permissions:getPermissions'; // Redis supported key format.
    const CACHE_GET_ROOT       = 'Permissions:Rbac:Permissions:getRoot';
    const CACHE_GET_ROLES      = 'Permissions:Rbac:Permissions:getRoles';
    const CACHE_GET_SIBLINGS   = 'Permissions:Rbac:Permissions:getSiblings:';

    /**
     * Permission Tablename
     * 
     * @var string
     */
    public $permTableName = 'rbac_permissions';

    /**
     * Role-Permission Tablename
     * 
     * @var string
     */
    public $rolePermTableName = 'rbac_role_permissions';

    /**
     * Primary key column name
     * 
     * @var string
     */
    public $primaryKey = 'perm_id';

    /**
     * Parent id column name
     * 
     * @var string
     */
    public $parentId = 'parent_id';

    /**
     * Role id column name
     * 
     * @var string
     */
    public $roleId = 'role_id';

    /**
     * Left column name
     * 
     * @var string
     */
    public $lft = 'lft';

    /**
     * Right column name
     * 
     * @var string
     */
    public $rgt = 'rgt';

    /**
     * Text column name
     * 
     * @var string
     */
    public $text = 'perm_name';

    /**
     * Resource id column name
     * 
     * @var string
     */
    public $resource = 'perm_resource_id';

    /**
     * Column name assignment date
     * 
     * @var string
     */
    public $columnAssignmentDate = 'assignment_date';

    /**
     * Tree Db object
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
     * Db object
     * 
     * @var object
     */
    public $db;

    /**
     * Constructor
     *
     * @param object $c      container
     * @param array  $params parameter
     */
    public function __construct($c, $params = array())
    {
        $this->c = $c;
        $this->treeDb = new Db($c);
        $this->cache = $c->load('service/cache');
        $this->db = $c->load('return db');

        if (count($params) > 0) {
            $this->permTableName          = $params['permissions'][static::TABLENAME];
            $this->opPermTableName        = $params['op_permissions'][static::OP_PERM_TABLENAME];
            $this->opPermPermPrimaryKey   = $params['op_permissions'][static::OP_PERM_PRIMARY_KEY];
            $this->opPermOpPrimaryKey     = $params['op_permissions'][static::OP_PERM_OP_PRIMARY_KEY];
            $this->opPermRolePrimaryKey   = $params['op_permissions'][static::OP_PERM_ROLE_PRIMARY_KEY];
            $this->rolePermTableName      = $params['role_permissions'][static::ROLE_PERM_TABLENAME];
            $this->rolePermRolePrimaryKey = $params['role_permissions'][static::ROLE_PERM_ROLE_PRIMARY_KEY];
            $this->assignmentDate         = $params['role_permissions'][static::ASSIGNMENT_DATE];
            $this->rolePrimaryKey         = $params['role_permissions'][static::ROLE_PRIMARY_KEY];
            $this->rolePermPrimaryKey     = $params['role_permissions'][static::ROLE_PERM_PRIMARY];
            $this->primaryKey             = $params['permissions'][static::PRIMARY_KEY];
            $this->parentId               = $params['permissions'][static::PARENT_ID];
            $this->resource               = $params['permissions'][static::RESOURCE];
            $this->permType               = $params['permissions'][static::PERM_TYPE];
            $this->text                   = $params['permissions'][static::TEXT];
            $this->lft                    = $params['permissions'][static::LEFT];
            $this->rgt                    = $params['permissions'][static::RIGHT];
        }
        $this->treeDb->setTablename($this->permTableName);
        $this->treeDb->setPrimaryKey($this->primaryKey);
        $this->treeDb->setParentId($this->parentId);
        $this->treeDb->setText($this->text);
        $this->treeDb->setLft($this->lft);
        $this->treeDb->setRgt($this->rgt);
    }
    
    /**
     * Add root
     * 
     * @param string $permName perm name
     * @param array  $extra    extra data
     * 
     * @return void
     */
    public function addRoot($permName, $extra = array())
    {
        $this->deleteCache();
        $this->treeDb->addTree((string)$permName, (array)$extra);
    }

    /**
     * Add node
     * 
     * @param int   $permId   perm id
     * @param int   $permName perm name
     * @param array $extra    extra data
     * 
     * @return void
     */
    public function add($permId, $permName, $extra = array())
    {
        $this->deleteCache();
        $this->treeDb->addChild((int)$permId, (string)$permName, (array)$extra);
    }

    /**
     * Append node
     * 
     * @param int   $permId   perm id
     * @param int   $permName perm name
     * @param array $extra    extra data
     * 
     * @return void
     */
    public function append($permId, $permName, $extra = array())
    {
        $this->deleteCache();
        $this->treeDb->appendChild((int)$permId, (string)$permName, (array)$extra);
    }

    /**
     * Delete permission
     * 
     * @param int $permId perm id
     * 
     * @return void
     */
    public function delete($permId)
    {
        $permIds = $this->treeDb->getTree((int)$permId);

        $this->deleteCache();
        $this->deAssignAllOperations($permIds);
        $this->deAssignRoles($permIds);
        $this->treeDb->deleteNode((int)$permId);
    }

    /**
     * Update permission
     * 
     * @param int   $permId role id
     * @param array $data   data
     * 
     * @return boolean
     */
    public function update($permId, $data = array())
    {
        $this->deleteCache();
        $this->treeDb->updateNode($permId, $data);
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
        $this->deleteCache();

        $this->db->prepare(
            'INSERT INTO %s (%s,%s,%s) VALUES (?,?,?)',
            array(
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->rolePrimaryKey),
                $this->db->protect($this->rolePermPrimaryKey),
                $this->db->protect($this->assignmentDate)
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
        $this->deleteCache();

        $this->db->prepare(
            'INSERT INTO %s (%s,%s,%s) VALUES (?,?,?)',
            array(
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->opPermPermPrimaryKey),
                $this->db->protect($this->opPermOpPrimaryKey),
                $this->db->protect($this->opPermRolePrimaryKey)
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
        $this->deleteCache();

        $this->db->prepare(
            'DELETE FROM %s WHERE %s = ? AND %s = ?',
            array(
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->rolePrimaryKey),
                $this->db->protect($this->rolePermPrimaryKey)
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
            $permId = array(array($this->primaryKey => $permId));
        }
        $permId = array_reverse($permId);

        $this->deleteCache();
        $this->db->prepare(
            'DELETE FROM %s WHERE %s IN (%s)',
            array(
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->rolePermPrimaryKey),
                str_repeat('?,', count($permId) - 1) . '?'
            )
        );
        $i = 0;
        foreach ($permId as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->primaryKey], PARAM_INT);
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
        $this->deleteCache();

        $this->db->prepare(
            'DELETE FROM %s WHERE %s = ? AND %s = ? AND %s = ?',
            array(
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->opPermPermPrimaryKey),
                $this->db->protect($this->opPermOpPrimaryKey),
                $this->db->protect($this->opPermRolePrimaryKey)
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
        $this->deleteCache();

        $this->db->prepare(
            'DELETE FROM %s WHERE %s = ? AND %s = ?',
            array(
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->opPermPermPrimaryKey),
                $this->db->protect($this->opPermRolePrimaryKey),
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
            $permId = array(array($this->primaryKey => $permId));
        }
        $permId = array_reverse($permId);

        $this->deleteCache();
        $this->db->prepare(
            'DELETE FROM %s WHERE %s IN (%s)',
            array(
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->opPermPermPrimaryKey),
                str_repeat('?,', count($permId) - 1) . '?'
            )
        );
        $i = 0;
        foreach ($permId as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->primaryKey], PARAM_INT);
        }
        return $this->db->execute();
    }

    /**
     * Get all roles
     * 
     * @param string $select     select column
     * @param int    $expiration expiration time
     * 
     * @return array
     */
    public function getPermissions($select = null, $expiration = 7200)
    {
        $select      = ($select == null) ? $this->primaryKey . ',' . $this->text . ',' . $this->resource : $select;
        $key         = static::CACHE_GET_PERMISSONS . $select;
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

        if ($resultArray == false) {  // If not exist in the cache
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
     * @param int    $permId     perm id
     * @param string $select     select column
     * @param int    $expiration expiration time
     * 
     * @return array
     */
    public function getSiblings($permId, $select = null, $expiration = 7200)
    {
        $key         = static::CACHE_GET_SIBLINGS . $permId;
        $select      = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $resultArray = $this->cache->get($key);
        
        if ($resultArray == false) {  // If not exist in the cache
            $queryResultArray = $this->treeDb->getSiblings((int)$permId, (string)$select); // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
    }

    /**
     * Get roles
     * 
     * @param int    $permId     perm id
     * @param string $select     select column
     * @param string $expiration cache
     * 
     * @return array
     */
    public function getRoles($permId, $select = null, $expiration = 7200)
    {
        $key         = static::CACHE_GET_ROLES . $permId;
        $select      = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $resultArray = $this->cache->get($key);
        if ($resultArray == false) {  // If not exist in the cache
            $queryResultArray = $this->getRolesSqlQuery((int)$permId, (string)$select); // do sql query
            $resultArray = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
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
        $db = $this->c->load('db');
        $db->prepare(
            'SELECT %s FROM %s WHERE %s = ?',
            array(
                $db->protect($this->rolePermRolePrimaryKey),
                $db->protect($this->rolePermTableName),
                $db->protect($this->rolePermPrimaryKey)
            )
        );
        $db->bindValue(1, $permId, PARAM_INT);
        $db->execute();
        return $db->resultArray();
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
     * Delete cache
     * 
     * @return void
     */
    public function deleteCache()
    {
        $keys = $this->cache->getAllKeys('Permissions:*');
        $this->cache->delete($keys);
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


// END Permissions.php File
/* End of file Permissions.php

/* Location: .Obullo/Permissions/Rbac/Permissions.php */