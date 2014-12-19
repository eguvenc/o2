<?php

namespace Obullo\Permissions\Rbac;

use Closure,
    RunTimeException;

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
     * Table constants
     */
    const TABLENAME        = 'db.tablename';
    const PRIMARY_KEY      = 'db.primary_key';
    const USER_PRIMARY_KEY = 'db.user_primary_key';
    const ROLE_PRIMARY_KEY = 'db.role_primary_key';
    const ROLE_TEXT        = 'db.text';
    const PERM_PRIMARY_KEY = 'db.perm_primary_key';
    const PERM_PARENT_ID   = 'db.parent_id';
    const PERM_RESOURCE    = 'db.resource';
    const PERM_TEXT        = 'db.text';
    const PERM_TYPE        = 'db.type';
    const OP_PRIMARY_KEY   = 'db.op_primary_key';
    const OP_TEXT          = 'db.text';
    const ASSIGNMENT_DATE  = 'db.assignment_date';
    const IS_ALLOWED       = 'db.allow';
    const IS_DENIED        = 'db.deny';

    /**
     * Cache constants
     */
    const CACHE_HAS_PAGE_PERMISSION   = 'Permissions:Rbac:User:hasPagePermission:';   // Redis supported key format.
    const CACHE_GET_ALL_PERMISSIONS   = 'Permissions:Rbac:User:getAllPermissions:';
    const CACHE_HAS_OBJECT_PERMISSION = 'Permissions:Rbac:User:hasObjectPermission:';
    const CACHE_GET_OPERATIONS        = 'Permissions:Rbac:User:getOperations:';
    const CACHE_GET_ROLES             = 'Permissions:Rbac:User:getRoles:';
    const CACHE_ROLE_COUNT            = 'Permissions:Rbac:User:roleCount:';
    const CACHE_HAS_ROLE              = 'Permissions:Rbac:User:hasRole:';

    /**
     * Container
     * 
     * @var object
     */
    protected $c;

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
        $this->cache = $c->load('service/cache');
        
        $this->c->config->load('constants/rbac');  // load rbac constants
        
        $columns = $config['database']['columns'];

        if (count($columns) > 0) {
            $this->userRolesTableName           = $columns['user_roles'][static::TABLENAME];
            $this->columnUserPrimaryKey         = $columns['user_roles'][static::USER_PRIMARY_KEY];
            $this->columnUserRolePrimaryKey     = $columns['user_roles'][static::ROLE_PRIMARY_KEY];
            $this->columnAssignmentDate         = $columns['user_roles'][static::ASSIGNMENT_DATE];
            $this->rolesTableName               = $columns['roles'][static::TABLENAME];
            $this->columnRolePrimaryKey         = $columns['roles'][static::PRIMARY_KEY];
            $this->columnRoleText               = $columns['roles'][static::ROLE_TEXT];
            $this->opTableName                  = $columns['operations'][static::TABLENAME];
            $this->columnOpPrimaryKey           = $columns['operations'][static::PRIMARY_KEY];
            $this->columnOpText                 = $columns['operations'][static::OP_TEXT];
            $this->opPermTableName              = $columns['op_permissions'][static::TABLENAME];
            $this->columnOpPermOpPrimaryKey     = $columns['op_permissions'][static::OP_PRIMARY_KEY];
            $this->columnOpPermPrimaryKey       = $columns['op_permissions'][static::PERM_PRIMARY_KEY];
            $this->columnOpRolePrimaryKey       = $columns['op_permissions'][static::ROLE_PRIMARY_KEY];
            $this->rolePermTableName            = $columns['role_permissions'][static::TABLENAME];
            $this->columnRolePermRolePrimaryKey = $columns['role_permissions'][static::ROLE_PRIMARY_KEY];
            $this->columnRolePermPrimaryKey     = $columns['role_permissions'][static::PERM_PRIMARY_KEY];
            $this->columnPermPrimaryKey         = $columns['permissions'][static::PRIMARY_KEY];
            $this->columnPermParentId           = $columns['permissions'][static::PERM_PARENT_ID];
            $this->permTableName                = $columns['permissions'][static::TABLENAME];
            $this->columnPermText               = $columns['permissions'][static::PERM_TEXT];
            $this->columnPermType               = $columns['permissions'][static::PERM_TYPE];
            $this->columnPermResource           = $columns['permissions'][static::PERM_RESOURCE];
            $this->isAllowed                    = $columns[static::IS_ALLOWED];
            $this->isDenied                     = $columns[static::IS_DENIED];
        }
    }

    /**
     * Checks permission name is allowed in your permission list
     * 
     * @param string $permName    permission name
     * @param array  $permissions permissions
     * 
     * @return boolean
     */
    public function isPermitted($permName, $permissions)
    {
        if ( ! is_array($permissions)) {
            return false;
        }
        $element = $this->c->load('return utils/element');
        $isAssoc = $element->isAssoc($permissions);

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
        $this->db->prepare(
            'INSERT INTO %s (%s,%s, %s) VALUES (?,?,?)',
            array(
                $this->userRolesTableName,
                $this->columnUserPrimaryKey,
                $this->columnUserRolePrimaryKey,
                $this->columnAssignmentDate
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
        $this->deleteCache();
        $this->db->prepare(
            'DELETE FROM %s WHERE %s = ? AND %s = ?',
            array(
                $this->userRolesTableName,
                $this->columnUserPrimaryKey,
                $this->columnUserRolePrimaryKey
            )
        );
        $this->db->bindValue(1, $userId, PARAM_INT);
        $this->db->bindValue(2, $roleId, PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Set User Id
     * 
     * @param int $userId user id
     * 
     * @return void
     */
    public function setUserId($userId)
    {
        $this->userId = (int)$userId;
    }

    /**
     * Set Resource Id
     * 
     * @param string $resourceId resource id
     * 
     * @return void
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = (string)$resourceId;
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
            $roleIds[] = array($this->columnUserRolePrimaryKey => $roleIds);
        }
        $this->roleIds = $roleIds;
    }
    
    /**
     * Get User Id
     * 
     * @return integer
     */
    public function getUserId()
    {
        if ($this->userId == 0) {
            throw new RunTimeException(sprintf('User id is not defined. You must first use %s->hasPagePermission() or %s->setUserId() method.', get_class()));
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
            throw new RunTimeException(sprintf('Role id is not defined. You must first use %s->setRoleIds() method.', get_class()));
        }
        return $this->roleIds;
    }

    /**
     * Get Resource Id
     * 
     * @return string
     */
    public function getResourceId()
    {
        if (empty($this->resourceId)) {
            $getClass = get_class();
            throw new RunTimeException(sprintf('Resource id is not defined. You must first use %s->hasPagePermission() or %s->setResourceId() method.', $getClass, $getClass));
        }
        return $this->resourceId;
    }
    
    /**
     * hasRole
     * 
     * @param int $expiration expiration time
     * 
     * @return array
     */
    public function hasRole($expiration = 7200)
    {
        $key = static::CACHE_HAS_ROLE . $this->getUserId();
        $resultArray = $this->cache->get($key);
        if ($resultArray == false) {  // If not exist in the cache
            $queryResultArray = $this->hasRoleSqlQuery();  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
    }

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
            $queryResultArray = $this->getRolesSqlQuery();  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;  // 
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
    }

    /**
     * Get all permission
     * 
     * @param string $roleIds    role ids
     * @param int    $expiration expiration time.
     * 
     * @return array
     */
    public function getPermissions($roleIds, $expiration = 7200)
    {
        if (empty($roleIds)) {
            return null;
        }
        $key = static::CACHE_GET_ALL_PERMISSIONS . $this->getUserId();
        $resultArray = $this->cache->get($key);
        if ($resultArray == false) {  // If not exist in the cache
            $queryResultArray = $this->getPermissionsSqlQuery($roleIds);  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return null;
        }
        return $resultArray;
    }

    /**
     * Has page permission
     * 
     * @param string $permResource  permission page resource ('admin/advertising'),
     * @param string $operationName operations ( edit, update, delete, view )
     * @param int    $expiration    expiration time
     * 
     * @return boolean
     */
    public function hasPagePermission($permResource, $operationName = 'view', $expiration = 7200)
    {
        $this->setResourceId($permResource);
        $key = static::CACHE_HAS_PAGE_PERMISSION . $this->getUserId() . ':' . $permResource;
        $resultArray = $this->cache->get($key);
        if ($resultArray == false) { // If not exist in the cache
            $attribute = ' AND ' . $this->db->protect($this->opTableName) . '.' . $this->db->protect($this->columnOpText) . ' = ' . $this->db->escape($operationName);
            $queryResultArray = $this->hasPagePermissionSqlQuery($permResource, $attribute); // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;  // If mysql query no result cache driver say cache is false but we have the empty values
            $this->cache->set($key, $resultArray, $expiration);                              // This fix the query loops and gives the native value.
        }
        if ($resultArray == 'empty') {
            return false;
        }
        return true;
    }

    /**
     * Has operation
     * 
     * @param string $permName      perm name
     * @param string $operationName operations ( edit, update, delete, view )
     * @param int    $expiration    expiration time
     * 
     * @return boolean
     */
    public function hasObjectPermission($permName, $operationName, $expiration = 7200)
    {
        if ( ! is_array($permName)) {
            $permName = array($permName);
        }
        $cacheKey = md5(json_encode($permName));
        $key = static::CACHE_HAS_OBJECT_PERMISSION . $this->getUserId() . ':' . $cacheKey . ':' . $operationName;
        $resultArray = $this->cache->get($key);
        if ($resultArray === false) { // If not exist in the cache
            $attribute = ' AND ' . $this->db->protect($this->opTableName) . '.' . $this->db->protect($this->columnOpText) . ' = ' . $this->db->escape($operationName);
            $queryResultArray = $this->hasObjectPermissionSqlQuery($permName, $attribute);  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return false;
        }
        return $resultArray;
    }

    /**
     * Has operation
     * 
     * @param string $objectName    object name
     * @param string $permName      permission name
     * @param string $operationName operations ( edit, update, delete, view )
     * @param int    $expiration    expiration time
     * 
     * @return boolean
     */
    public function hasChildPermission($objectName, $permName, $operationName, $expiration = 7200)
    {
        if ( ! is_array($permName)) {
            $permName = array($permName);
        }
        $cacheKey = md5(json_encode($permName));
        $key = static::CACHE_HAS_OBJECT_PERMISSION . $this->getUserId() . ':' . $cacheKey . ':' . $operationName;
        $resultArray = $this->cache->get($key);
        if ($resultArray === false) { // If not exist in the cache
            $attribute = ' AND ' . $this->db->protect($this->opTableName) . '.' . $this->db->protect($this->columnOpText) . ' = ' . $this->db->escape($operationName);
            $queryResultArray = $this->hasChildPermissionSqlQuery($objectName, $permName, $attribute);  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return false;
        }
        return $resultArray;
    }

    /**
     * Role Count
     * 
     * @param int $expiration expiration time
     * 
     * @return integer
     */
    public function roleCount($expiration = 7200)
    {
        $key         = static::CACHE_ROLE_COUNT . $this->getUserId();
        $resultArray = $this->cache->get($key);
        if ($resultArray === false) {  // If not exist in the cache
            $queryResultArray = $this->roleCountSqlQuery();  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return 0;
        }
        return $resultArray;
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
        $roleIds = $this->getRoleIds();
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
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermText),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermPrimaryKey),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermRolePrimaryKey),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermResource),
                $this->db->protect($this->opTableName),
                $this->db->protect($this->columnOpText),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermPrimaryKey),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermPrimaryKey),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermRolePrimaryKey),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserRolePrimaryKey),
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermPrimaryKey),
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->columnRolePermPrimaryKey),
                $this->db->protect($this->opTableName),
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->columnOpPermOpPrimaryKey),
                $this->db->protect($this->opTableName),
                $this->db->protect($this->columnOpPrimaryKey),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermResource),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermText),
                str_repeat('?,', count($permName) - 1) . '?',
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermType),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserPrimaryKey),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?',
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->columnOpRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?',
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermParentId),
                $this->db->protect($this->columnPermPrimaryKey),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermText)
            )
        );
        $this->db->bindValue(1, $this->getResourceId(), PARAM_STR);
        $i = 1;
        foreach ($permName as $name) {
            $i++;
            $this->db->bindValue($i, $name, PARAM_STR);
        }
        $i++;
        $this->db->bindValue($i, 'object', PARAM_STR);
        $i++;
        $this->db->bindValue($i, $this->getUserId(), PARAM_INT);

        foreach ($roleIds as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->columnUserRolePrimaryKey], PARAM_INT);
        }
        foreach ($roleIds as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->columnUserRolePrimaryKey], PARAM_INT);
        }
        $i++;
        $this->db->bindValue($i, $objectName, PARAM_STR);
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Run sql query
     * 
     * @return array data otherwise false.
     */
    protected function hasRoleSqlQuery()
    {
        $roleIds = $this->getRoleIds();
        $this->db->prepare(
            'SELECT %s.%s,%s.%s,%s.%s
                FROM %s
                INNER JOIN %s
                ON %s.%s = %s.%s
                WHERE %s.%s = ?
                AND %s.%s IN (%s)',
            array(
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserPrimaryKey),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserRolePrimaryKey),
                $this->db->protect($this->rolesTableName),
                $this->db->protect($this->columnRoleText),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->rolesTableName),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserRolePrimaryKey),
                $this->db->protect($this->rolesTableName),
                $this->db->protect($this->columnRolePrimaryKey),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserPrimaryKey),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?'
            )
        );
        $this->db->bindValue(1, $this->getUserId(), PARAM_INT);
        $i = 1;
        foreach ($roleIds as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->columnUserRolePrimaryKey], PARAM_INT);
        }
        $this->db->execute();
        return $this->db->rowArray();
    }

    /**
     * Run sql query
     * 
     * @return array data otherwise false.
     */
    protected function getRolesSqlQuery()
    {
        $this->db->prepare(
            'SELECT %s
                FROM %s
                WHERE %s = ?',
            array(
                $this->db->protect($this->columnUserRolePrimaryKey),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserPrimaryKey)
            )
        );
        $this->db->bindValue(1, $this->getUserId(), PARAM_INT);
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
    protected function getPermissionsSqlQuery($roleIds)
    {
        $this->db->prepare(
            'SELECT
                %s.%s
                FROM
                %s
                WHERE %s IN (%s)',
            array(
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermPrimaryKey),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?'
            )
        );
        $i = 0;
        foreach ($roleIds as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->columnRolePermRolePrimaryKey], PARAM_INT);
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
    protected function hasPagePermissionSqlQuery($permResource, $extra)
    {
        $roleIds = $this->getRoleIds();
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
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermText),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermPrimaryKey),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermRolePrimaryKey),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermResource),
                $this->db->protect($this->opTableName),
                $this->db->protect($this->columnOpText),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermPrimaryKey),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermPrimaryKey),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermRolePrimaryKey),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserRolePrimaryKey),
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermPrimaryKey),
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->columnRolePermPrimaryKey),
                $this->db->protect($this->opTableName),
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->columnOpPermOpPrimaryKey),
                $this->db->protect($this->opTableName),
                $this->db->protect($this->columnOpPrimaryKey),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermResource),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermType),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserPrimaryKey),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?',
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->columnOpRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?'
            )
        );
        $this->db->bindValue(1, $permResource, PARAM_STR);
        $this->db->bindValue(2, 'page', PARAM_STR);
        $this->db->bindValue(3, $this->getUserId(), PARAM_INT);
        $i=3;
        foreach ($roleIds as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->columnUserRolePrimaryKey], PARAM_INT);
        }
        foreach ($roleIds as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->columnUserRolePrimaryKey], PARAM_INT);
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
    protected function hasObjectPermissionSqlQuery($permName, $extra)
    {
        $roleIds = $this->getRoleIds();
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
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermText),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermPrimaryKey),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermRolePrimaryKey),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermResource),
                $this->db->protect($this->opTableName),
                $this->db->protect($this->columnOpText),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermPrimaryKey),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermPrimaryKey),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermRolePrimaryKey),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserRolePrimaryKey),
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermPrimaryKey),
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->columnRolePermPrimaryKey),
                $this->db->protect($this->opTableName),
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->columnOpPermOpPrimaryKey),
                $this->db->protect($this->opTableName),
                $this->db->protect($this->columnOpPrimaryKey),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermResource),
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermText),
                str_repeat('?,', count($permName) - 1) . '?',
                $this->db->protect($this->permTableName),
                $this->db->protect($this->columnPermType),
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserPrimaryKey),
                $this->db->protect($this->rolePermTableName),
                $this->db->protect($this->columnRolePermRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?',
                $this->db->protect($this->opPermTableName),
                $this->db->protect($this->columnOpRolePrimaryKey),
                str_repeat('?,', count($roleIds) - 1) . '?'
            )
        );
        $this->db->bindValue(1, $this->getResourceId(), PARAM_STR);
        $i = 1;
        foreach ($permName as $name) {
            $i++;
            $this->db->bindValue($i, $name, PARAM_STR);
        }
        $i++;
        $this->db->bindValue($i, 'object', PARAM_STR);
        $i++;
        $this->db->bindValue($i, $this->getUserId(), PARAM_INT);

        foreach ($roleIds as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->columnUserRolePrimaryKey], PARAM_INT);
        }
        foreach ($roleIds as $id) {
            $i++;
            $this->db->bindValue($i, $id[$this->columnUserRolePrimaryKey], PARAM_INT);
        }
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Run sql query
     * 
     * @return array data otherwise false
     */
    protected function roleCountSqlQuery()
    {
        $this->db->prepare(
            'SELECT * FROM %s WHERE %s = ?',
            array(
                $this->userRolesTableName,
                $this->columnUserPrimaryKey
            )
        );
        $this->db->bindValue(1, $this->getUserId(), PARAM_INT);
        $this->db->execute();
        return $this->db->count();
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
        $this->deleteCache();
        $this->db->prepare(
            'DELETE FROM %s WHERE %s = ?',
            array(
                $this->db->protect($this->userRolesTableName),
                $this->db->protect($this->columnUserPrimaryKey)
            )
        );
        $this->db->bindValue(1, $userId, PARAM_INT);

        return $this->db->execute();
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


// END User.php File
/* End of file User.php

/* Location: .Obullo/Permissions/Rbac/User.php */