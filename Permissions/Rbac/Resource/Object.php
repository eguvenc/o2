<?php

namespace Obullo\Permissions\Rbac\Resource;

use ArrayAccess,
    RuntimeException,
    Obullo\Permissions\Rbac\User,
    Obullo\Permissions\Rbac\Utils,
    Obullo\Permissions\Rbac\Resource\Object\Element;

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
Class Object implements ArrayAccess
{
    /**
     * Offset count
     * 
     * @var integer
     */
    protected $offsetCount = 0;

    /**
     * Container
     * 
     * @var array
     */
    protected $container = array();

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
     * @param string $func function name
     * @param array  $args arguments
     * 
     * @return array
     */
    public function __call($func, $args)
    {
        if ($this->offsetCount > 1) {

            if (! method_exists($this->c['rbac.resource.object.element'], $func)) {
                throw new RuntimeException(sprintf('Method "%s()" not found.', $func));
            }
            $this->c['rbac.resource.object.element']->objectName = $this->container[$this->offsetCount -1];

            return $this->c['rbac.resource.object.element']->$func($this->container[$this->offsetCount], $args[0]);
        }

        if (count($args) > 1) {
            return $this->c['rbac.resource.object.element']->getPermissions($this->container[$this->offsetCount], $args[0]);
        }
        return $this->getPermissions($this->container[$this->offsetCount], $args[0]);
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
        $this->offsetCount++;
        $this->container[$this->offsetCount] = $offset;
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
}


// END Object.php File
/* End of file Object.php

/* Location: .Obullo/Permissions/Rbac/Resource/Object.php */
