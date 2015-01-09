<?php

namespace Obullo\Authentication;

use Auth\Constant,
    Auth\Identities\GenericUser,
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
    public $rememberTokenColumn;    // Remember token column name  
    public $userSQL;                // User query sql
    public $recalledUserSQL;        // Recalled user sql
    public $rememberTokenUpdateSQL; // Remember token update sql

     /**
     * Constructor
     * 
     * @param object $c  container
     * @param object $db db object
     */
    public function __construct($c, $db)
    {
        $this->tablename = Constant::TABLENAME;  // Db users tablename
        $this->rememberTokenColumn = Constant::REMEMBER_TOKEN;  // RememberMe token column name

        $this->userSQL = 'SELECT * FROM %s WHERE BINARY %s = ?';      // Login attempt SQL
        $this->recalledUserSQL = 'SELECT * FROM %s WHERE %s = ?';     // Recalled user for remember me SQL
        $this->rememberTokenUpdateSQL = 'UPDATE %s SET %s = ? WHERE BINARY %s = ?';  // RememberMe token update SQL

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
        $this->db->prepare($this->userSQL, array($this->tablename, Constant::IDENTIFIER));
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
        $this->db->prepare($this->recalledUserSQL, array($this->tablename, $this->rememberTokenColumn));
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
        $this->db->prepare($this->rememberTokenUpdateSQL, array($this->tablename, $this->rememberTokenColumn, Constant::IDENTIFIER));
        $this->db->bindValue(1, $token, PARAM_STR);
        $this->db->bindValue(2, $user->getIdentifier(), PARAM_STR);
        $this->db->execute();
    }
}

// END UserProvider.php File
/* End of file UserProvider.php

/* Location: .Obullo/Authentication/UserProvider.php */