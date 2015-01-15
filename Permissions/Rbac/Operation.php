<?php

namespace Obullo\Permissions\Rbac;

/**
 * Operation Class
 * 
 * @category  Operation
 * @package   Operation
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/rbac
 */
Class Operation
{
    /**
     * Constructor
     * 
     * @param object $c         container
     * @param string $operation operation
     */
    public function __construct($c, $operation)
    {
        $this->c = $c;
        $this->operation = $operation;
    }

    /**
     * Magic methods ( Get )
     * 
     * @param string $class name
     * 
     * @return object
     */
    public function __get($class)
    {
        $key = strtolower($class);

        if (isset($this->{$key})) { // Lazy loading
            return $this->{$key};
        }
        $class = 'Obullo\Permissions\Rbac\Operation\Type\\'. ucfirst($key);
        $this->{$key} = new $class($this->c);
        $this->{$key}->setOperationName($this->operation);

        return $this->{$key};
    }
}


// END Operation.php File
/* End of file Operation.php

/* Location: .Obullo/Permissions/Rbac/Operation.php */
