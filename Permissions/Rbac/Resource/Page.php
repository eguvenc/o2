<?php

namespace Obullo\Permissions\Rbac\Resource;

use ArrayAccess,
    Obullo\Permissions\Rbac\User,
    Obullo\Permissions\Rbac\Utils;

/**
 * Resource Page Permission
 * 
 * @category  Resource
 * @package   Page
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/permissions
 */
Class Page implements ArrayAccess
{
    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Permission resource id
     * 
     * @param string $resource id
     * 
     * @return object self
     */
    public function offsetGet($resource)
    {
        $this->c['rbac.resource']->setId($resource);
        return $this;
    }

    /**
     * Offset Set
     * 
     * @param string $offset offset
     * @param mix    $value  value
     * 
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        unset($offset, $value);
        return;
    }

    /**
     * Offset exists
     *
     * @param string $value offset value
     *
     * @return void
     */
    public function offsetExists($value)
    {
        unset($value);
        return;
    }

    /**
     * Offset exists
     *
     * @param string $value offset value
     *
     * @return void
     */
    public function offsetUnset($value)
    {
        unset($value);
        return;
    }

    /**
     * Get permission
     * 
     * @param mix $opName     operations ( view,update,delete,insert,save )
     * @param int $expiration expiration time
     * 
     * @return boolean
     */
    public function getPermissions($opName, $expiration = 7200)
    {
        $opName      = Utils::arrayConvert($opName);
        $key         = User::CACHE_HAS_PAGE_PERMISSION . $this->c['rbac.user']->getId() .':'. Utils::hash($this->c['rbac.resource']->getId()) .':'. Utils::hash($opName);
        // $resultArray = $this->c['rbac.user']->cache->get($key);
        $resultArray = false;

        if ($resultArray === false) { // If not exist in the cache
            $queryResultArray = $this->c['model.user']->hasPagePermissionSqlQuery($opName);  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            // $this->c['rbac.user']->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return false;
        }
        return $resultArray;
    }

    /**
     * Get permission
     * 
     * @param mix $opName     operations ( view,update,delete,insert,save )
     * @param int $expiration expiration time
     * 
     * @return boolean
     */
    public function getAllPermissions($expiration = 7200)
    {
        // $opName      = Utils::arrayConvert($opName);
        // $key         = User::CACHE_HAS_PAGE_PERMISSION . $this->c['rbac.user']->getId() .':'. Utils::hash($this->c['rbac.resource']->getId()) .':'. Utils::hash($opName);
        // $resultArray = $this->c['rbac.user']->cache->get($key);
        $resultArray = false;
        
        if ($resultArray === false) { // If not exist in the cache
            $queryResultArray = $this->c['model.user']->getPagePermissionsSqlQuery();  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            // $this->c['rbac.user']->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return false;
        }
        return $resultArray;
    }
}


// END Page.php File
/* End of file Page.php

/* Location: .Obullo/Permissions/Rbac/Resource/Page.php */
