<?php

namespace Obullo\Authentication\Model;

use Auth\Identities\GenericUser;
use Obullo\Service\ServiceProviderInterface;

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
interface UserInterface
{
     /**
     * Constructor
     * 
     * @param object $provider ServiceProviderInterface
     * @param object $params   Auth configuration & service configuration parameters
     */
    public function __construct(ServiceProviderInterface $provider, array $params);

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

// END UserInterface File
/* End of file UserInterface.php

/* Location: .Obullo/Authentication/Model/UserInterface.php */