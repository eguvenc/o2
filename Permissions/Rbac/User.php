<?php

namespace Obullo\Permissions\Rbac;

use Closure,
    RuntimeException;

/**
 * User Roles
 * 
 * @category  Permissions
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
     * Cache constants
     * Redis supported key format.
     */
    const CACHE_HAS_ELEMENT_PERMISSIONS = 'Permissions:Rbac:User:hasElementPermissions:';   // Obullo\Permissions\Rbac\Element->getPermissions();
    const CACHE_HAS_OBJECT_PERMISSION   = 'Permissions:Rbac:User:hasObjectPermissions:';    // Obullo\Permissions\Rbac\Object->getPermissions();

    const CACHE_HAS_PAGE_PERMISSION   = 'Permissions:Rbac:User:hasPagePermission:';
    const CACHE_GET_ALL_PERMISSIONS   = 'Permissions:Rbac:User:getAllPermissions:';
    const CACHE_GET_OPERATIONS        = 'Permissions:Rbac:User:getOperations:';
    const CACHE_GET_ROLES             = 'Permissions:Rbac:User:getRoles:';
    const CACHE_ROLE_COUNT            = 'Permissions:Rbac:User:roleCount:';
    const CACHE_HAS_ROLE              = 'Permissions:Rbac:User:hasRole:';

    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Tablename
     * 
     * @var string
     */
    public $tableName = 'rbac_user_roles';

    /**
     * User id column name
     * 
     * @var string
     */
    public $columnUserId = 'user_id';

    /**
     * Role id column name
     * 
     * @var string
     */
    public $columnRoleId = 'role_id';

    /**
     * Roles Tablename
     * 
     * @var string
     */
    public $rolesTableName = 'rbac_roles';

    /**
     * Column name text
     * 
     * @var string
     */
    public $columnRoleText = 'role_name';

    /**
     * Column name assignment date
     * 
     * @var string
     */
    public $columnAssignmentDate = 'assignment_date';

    /**
     * Column name assignment date
     * 
     * @var string
     */
    public $columnPermType = 'perm_type';

    /**
     * Column name role perm id
     * 
     * @var string
     */
    public $columnRolePermId = 'permission_id';

    /**
     * Role ids
     * 
     * @var array
     */
    public $roleIds = array();

    /**
     * Resource id
     * 
     * @var string
     */
    public $resourceId = '';

    /**
     * User id
     * 
     * @var integer
     */
    public $userId = 0;

    /**
     * Permissions\Rbac\Db\User instance
     * 
     * @var null
     */
    protected $modelUser = null;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param object $params parameters
     */
    public function __construct($c, $params)
    {
        $this->c = $c;
        $this->cache  = $c->load('service/cache');
        $this->c['config']->load('rbac');  // load rbac constants

        $this->c['model.user'] = function () use ($params) {
             return new \Obullo\Permissions\Rbac\Model\User($this->c, $this->c->load('service/provider/db', $params));
        };

        // RBAC "user_roles" table variable definitions
        $this->userRolesTableName           = RBAC_USER_ROLES_DB_TABLENAME;
        $this->columnUserPrimaryKey         = RBAC_USER_ROLES_TABLE_USER_PRIMARY_KEY;
        $this->columnUserRolePrimaryKey     = RBAC_USER_ROLES_TABLE_ROLE_PRIMARY_KEY;
        $this->columnAssignmentDate         = RBAC_USER_ROLES_COLUMN_ASSIGNMENT_DATE;

        // RBAC "roles" table variable definitions
        $this->rolesTableName               = RBAC_ROLES_DB_TABLENAME;
        $this->columnRolePrimaryKey         = RBAC_ROLES_COLUMN_PRIMARY_KEY;
        $this->columnRoleText               = RBAC_ROLES_COLUMN_TEXT;

        // RBAC "operations" table variable definitions
        $this->opTableName                  = RBAC_OPERATIONS_DB_TABLENAME;
        $this->columnOpPrimaryKey           = RBAC_OPERATIONS_COLUMN_PRIMARY_KEY;
        $this->columnOpText                 = RBAC_OPERATIONS_COLUMN_TEXT;

        // RBAC "op_permissions" table variable definitions
        $this->opPermTableName              = RBAC_OP_PERM_DB_TABLENAME;
        $this->columnOpPermOpPrimaryKey     = RBAC_OP_PERM_TABLE_OP_PRIMARY_KEY;
        $this->columnOpPermPrimaryKey       = RBAC_OP_PERM_TABLE_PERM_PRIMARY_KEY;
        $this->columnOpRolePrimaryKey       = RBAC_OP_PERM_TABLE_ROLE_PRIMARY_KEY;

        // RBAC "role_permissions" table variable definitions
        $this->rolePermTableName            = RBAC_ROLE_PERM_DB_TABLENAME;
        $this->columnRolePermRolePrimaryKey = RBAC_ROLE_PERM_TABLE_ROLES_PRIMARY_KEY;
        $this->columnRolePermPrimaryKey     = RBAC_ROLE_PERM_TABLE_PERM_PRIMARY_KEY;

        // RBAC "permissions" table variable definitions
        $this->permTableName                = RBAC_PERM_DB_TABLENAME;
        $this->columnPermPrimaryKey         = RBAC_PERM_COLUMN_PRIMARY_KEY;
        $this->columnPermParentId           = RBAC_PERM_COLUMN_PARENT_ID;
        $this->columnPermText               = RBAC_PERM_COLUMN_TEXT;
        $this->columnPermType               = RBAC_PERM_COLUMN_TYPE;
        $this->columnPermResource           = RBAC_PERM_COLUMN_RESOURCE;
    }

    // /**
    //  * Magic methods (Get)
    //  * 
    //  * @param string $name get name
    //  * 
    //  * @return return object;
    //  */
    // public function __get($name)
    // {
    //     $name = strtolower($name);
    //     $Class = 'Obullo\Permissions\Rbac\\'. ucfirst($name);

    //     if (class_exists($Class)) {
    //         return new $Class($this);
    //     }
    // }

    /**
     * Checks permission name is allowed in your permission list
     * 
     * @param string $permName    permission name
     * @param array  $permissions permissions
     * 
     * @return boolean
     */
    public function isAllowed($permName, $permissions)
    {
        if ( ! is_array($permissions)) {
            return false;
        }
        $isAssoc = array_keys($permissions) !== range(0, count($permissions) - 1);

        foreach ($permissions as $val) {
            $permValue = ($isAssoc) ? $val[$this->columnPermText] : $val;
            if ($permName == $permValue) {
                return true;
            }
        }
        return false;
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
        $this->deleteCache();
        return $this->c['mode.user']->assign($userId, $roleId);
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
        return $this->c['mode.user']->deAssign($userId, $roleId);
    }

    /**
     * Set User Id
     * 
     * @param int $userId user id
     * 
     * @return void
     */
    public function setId($userId)
    {
        $this->userId = (int)$userId;
    }
    
    /**
     * Set Role Id
     * 
     * Array 
     * (
     *   [0] => Array
     *     (
     *         [role_id] => 1
     *     )
     *   [1] => Array
     *     (
     *         [role_id] => 2
     *     )
     * )
     * 
     * @param int $roleIds role id
     * 
     * @return void
     */
    public function setRoleIds($roleIds)
    {
        if ( ! is_array($roleIds)) {
            $roleIds = array(array($this->columnUserRolePrimaryKey => $roleIds));
        }
        $this->roleIds = $roleIds;
    }

    /**
     * Get User Id
     * 
     * @return integer
     */
    public function getId()
    {
        if ($this->userId == 0) {
            throw new RuntimeException(sprintf('User id is not defined. You must first use %s->hasPagePermission() or %s->setUserId() method.', get_class()));
        }
        return $this->userId;
    }

    /**
     * Get Role Id
     * 
     * @return integer
     */
    public function getRoleIds()
    {
        if (count($this->roleIds) == 0) {
            throw new RuntimeException(sprintf('Role id is not defined. You must first use %s->setRoleIds() method.', get_class()));
        }
        return $this->roleIds;
    }

    // /**
    //  * hasRole
    //  * 
    //  * @param int $expiration expiration time
    //  * 
    //  * @return array
    //  */
    // public function hasRole($expiration = 7200)
    // {
    //     $key = static::CACHE_HAS_ROLE . $this->getUserId();
    //     $resultArray = $this->cache->get($key);
    //     if ($resultArray == false) {  // If not exist in the cache
    //         $queryResultArray = $this->c['mode.user']->hasRoleSqlQuery();  // do sql query
    //         $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
    //         $this->cache->set($key, $resultArray, $expiration);
    //     }
    //     if ($resultArray == 'empty') {
    //         return null;
    //     }
    //     return $resultArray;
    // }

    /**
     * Get Operations
     * 
     * @param int $expiration expiration time.
     * 
     * @return array
     */
    public function getRoles($expiration = 7200)
    {
        $key = static::CACHE_GET_ROLES . $this->getUserId();
        $resultArray = $this->cache->get($key);
        if ($resultArray == false) {  // If not exist in the cache
            $queryResultArray = $this->c['mode.user']->getRolesSqlQuery();  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;  // 
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
    }

    // /**
    //  * Get all permission
    //  * 
    //  * @param string $roleIds    role ids
    //  * @param int    $expiration expiration time.
    //  * 
    //  * @return array
    //  */
    // public function getPermissions($roleIds, $expiration = 7200)
    // {
    //     if (empty($roleIds)) {
    //         return null;
    //     }
    //     $key = static::CACHE_GET_ALL_PERMISSIONS . $this->getUserId();
    //     $resultArray = $this->cache->get($key);
    //     if ($resultArray == false) {  // If not exist in the cache
    //         $queryResultArray = $this->c['mode.user']->getPermissionsSqlQuery($roleIds);  // do sql query
    //         $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
    //         $this->cache->set($key, $resultArray, $expiration);
    //     }
    //     if ($resultArray == 'empty') {
    //         return null;
    //     }
    //     return $resultArray;
    // }

    // /**
    //  * Has page permission
    //  * 
    //  * @param string $operationName operations ( edit, update, delete, view )
    //  * @param int    $expiration    expiration time
    //  * 
    //  * @return boolean
    //  */
    // public function getPermission($operationName = 'view', $expiration = 7200)
    // {
    //     $permResource = $this->getResourceId();  // permission page resource ('admin/advertising')
    //     $key = static::CACHE_HAS_PAGE_PERMISSION . $this->getUserId() . ':' . $permResource;
    //     $resultArray = $this->cache->get($key);
    //     if ($resultArray == false) { // If not exist in the cache
    //         $attribute = ' AND ' . $this->db->protect($this->opTableName) . '.' . $this->db->protect($this->columnOpText) . ' = ' . $this->db->escape($operationName);
    //         $queryResultArray = $this->c['mode.user']->hasPagePermissionSqlQuery($permResource, $attribute); // do sql query
    //         $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;  // If mysql query no result cache driver say cache is false but we have the empty values
    //         $this->cache->set($key, $resultArray, $expiration);                              // This fix the query loops and gives the native value.
    //     }
    //     if ($resultArray == 'empty') {
    //         return false;
    //     }
    //     return true;
    // }

    /**
     * Role Count
     * 
     * @param int $expiration expiration time
     * 
     * @return integer
     */
    public function roleCount($expiration = 7200)
    {
        $key = static::CACHE_ROLE_COUNT . $this->getUserId();
        $resultArray = $this->cache->get($key);
        if ($resultArray === false) {  // If not exist in the cache
            $queryResultArray = $this->c['mode.user']->roleCountSqlQuery();  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return 0;
        }
        return $resultArray;
    }

    /**
     * Delete Assign User Roles
     * 
     * @param int $userId user id
     * 
     * @return object statement of Pdo
     */
    public function deleteRoles($userId)
    {
        return $this->c['mode.user']->deleteRoleFromUsers($userId);
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

    /**
     * Hash
     * 
     * @param mixed $data hash data
     * 
     * @return string
     */
    public function hash($data)
    {
        return hash('adler32', json_encode($data));
    }


    /**
     * Build array
     * 
     * @param mix $data data
     * 
     * @return array
     */
    public function arrayConvert($data)
    {
        return (! is_array($data)) ? array($data) : $data;
    }
}


// END User.php File
/* End of file User.php

/* Location: .Obullo/Permissions/Rbac/User.php */