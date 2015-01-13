<?php

namespace Obullo\Permissions\Rbac;

use Obullo\Permissions\Rbac\Resource\Page,
    Obullo\Permissions\Rbac\Resource\Object,
    RuntimeException;

/**
 * RBAC Resource
 * 
 * @category  Rbac
 * @package   Resource
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/permissions
 */
Class Resource
{
    /**
     * Resource id
     * 
     * @var string
     */
    public $resourceId;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct($c, $params = array())
    {
        $this->c = $c;
        $this->params = $params;

        $this->c['rbac.resource.page'] = function () {
            return new Page($this->c);
        };
        $this->c['rbac.resource.object'] = function () {
            return new Object($this->c);
        };
    }

    /**
     * Magic methods (Get)
     * 
     * @param string $class name
     * 
     * @return return object;
     */
    public function __get($class)
    {
        return $this->c['rbac.resource.'.strtolower($class)];
    }

    /**
     * Set resource id
     * 
     * @param string $id resource id
     * 
     * @return void
     */
    public function setId($id)
    {
        $this->resourceId = $id;
    }

    /**
     * Get resource id
     * 
     * @return string
     */
    public function getId()
    {
        if (empty($this->resourceId)) {
            throw new RuntimeException('Resource id is not defined. You must first use $this->rbac->resource->setId() method.');
        }
        return $this->resourceId;
    }
}


// END Resource.php File
/* End of file Resource.php

/* Location: .Obullo/Permissions/Rbac/Resource.php */
