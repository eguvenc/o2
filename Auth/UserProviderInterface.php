<?php

namespace Obullo\Auth;

use Auth\Identities\GenericIdentity;

/**
 * User Provider Interface
 * 
 * @category  Auth
 * @package   ModelUserInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/auth
 */
interface UserProviderInterface
{
    /**
     * Constructor
     * 
     * @param object $c  container
     * @param object $db database object
     */
    public function __construct($c, $db);

    /**
     * Execute sql query
     *
     * @param array $user GenericIdentity object to get user's identifier
     * 
     * @return mixed boolean|array
     */
    public function execQuery(GenericIdentity $user);
    
    /**
     * Recalled user sql query using remember cookie
     * 
     * @param string $token rememberMe token
     * 
     * @return array
     */
    public function execRecallerQuery($token);
    
    /**
     * Update remember token upon every login & logout requests
     * 
     * @param string $token name
     * @param object $user  object UserIdentity
     * 
     * @return void
     */
    public function updateRememberToken($token, GenericIdentity $user);

}

// END UserProviderInterface File
/* End of file UserProviderInterface.php

/* Location: .Obullo/Auth/UserProviderInterface.php */