<?php

namespace Obullo\Permissions\Rbac\Resource;

use Obullo\Permissions\Rbac\Resource\Object\Element,
    Obullo\Permissions\Rbac\Utils;

/**
 * Resource Object Permission
 * 
 * @category  Resource
 * @package   Object
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/permissions
 */
Class Object
{
    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;

        $this->c['rbac.resource.object.element'] = function () {
            return new Element($this->c);
        };
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
        $Class = 'Obullo\Permissions\Rbac\Resource\Object\\'. ucfirst(strtolower($name));

        if ( ! class_exists($Class, false)) {
            $this->c['rbac.resource.object.element']->objectName = $name;
            return $this->c['rbac.resource.object.element'];
        }
    }

    /**
     * Get permissions
     * 
     * @param mix $permName   perm name
     * @param mix $opName     operations ( view,update,delete,insert,save )
     * @param int $expiration expiration time
     * 
     * @return boolean
     */
    public function getPermissions($permName, $opName, $expiration = 7200)
    {
        $opName      = Utils::arrayConvert($opName);
        $permName    = Utils::arrayConvert($permName);

        $key         = User::CACHE_HAS_OBJECT_PERMISSION . $this->c['rbac.user']->getId() .':'. Utils::hash($permName) .':'. Utils::hash($opName);
        $resultArray = $this->c['rbac.user']->cache->get($key);

        if ($resultArray === false) { // If not exist in the cache
            $queryResultArray = $this->c['model.user']->hasObjectPermissionSqlQuery($permName, $opName);  // do sql query
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

/* Location: .Obullo/Permissions/Rbac/Resource/Object.php */
