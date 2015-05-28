<?php

namespace Obullo\Authentication\Adapter;

use Auth\Identities\GenericUser;

/**
 * Adapter Interface
 * 
 * @category  Authentication
 * @package   AdapterInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
interface AdapterInterface
{
    /**
     * Performs an authentication attempt
     *
     * @param object  $genericUser generic identity object
     * @param boolean $login       whether to authenticate user
     * 
     * @return object authResult
     */
    public function login(GenericUser $genericUser, $login = true);
}

// END AdapterInterface.php File
/* End of file AdapterInterface.php

/* Location: .Obullo/Authentication/AdapterInterface.php */