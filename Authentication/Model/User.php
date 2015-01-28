<?php

namespace Obullo\Authentication\Model;

use Pdo,
    Auth\Identities\GenericUser,
    Auth\Identities\AuthorizedUser,
    Obullo\Container\Container,
    Obullo\Authentication\UserProviderInterface;

/**
 * O2 User Model
 * 
 * @category  Authentication
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class User implements UserInterface
{
    public $c;                      // Container
    public $db;                     // Database
    public $tablename;              // Users tablename
    public $columnId;               // Primary key column name
    public $columnIdentifier;       // Username column name
    public $columnPassword;         // Password column name
    public $columnRememberToken;    // Remember token column name  
    public $sqlUser;                // User query sql
    public $sqlRecalledUser;        // Recalled user sql
    public $sqlUpdateRememberToken; // Remember token update sql

     /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->db = $c->load(
            'service provider '.$this->c['auth.params']['db.provider'],
            ['connection' => $this->c['auth.params']['db.connection']]
        );
        
        $this->tablename           = $this->c['auth.params']['db.tablename'];      // Db users tablename
        $this->columnId            = $this->c['auth.params']['db.id'];
        $this->columnIdentifier    = $this->c['auth.params']['db.identifier'];
        $this->columnPassword      = $this->c['auth.params']['db.password'];
        $this->columnRememberToken = $this->c['auth.params']['db.rememberToken'];  // RememberMe token column name

        $this->sqlUser = 'SELECT * FROM %s WHERE BINARY %s = ?';      // Login attempt SQL
        $this->sqlRecalledUser = 'SELECT * FROM %s WHERE %s = ?';     // Recalled user for remember me SQL
        $this->sqlUpdateRememberToken = 'UPDATE %s SET %s = ? WHERE BINARY %s = ?';  // RememberMe token update SQL

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
        $this->db->prepare($this->sqlUser, array($this->tablename, $this->columnId));
        $this->db->bindValue(1, $user->getIdentifier(), PDO::PARAM_STR);
        $this->db->execute();

        return $this->db->rowArray();  // returns to false if fail
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
        $this->db->prepare($this->sqlRecalledUser, array($this->tablename, $this->columnRememberToken));
        $this->db->bindValue(1, $token, PDO::PARAM_STR);
        $this->db->execute();

        return $this->db->rowArray();  // returns to false if fail
    }

    /**
     * Update remember me token upon every login & logout
     * 
     * @param string $token name
     * @param object $user  object GenericUser
     * 
     * @return void
     */
    public function updateRememberToken($token, GenericUser $user)
    {
        $this->db->prepare($this->sqlUpdateRememberToken, array($this->tablename, $this->columnRememberToken, $this->columnIdentifier));
        $this->db->bindValue(1, $token, PDO::PARAM_STR);
        $this->db->bindValue(2, $user->getIdentifier(), PDO::PARAM_STR);
        $this->db->execute();
    }
}

// END User.php File
/* End of file User.php

/* Location: .Obullo/Authentication/Model/User.php */