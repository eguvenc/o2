<?php

namespace Obullo\Permissions\Rbac;

use Obullo\Permissions\Rbac\User;

/**
 * User Roles
 * 
 * @category  Permissions
 * @package   Object
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
Class Object
{
    /**
     * Constructor
     * 
     * @param object $user User object
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Magic methods (Get)
     * 
     * @param string $name get name
     * 
     * @return return object;
     */
    public function __get($name)
    {
        $Class = 'Obullo\Permissions\Rbac\Element';
        if (class_exists($Class)) {
            $child = new $Class($this->user);
            $child->objectName = $name;

            return $child;
        }
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
    public function getPermission($permName, $operationName, $expiration = 7200)
    {
        if ( ! is_array($permName)) {
            $permName = array($permName);
        }
        $cacheKey = md5(json_encode($permName));
        $key = User::CACHE_HAS_OBJECT_PERMISSION . $this->user->getUserId() . ':' . $cacheKey . ':' . $operationName;
        $resultArray = $this->user->cache->get($key);

        if ($resultArray === false) { // If not exist in the cache
            $attribute = ' AND ' . $this->user->db->protect($this->user->opTableName) . '.' . $this->user->db->protect($this->user->columnOpText) . ' = ' . $this->user->db->escape($operationName);
            $queryResultArray = $this->user->getModel()->hasObjectPermissionSqlQuery($permName, $attribute);  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->user->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return false;
        }
        return $resultArray;
    }
}


// END User.php File
/* End of file User.php

/* Location: .Obullo/Permissions/Rbac/User.php */