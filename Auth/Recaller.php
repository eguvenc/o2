<?php

namespace Obullo\Auth;

use Obullo\Auth\UserProviderInterface,
    Auth\Credentials,
    Auth\Identities\GenericIdentity;

/**
 * O2 Authentication - RememberMe Recaller
 *
 * @category  Auth
 * @package   Token
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/auth
 */
Class Recaller
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Storage
     * 
     * @var object
     */
    protected $storage;

    /**
     * Constructor
     * 
     * @param object $c       container
     * @param object $storage storage
     */
    public function __construct($c, $storage)
    {
        $this->c = $c;
        $this->storage = $storage;
    }

    /**
     * Recall user identity using remember token
     * 
     * @param string $token remember me cookie
     * 
     * @return void
     */
    public function recallUser($token)
    {
        $database = $this->c['user.provider'];
        $resultRowArray = $database->execRecallerQuery($token);

        if ( ! is_array($resultRowArray)) {           // If login query not success.
            $this->storage->setIdentifier('Guest');   // Mark user as guest
            $this->removeCookie();
            return;
        }
        $id = $resultRowArray[Credentials::IDENTIFIER];
        $this->storage->setIdentifier($id);
        
        $genericUser = new GenericIdentity(array(Credentials::IDENTIFIER => $id));

        $adapter = $this->c['auth.adapter'];
        $adapter->generateUser($genericUser, $resultRowArray, $database, true);

        $database->updateRememberToken($adapter->getRememberToken(), $genericUser);
    }

    /**
     * Delete rememberMe cookie
     * 
     * @return void
     */
    public function removeCookie()
    {
        $cookie = $this->config['login']['rememberMe']['cookie']; // Delete rememberMe cookie
        
        setcookie(
            $cookie['prefix'].$cookie['name'], 
            null,
            -1,
            $cookie['path'],
            $this->c['config']['cookie']['domain'],   //  Get domain from global config
            $cookie['secure'], 
            $cookie['httpOnly']
        );
    }

}

// END Recaller.php File
/* End of file Recaller.php

/* Location: .Obullo/Auth/Recaller.php */