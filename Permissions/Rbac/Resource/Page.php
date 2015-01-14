<?php

namespace Obullo\Permissions\Rbac\Resource;

use Obullo\Permissions\Rbac\User,
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
    }

    /**
     * Get permission
     * 
     * @param mix $opName     operations ( view,update,delete,insert,save )
     * @param int $expiration expiration time
     * 
     * @return boolean
     */
    public function getPermission($opName, $expiration = 7200)
    {
        $opName      = Utils::arrayConvert($opName);
        $key         = User::CACHE_HAS_PAGE_PERMISSION . $this->c['rbac.user']->getId() .':'. Utils::hash($this->c['rbac.resource']->getId()) .':'. Utils::hash($opName);
        $resultArray = $this->c['rbac.user']->cache->get($key);

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


// END Page.php File
/* End of file Page.php

/* Location: .Obullo/Permissions/Rbac/Resource/Page.php */