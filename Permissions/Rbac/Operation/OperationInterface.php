<?php

namespace Obullo\Permissions\Rbac\Operation;

/**
 * Operation Interface
 * 
 * @category  Operation
 * @package   OperationInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/rbac
 */
interface OperationInterface
{
    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c);

    /**
     * Magic methods ( Get )
     * 
     * @param string $class name
     * 
     * @return object
     */
    public function __get($class);
}


// END Interface.php File
/* End of file Interface.php

/* Location: .Obullo/Permissions/Rbac/Operation/Interface.php */
