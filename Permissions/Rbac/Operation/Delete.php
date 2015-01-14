<?php

namespace Obullo\Permissions\Rbac\Operation;

/**
 * Operation View
 * 
 * @category  Operation
 * @package   View
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @author    Ersin Guvenc <eguvenc@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/rbac
 */
Class View
{
    /**
     * Permissions\Rbac\User instance
     * 
     * @var object
     */
    protected $user;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c    = $c;
        $this->user = $this->c['rbac.user'];
    }

    /**
     * Checks permission name is allowed in your permission list
     * 
     * @param string $permName    permission name
     * @param array  $permissions permissions
     * 
     * @return boolean
     */
    public function isAllowed($permName, $permissions)
    {
        if ( ! is_array($permissions)) {
            return false;
        }
        $isAssoc = array_keys($permissions) !== range(0, count($permissions) - 1);

        foreach ($permissions as $val) {
            $permValue = ($isAssoc) ? $val[$this->columnPermText] : $val;
            if ($permName == $permValue) {
                return true;
            }
        }
        return false;
    }
}


// END View.php File
/* End of file View.php

/* Location: .Obullo/Permissions/Rbac/Operation/View.php */
