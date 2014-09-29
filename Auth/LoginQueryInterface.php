<?php

namespace Obullo\Auth;

use Auth\Identities\GenericIdentity;

/**
 * Query interface
 * 
 * @category  Auth
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
interface LoginQueryInterface
{
    /**
     * Constructor
     * 
     * @param object $c       container
     * @param object $storage memory storage
     */
    public function __construct($c, $storage);

    /**
     * Execute sql query
     *
     * @param array $user GenericIdentity object to get user's identifier
     * 
     * @return mixed boolean|array
     */
    public function execDatabase(GenericIdentity $user);

    /**
     * Execute storage query
     *
     * @return mixed boolean|array
     */
    public function execStorage();

}

// END LoginQueryInterfacephp File
/* End of file LoginQueryInterface.php

/* Location: .Obullo/Auth/LoginQueryInterface.php */