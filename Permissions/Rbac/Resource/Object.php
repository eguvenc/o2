<?php

namespace Obullo\Permissions\Rbac\Resource;

use ArrayAccess;
use RuntimeException;
use Obullo\Permissions\Rbac\User;
use Obullo\Permissions\Rbac\Utils;
use Obullo\Permissions\Rbac\Resource\Object\Element;

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
class Object implements ArrayAccess
{
    /**
     * Object name
     * 
     * @var integer
     */
    protected $objectName;

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
     * Magic methods ( Call )
     * 
     * @param string $func      function name
     * @param array  $arguments arguments
     * 
     * @example $rbac->resource->object['formName']->getPermissions('view')
     * @example $rbac->resource->object['formName']->getPermissions(['user_username', 'user_email'], 'view')
     * 
     * @return array
     */
    public function __call($func, $arguments)
    {
        $argsCount = count($arguments);

        if (empty($this->objectName)) {
            throw new RuntimeException('Missing form name. You need to use "$this->rbac->resource->object[\'FormName\']->getPermissions(\'view\')"');
        }
        if ($argsCount == 0) {
            throw new RuntimeException('Missing parameter. You need to use element or operation name.');
        }
        /**
         * $arguments
         * 
         * Array
         * (
         *     [0] => Array
         *     (
         *         [0] => user_username
         *     )
         *     [1] => Array
         *     (
         *         [0] => view
         *         [1] => insert
         *     )
         * )
         * @var array
         */
        if ($argsCount > 1) { // If that argument is greater than 1 we understand desired permission is element.
            if (! method_exists($this->c['rbac.resource.object.element'], $func)) { // If method exists in "Element" class
                throw new RuntimeException(sprintf('Method "%s()" not found.', $func));
            }
            $this->c['rbac.resource.object.element']->objectName = $this->objectName; // Set object name in "Element" class

            // e.g. Obullo\Rbac\Permissions\Resource\Object\Element->getPermissions($arg[0], $arguments[1]);
            return $this->c['rbac.resource.object.element']->$func($arguments[0], $arguments[1]); 
        }
        if (! method_exists($this, $func)) {
            throw new RuntimeException(sprintf('Method "%s()" not found.', $func));
        }
        // if ($arguments[0] == 0) {
        //     throw new RuntimeException('Missing parameter. You need to use element or operation name.');
        // }
        /**
         * $arguments
         * 
         * Array
         *
         * (
         *     [0] => Array
         *     (
         *         [0] => view
         *         [1] => insert
         *     )
         * )
         * @var  array
         * @uses $this->getPermissions();
         */
        return $this->$func($this->objectName, $arguments[0]);
    }

    /**
     * Permission form name
     * 
     * @param string $offset form name
     * 
     * @return object self
     */
    public function offsetGet($offset)
    {
        $this->objectName = $offset;
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
     * Get permissions
     * 
     * @param mix $permName   perm name
     * @param mix $opName     operations ( view,update,delete,insert,save )
     * @param int $expiration expiration time
     * 
     * @return boolean
     */
    protected function getPermissions($permName, $opName, $expiration = 7200)
    {
        $opName      = Utils::arrayConvert($opName);
        $permName    = Utils::arrayConvert($permName);

        $key         = User::CACHE_HAS_OBJECT_PERMISSION . $this->c['rbac.user']->getId() .':'. Utils::hash($permName) .':'. Utils::hash($opName);
        // $resultArray = $this->c['rbac.user']->cache->get($key);
        $resultArray = false;

        if ($resultArray === false) { // If not exist in the cache
            $queryResultArray = $this->c['model.user']->hasObjectPermissionSqlQuery($permName, $opName);  // do sql query
            // echo $this->c['model.user']->db->lastQuery();  // do sql query
            $resultArray      = ($queryResultArray == false) ? 'empty' : $queryResultArray;
            // $this->c['rbac.user']->cache->set($key, $resultArray, $expiration);
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
