<?php

namespace Obullo\Auth;

use Obullo\Auth\UserProviderInterface,
    Obullo\Auth\Token,
    Auth\Credentials,
    Auth\Identities\GenericUser;

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
     * Config
     * 
     * @var array
     */
    protected $config;

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
        $this->config = $this->c['config']->load('auth');
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
    
        $credentials = array(
            Credentials::IDENTIFIER => $id,
            '__rememberMe' => 1,
            '__rememberToken' => $resultRowArray[Credentials::REMEMBER_TOKEN]
        );

        $genericUser = new GenericUser($credentials);
        $this->c['auth.adapter']->authenticate($genericUser, true, false);

        // $adapter->generateUser($genericUser, $resultRowArray, $database, true);

        // $token = new Token($this->c);
        // $database->updateRememberToken($token->getRememberToken(), $genericUser);
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