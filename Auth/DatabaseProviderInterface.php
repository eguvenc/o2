<?php

namespace Obullo\Auth;

use Auth\Identities\GenericIdentity;

/**
 * User Database Provider Interface
 * 
 * @category  Auth
 * @package   ModelUserInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/auth
 */
interface DatabaseProviderInterface
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
    public function refreshRememberMeToken($token, GenericIdentity $user);

}

// END DatabaseProviderInterface File
/* End of file DatabaseProviderInterface.php

/* Location: .Obullo/Auth/DatabaseProviderInterface.php */