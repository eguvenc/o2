<?php

namespace Obullo\Auth;

use Auth\Identities\GenericIdentity,
    Obullo\Auth\UserService;

/**
 * Adapter Interface
 * 
 * @category  Auth
 * @package   AdapterInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
interface AdapterInterface
{
    /**
     * Constructor
     * 
     * @param object $c           container object
     * @param object $storage     object
     * @param array  $userService user service object
     */
    public function __construct($c, $storage, UserService $userService);

    /**
     * Performs an authentication attempt
     *
     * @param object $genericUser generic identity object
     * 
     * @return object authResult
     */
    public function login(GenericIdentity $genericUser);

    /**
     * Login to authetication adapter
     * 
     * @param object  $genericUser identity
     * @param boolean $login       whether to authenticate user
     * 
     * @return object
     */
    public function authenticate(GenericIdentity $genericUser, $login = true);

}

// END AdapterInterface.php File
/* End of file AdapterInterface.php

/* Location: .Obullo/Auth/AdapterInterface.php */