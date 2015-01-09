<?php

namespace Obullo\Authentication;

use Auth\Identities\GenericUser,
    Obullo\Authentication\UserService;

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
     * Constructor
     * 
     * @param object $c    container object
     * @param array  $user user service object
     */
    public function __construct($c, UserService $user);

    /**
     * Performs an authentication attempt
     *
     * @param object $genericUser generic identity object
     * 
     * @return object authResult
     */
    public function login(GenericUser $genericUser);

    /**
     * Login to authetication adapter
     * 
     * @param object  $genericUser identity
     * @param boolean $login       whether to authenticate user
     * 
     * @return object
     */
    public function authenticate(GenericUser $genericUser, $login = true);

}

// END AdapterInterface.php File
/* End of file AdapterInterface.php

/* Location: .Obullo/Authentication/AdapterInterface.php */