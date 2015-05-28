<?php

namespace Obullo\Authentication;

use Obullo\Container\Container;
use Auth\Identities\GenericUser;

/**
 * O2 Authentication - Recaller
 *
 * Remember the users when they come back, if remeber me cookie exist
 * authenticate the user using recaller.
 *
 * @category  Authentication
 * @package   Token
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class Recaller
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
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->storage = $this->c['auth.storage'];

        $this->columnIdentifier = $this->c['user']['db.identifier'];
        $this->rememberToken = $this->c['user']['db.rememberToken'];
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
        $id = $resultRowArray[$this->columnIdentifier];
        $this->storage->setIdentifier($id);

        $credentials = array(
            $this->columnIdentifier => $id,
            '__rememberMe' => 1,
            '__rememberToken' => $resultRowArray[$this->rememberToken]
        );
        $genericUser = new GenericUser;
        $genericUser->setContainer($this->c);
        $genericUser->setCredentials($credentials);
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
        $sessions = $this->storage->getUserSessions();
        if (sizeof($sessions) == 0) {
            return;
        }
        foreach ($sessions as $loginID => $val) {       // Destroy all inactive sessions
            if (isset($val['__isAuthenticated']) AND $val['__isAuthenticated'] == 0) {  
                $this->storage->killSession($loginID);
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
        $cookie = $this->c['user']['login']['rememberMe']['cookie']; // Delete rememberMe cookie
        setcookie(
            $cookie['prefix'].$cookie['name'], 
            null,
            -1,
            $cookie['path'],
            $cookie['domain'],   //  Get domain from global config
            $cookie['secure'], 
            $cookie['httpOnly']
        );
    }

}

// END Recaller.php File
/* End of file Recaller.php

/* Location: .Obullo/Authentication/Recaller.php */