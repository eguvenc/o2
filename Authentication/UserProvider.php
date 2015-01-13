<?php

namespace Obullo\Authentication;

use Auth\Identities\GenericUser,
    Auth\Identities\AuthorizedUser,
    Obullo\Authentication\UserProviderInterface;

/**
 * Authentication Default User Provider
 * 
 * @category  Authentication
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
Class UserProvider implements UserProviderInterface
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
     * @param object $c      container
     * @param object $db     db object
     * @param array  $params array
     */
    public function __construct($c, $db, $params = array())
    {
        $this->tablename = $params['tablename'];  // Db users tablename
        
        $this->columnId = $params['id'];
        $this->columnIdentifier = $params['identifier'];
        $this->columnPassword = $params['password'];
        $this->columnRememberToken = $params['rememberToken'];  // RememberMe token column name

        $this->sqlUser = 'SELECT * FROM %s WHERE BINARY %s = ?';      // Login attempt SQL
        $this->sqlRecalledUser = 'SELECT * FROM %s WHERE %s = ?';     // Recalled user for remember me SQL
        $this->sqlUpdateRememberToken = 'UPDATE %s SET %s = ? WHERE BINARY %s = ?';  // RememberMe token update SQL

        $this->c = $c;
        $this->db = $db;
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
        $this->db->bindValue(1, $user->getIdentifier(), PARAM_STR);
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
        $this->db->bindValue(1, $token, PARAM_STR);
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
        $this->db->bindValue(1, $token, PARAM_STR);
        $this->db->bindValue(2, $user->getIdentifier(), PARAM_STR);
        $this->db->execute();
    }
}

// END UserProvider.php File
/* End of file UserProvider.php

/* Location: .Obullo/Authentication/UserProvider.php */