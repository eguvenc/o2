<?php

namespace Obullo\Authentication\Model;

use Pdo;
use Obullo\Container\Container;
use Auth\Identities\GenericUser;
use Auth\Identities\AuthorizedUser;
use Obullo\Authentication\UserProviderInterface;
use Obullo\Service\ServiceProviderInterface;

/**
 * O2 User Model
 * 
 * @category  Authentication
 * @package   Model
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class User implements UserInterface
{
    public $c;                      // Container
    public $db;                     // Database
    public $tablename;              // Users tablename
    public $columnId;               // Primary key column name
    public $columnIdentifier;       // Username column name
    public $columnPassword;         // Password column name
    public $columnRememberToken;    // Remember token column name

     /**
     * Constructor
     * 
     * @param object $c        container
     * @param object $provider ServiceProviderInterface
     */
    public function __construct(Container $c, ServiceProviderInterface $provider)
    {
        $this->c = $c;
        $this->tablename           = $this->c['user']['db.tablename'];      // Db users tablename
        $this->columnId            = $this->c['user']['db.id'];
        $this->columnIdentifier    = $this->c['user']['db.identifier'];
        $this->columnPassword      = $this->c['user']['db.password'];
        $this->columnRememberToken = $this->c['user']['db.rememberToken'];  // RememberMe token column name

        $this->connect($provider);
    }

    /**
     * Set database provider connection variable ( We don't open the db connection in here ) 
     * 
     * @param object $provider [description]
     * 
     * @return void
     */
    public function connect($provider)
    {
        $this->db = $provider->get(
            [
                'connection' => $this->c['user']['db.connection']
            ]
        );
    }

    /**
     * Execute sql query
     *
     * @param object $user GenericUser object to get user's identifier
     * 
     * @return mixed boolean|array
     */
    public function execQuery(GenericUser $user)
    {
        return $this->db->prepare(sprintf('SELECT * FROM %s WHERE BINARY %s = ?', $this->tablename, $this->columnIdentifier))
            ->bindValue(1, $user->getIdentifier(), PDO::PARAM_STR)
            ->execute()
            ->rowArray();
    }

    /**
     * Recalled user sql query using remember cookie
     * 
     * @param string $token rememberMe token
     * 
     * @return array
     */
    public function execRecallerQuery($token)
    {
        return $this->db->prepare(sprintf('SELECT * FROM %s WHERE %s = ?', $this->tablename, $this->columnRememberToken))
            ->bindValue(1, $token, PDO::PARAM_STR)
            ->execute()->rowArray();
    }

    /**
     * Update remember me token upon every login & logout
     * 
     * @param string $token name
     * @param object $user  object GenericUser
     * 
     * @return integer
     */
    public function updateRememberToken($token, GenericUser $user)
    {
        return $this->db->prepare(sprintf('UPDATE %s SET %s = ? WHERE BINARY %s = ?', $this->tablename, $this->columnRememberToken, $this->columnIdentifier))
            ->bindValue(1, $token, PDO::PARAM_STR)
            ->bindValue(2, $user->getIdentifier(), PDO::PARAM_STR)
            ->execute();
    }
}

// END User.php File
/* End of file User.php

/* Location: .Obullo/Authentication/Model/User.php */