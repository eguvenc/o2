<?php

namespace Obullo\Authentication\Model;

use Pdo;
use Auth\Identities\GenericUser;
use Auth\Identities\AuthorizedUser;
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
    public $db;                     // Database object
    public $tablename;              // Users tablename
    public $columnId;               // Primary key column name
    public $columnIdentifier;       // Username column name
    public $columnPassword;         // Password column name
    public $columnRememberToken;    // Remember token column name

     /**
     * Constructor
     * 
     * @param object $provider ServiceProviderInterface
     * @param object $params   Auth configuration & service configuration parameters
     */
    public function __construct(ServiceProviderInterface $provider, array $params)
    {
        $this->tablename           = $params['db.tablename'];      // Db users tablename
        $this->columnId            = $params['db.id'];
        $this->columnIdentifier    = $params['db.identifier'];
        $this->columnPassword      = $params['db.password'];
        $this->columnRememberToken = $params['db.rememberToken'];  // RememberMe token column name

        $this->connect($provider, $params);
    }

    /**
     * Set database provider connection variable ( We don't open the db connection in here ) 
     * 
     * @param object $provider service provider object
     * @param array  $params   parameters
     * 
     * @return void
     */
    public function connect(ServiceProviderInterface $provider, array $params)
    {
        $this->db = $provider->get(
            [
                'connection' => $params['db.connection']
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