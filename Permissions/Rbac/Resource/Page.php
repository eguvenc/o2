<?php

namespace Obullo\Permissions\Rbac\Resource;

use Obullo\Permissions\Rbac\User,
    Obullo\Permissions\Rbac\Resource;

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
 * @link      http://obullo.com/package/permissions
 */
Class Page
{
    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;

        // $this->c['rbac.resource.object.element'] = function () {
        //     return new Element($this->c);
        // };
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
    //     $Class = 'Obullo\Permissions\Rbac\Resource\Object\\'. ucfirst(strtolower($name));

    //     if ( ! class_exists($Class, false)) {
    //         $this->c['rbac.resource.object.element']->objectName = $name;
    //         return $this->c['rbac.resource.object.element'];
    //     }
    // }

    /**
     * Get permissions
     * 
     * @param mix $permName   perm name
     * @param mix $opName     operations ( view,update,delete,insert,save )
     * @param int $expiration expiration time
     * 
     * @return boolean
     */
    public function getPermission($opName, $expiration = 7200)
    {
        $opName = $this->c['rbac.user']->arrayConvert($opName);

        $key = User::CACHE_HAS_OBJECT_PERMISSION . $this->c['rbac.user']->getId() .':'. $this->c['rbac.user']->hash($this->c['rbac.resource']->getId()) .':'. $this->c['rbac.user']->hash($opName);
        $resultArray = $this->c['rbac.user']->cache->get($key);
        $resultArray = false;

        if ($resultArray === false) { // If not exist in the cache
            $queryResultArray = $this->c['model.user']->hasPagePermissionSqlQuery($opName);  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            $this->c['rbac.user']->cache->set($key, $resultArray, $expiration);
        }
        if ($resultArray == 'empty') {
            return false;
        }
        return $resultArray;
    }
}


// END Object.php File
/* End of file Object.php

/* Location: .Obullo/Permissions/Rbac/Object.php */