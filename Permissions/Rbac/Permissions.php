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
     * Cache constants
     * Redis supported key format.
     */
    const CACHE_GET_PERMISSONS = 'Permissions:Rbac:Permissions:getPermissions';
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
     * Permissions\Rbac\Db\Permissions instance
     * 
     * @var null
     */
    protected static $dbPerms = null;

    /**
     * Constructor
     *
     * @param object $c      container
     * @param object $db     database object
     * @param array  $config parameters
     */
    public function __construct($c, $db, $config = array())
    {
        $this->c      = $c;
        $this->db     = $db;
        $this->cache  = $c->load('return service/cache');
        $this->treeDb = new Db($c, array('db' => $db));

        $this->c['config']->load('constants/rbac');  // load rbac constants

        $columns = $config['database']['columns'];

        if (count($columns) > 0) {

            // RBAC "permissions" table variable definations
            $this->permTableName          = RBAC_PERM_DB_TABLENAME;
            $this->primaryKey             = RBAC_PERM_COLUMN_PRIMARY_KEY;
            $this->parentId               = RBAC_PERM_COLUMN_PARENT_ID;
            $this->resource               = RBAC_PERM_COLUMN_RESOURCE;
            $this->permType               = RBAC_PERM_COLUMN_TYPE;
            $this->text                   = RBAC_PERM_COLUMN_TEXT;
            $this->lft                    = RBAC_PERM_COLUMN_LEFT;
            $this->rgt                    = RBAC_PERM_COLUMN_RIGHT;

            // RBAC "op_permissions" table variable definations
            $this->opPermTableName        = RBAC_OP_PERM_DB_TABLENAME;
            $this->opPermOpPrimaryKey     = RBAC_OP_PERM_TABLE_OP_PRIMARY_KEY;
            $this->opPermPermPrimaryKey   = RBAC_OP_PERM_TABLE_PERM_PRIMARY_KEY;
            $this->opPermRolePrimaryKey   = RBAC_OP_PERM_TABLE_ROLE_PRIMARY_KEY;

            // RBAC "role_permissions" table variable definations
            $this->rolePermTableName      = RBAC_ROLE_PERM_DB_TABLENAME;
            $this->rolePermRolePrimaryKey = RBAC_ROLE_PERM_TABLE_ROLES_PRIMARY_KEY;
            $this->rolePermPrimaryKey     = RBAC_ROLE_PERM_TABLE_PERM_PRIMARY_KEY;
            $this->assignmentDate         = RBAC_ROLE_PERM_COLUMN_ASSIGNMENT_DATE;
        }
        $this->treeDb->setTablename($this->permTableName);
        $this->treeDb->setPrimaryKey($this->primaryKey);
        $this->treeDb->setParentId($this->parentId);
        $this->treeDb->setText($this->text);
        $this->treeDb->setLft($this->lft);
        $this->treeDb->setRgt($this->rgt);
    }

    /**
     * Rbac Db User
     * 
     * @return Permissions\Rbac\Db\User object
     */
    protected static function dbPerms()
    {
        if (static::$dbPerms == null) {
            static::$dbPerms = new Obullo\Permissions\Rbac\Db\Permissions($this);
        }
        return static::$dbPerms;
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

        return static::dbPerms()->assignRole($roleId, $permId);
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

        return static::dbPerms()->assignOperation($roleId, $permId, $opId);
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

        return static::dbPerms()->deAssignRole($roleId, $permId);
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

        return static::dbPerms()->deAssignRoles($permId);
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

        return static::dbPerms()->deAssignOperation($roleId, $permId, $opId);
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

        return static::dbPerms()->deAssignOperations($roleId, $permId);
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

        return static::dbPerms()->deAssignAllOperations($permId);
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
            $queryResultArray = static::dbPerms()->getRolesSqlQuery((int)$permId, (string)$select); // do sql query
            $resultArray = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
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