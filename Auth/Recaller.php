<?php

namespace Obullo\Auth;

use Auth\Model\User,
    Auth\Credentials,
    Auth\Identities\GenericIdentity;

/**
 * O2 Authentication - RememberMe Recaller
 *
 * @category  Auth
 * @package   Token
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
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
        $modelUser = new User($this->c, $this->storage);
        $resultRowArray = $modelUser->execRecallerQuery($token);

        if ( ! is_array($resultRowArray)) {           // If login query not success.
            $this->storage->setIdentifier('Guest');   // Mark user as guest
            $this->removeCookie();
            return;
        }
        $id = $resultRowArray[Credentials::IDENTIFIER];
        $this->storage->setIdentifier($id);
        
        $genericUser = new GenericIdentity(array(Credentials::IDENTIFIER => $id));

        $adapter = $this->c['auth.adapter'];
        $adapter->generateUser($genericUser, $resultRowArray, $modelUser, true);
        $modelUser->refreshRememberMeToken($adapter->getRememberToken(), $genericUser);
    }

    /**
     * Delete rememberMe cookie
     * 
     * @return void
     */
    public function removeCookie()
    {
        $cookie = $this->config['login']['rememberMe']['cookie']; // Delete rememberMe cookie
        $this->c->load('cookie')->delete(
            $cookie['name'],
            $this->c['config']['cookie']['domain'], //  Get domain from global config
            $cookie['path'],
            $cookie['prefix']
        );
    }

}

// END Recaller.php File
/* End of file Recaller.php

/* Location: .Obullo/Auth/Recaller.php */