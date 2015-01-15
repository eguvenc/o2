<?php

namespace Obullo\Permissions\Rbac\Operation;

use Obullo\Permissions\Rbac\Operation\OperationInterface;

/**
 * Operation Save
 * 
 * @category  Operation
 * @package   Save
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/rbac
 */
Class Save implements OperationInterface
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
        $this->{$key}->setOperationName('save');

        return $this->{$key};
    }
}


// END Save.php File
/* End of file Save.php

/* Location: .Obullo/Permissions/Rbac/Operation/Save.php */
