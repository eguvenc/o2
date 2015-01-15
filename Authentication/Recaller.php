<?php

namespace Obullo\Authentication;

use Auth\Constant,
    Auth\Identities\GenericUser,
    Obullo\Authentication\Token;

/**
 * O2 Authentication - RememberMe Recaller
 *
 * @category  Authentication
 * @package   Token
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
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
        $resultRowArray = $this->c['user.model']->execRecallerQuery($token);

        if ( ! is_array($resultRowArray)) {           // If login query not success.
            $this->storage->setIdentifier('Guest');   // Mark user as guest
            $this->removeCookie();
            return;
        }
        $id = $resultRowArray[Constant::IDENTIFIER];
        $this->storage->setIdentifier($id);

        $credentials = array(
            Constant::IDENTIFIER => $id,
            '__rememberMe' => 1,
            '__rememberToken' => $resultRowArray[Constant::REMEMBER_TOKEN]
        );
        $genericUser = new GenericUser($credentials);
        $this->c['auth.adapter']->generateUser($genericUser, $resultRowArray, true);

        $this->removeInactiveSessions(); // Kill all inactive sessions
    }

    /**
     * Destroy all inactive sessions of the user
     * 
     * @return void
     */
    protected function removeInactiveSessions()
    {
        $sessions = $this->storage->getAllSessions();
        if (count($sessions) == 0) {
            return;
        }
        foreach ($sessions as $aid => $val) {       // Destroy all inactive sessions
            if ($val['__isAuthenticated'] == 0) {  
                $this->storage->killSession($aid);
            }
        }
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

/* Location: .Obullo/Authentication/Recaller.php */