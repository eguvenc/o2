<?php

namespace Obullo\Authentication;

use Auth\Identities\GenericUser;
use Obullo\Container\ContainerInterface;
use Obullo\Authentication\Model\UserInterface;
use Obullo\Authentication\User\IdentityInterface;
use Obullo\Authentication\Storage\StorageInterface;

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
     * Model
     * 
     * @var object
     */
    protected $model;

    /**
     * Storage
     * 
     * @var object
     */
    protected $storage;

    /**
     * User identity
     * 
     * @var object
     */
    protected $identity;

    /**
     * Datababase identifier column ( username or email .. )
     * 
     * @var string
     */
    protected $columnIdentifier;

    /**
     * Remember token column name
     * 
     * @var string
     */
    protected $columnRememberToken;

    /**
     * Constructor
     * 
     * @param object $c        \Obullo\Container\Container
     * @param object $storage  \Obullo\Authentication\Storage\Storage
     * @param object $model    \Obullo\Authetication\Model\User
     * @param array  $identity \Obullo\Authentication\Identity\Identity
     * @param array  $params   auth
     */
    public function __construct(ContainerInterface $c, StorageInterface $storage, UserInterface $model, IdentityInterface $identity, array $params)
    {
        $this->c = $c;
        $this->model = $model;
        $this->params = $params;
        $this->storage = $storage;
        $this->identity = $identity;

        $this->columnIdentifier = $params['db.identifier'];
        $this->columnRememberToken = $params['db.rememberToken'];
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
        $resultRowArray = $this->model->execRecallerQuery($token);

        if ( ! is_array($resultRowArray)) {           // If login query not success.
            $this->storage->setIdentifier('Guest');   // Mark user as guest
            $this->identity->forgetMe();
            return;
        }
        $id = $resultRowArray[$this->columnIdentifier];
        $this->storage->setIdentifier($id);

        $genericUser = new GenericUser;
        $genericUser->setCredentials(
            [
                $this->columnIdentifier => $id,
                '__rememberMe' => 1,
                '__rememberToken' => $resultRowArray[$this->columnRememberToken]
            ]
        );
        $this->c['auth.adapter']->generateUser($genericUser, $resultRowArray);  // Generate authenticated user without validation
        $this->removeInactiveSessions(); // Kill all inactive sessions of current user
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
            if (isset($val['__isAuthenticated']) && $val['__isAuthenticated'] == 0) {  
                $this->storage->killSession($loginID);
            }
        }
    }
}

// END Recaller.php File
/* End of file Recaller.php

/* Location: .Obullo/Authentication/Recaller.php */