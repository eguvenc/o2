<?php

namespace Obullo\Authentication;

use Authentication\Identities\GenericUser;

/**
 * User Provider Interface
 * 
 * @category  Authentication
 * @package   ModelUserInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
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
     * @param array $user GenericUser object to get user's identifier
     * 
     * @return mixed boolean|array
     */
    public function execQuery(GenericUser $user);
    
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
     * @param object $user  object GenericUser
     * 
     * @return void
     */
    public function updateRememberToken($token, GenericUser $user);

}

// END UserProviderInterface File
/* End of file UserProviderInterface.php

/* Location: .Obullo/Authentication/UserProviderInterface.php */