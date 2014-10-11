<?php

namespace Obullo\Auth;

use Auth\Identities\GenericIdentity;

/**
 * User Database Model Interface
 * 
 * @category  Auth
 * @package   ModelUserInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
interface ModelUserInterface
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
     * Execute storage query
     *
     * @return mixed boolean|array
     */
    public function execStorageQuery();
    
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

// END ModelUserInterface File
/* End of file ModelUserInterface.php

/* Location: .Obullo/Auth/ModelUserInterface.php */