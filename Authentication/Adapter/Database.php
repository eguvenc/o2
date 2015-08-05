<?php

namespace Obullo\Authentication\Adapter;

use Auth\Identities\GenericUser;
use Obullo\Session\SessionInterface;
use Obullo\Authentication\AuthResult;
use Obullo\Container\ContainerInterface;
use Obullo\Authentication\User\IdentityInterface;
use Obullo\Authentication\Storage\StorageInterface;

/**
 * O2 Authentication - Database Adapter
 * 
 * @category  Authentication
 * @package   Database
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class Database extends AbstractAdapter
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Parameters
     * 
     * @var array
     */
    protected $params;

    /**
     * Session class
     * 
     * @var object
     */
    protected $session;

    /**
     * Memory storage
     * 
     * @var object
     */
    protected $storage;

    /**
     * Result messages
     *
     * @var array
     */
    protected $results = null;

    /**
     * Results of authentication query
     *
     * @var array
     */
    protected $resultRowArray = array();

    /**
     * Check temporary identity exists in storage
     * 
     * @var boolean
     */
    protected $isTemporary = false;

    /**
     * Failure switch
     * 
     * @var boolean
     */
    protected $failure = false;

    /**
     * Db Idenitifier column name
     * 
     * @var string
     */
    protected $columnIdentifier;

    /**
     * Db Password column name
     * 
     * @var string
     */
    protected $columnPassword;

    /**
     * Already logged user
     * 
     * @var string
     */
    protected $alreadyLoggedIn = false;

    /**
     * Constructor
     * 
     * @param object $c        container
     * @param object $session  session
     * @param object $storage  storage
     * @param object $identity user identity
     * @param array  $params   config parameters
     */
    public function __construct(ContainerInterface $c, SessionInterface $session, StorageInterface $storage, IdentityInterface $identity, array $params)
    {
        $this->c = $c;
        $this->params = $params;
        $this->storage = $storage;
        $this->session = $session;
        $this->identity = $identity;

        $this->columnIdentifier = $params['db.identifier'];
        $this->columnPassword   = $params['db.password'];
    }

    /**
     * Creates array data before authenticate
     *
     * @param object $genericUser generic identity object
     * 
     * @return boolean if success
     */
    protected function initialize(GenericUser $genericUser)
    {
        if ($this->identity->guest()) {
            $this->storage->setIdentifier($genericUser->getIdentifier()); // Set current identifier to storage
        }
        $this->results = array(
            'code' => AuthResult::FAILURE,
            'identity' => $genericUser->getIdentifier(),
            'messages' => array()
        );
        return true;
    }

    /**
     * Performs an authentication attempt
     *
     * @param object  $genericUser generic identity object
     * @param boolean $login       whether to authenticate user
     * 
     * @return object authResult
     */
    public function login(GenericUser $genericUser, $login = true)
    {
        $this->initialize($genericUser);
        $this->authenticate($genericUser, $login);  // Perform Query
        
        if (($authResult = $this->validateResultSet()) instanceof AuthResult) {
            return $authResult;  // If we have errors return to auth results.
        }
        $authResult = $this->validateResult();
        return $authResult;
    }

    /**
     * This method is called to attempt an authentication. Previous to this
     * call, this adapter would have already been configured with all
     * necessary information to successfully connect to "memory storage". 
     * If memory login fail it will connect to "database table" and run sql 
     * query to find a record matching the provided identity.
     *
     * @param object  $genericUser identity
     * @param boolean $login       whether to authenticate user
     * 
     * @return object
     */
    public function authenticate(GenericUser $genericUser, $login = true)
    {
        if ($this->identity->check()) {
            $this->alreadyLoggedIn = true;
            return false;
        }
        $storageResult = $this->storage->query();  // First do query to permanent memory block if user exists return to cached auth

        /**
         * If cached identity does not exist in memory do SQL query
         */
        $this->resultRowArray = ($storageResult === false) ? $this->c['auth.model']->execQuery($genericUser) : $storageResult;

        if (is_array($this->resultRowArray) && isset($this->resultRowArray[$this->columnIdentifier])) {
            $plain = $genericUser->getPassword();
            $hash  = $this->resultRowArray[$this->columnPassword];

            if ($passwordNeedsRehash = $this->verifyPassword($plain, $hash)) {  // In here hash may cause performance bottleneck depending to passwordNeedHash "cost" value
                                                                                // default is 6 for best performance.
                if ($login) {  // If login process allowed.
                    $this->generateUser($genericUser, $this->resultRowArray, $passwordNeedsRehash);
                }
                return true;
            }
        }
        $this->resultRowArray = array();
        $this->failure = true; // We set failure variable when user password is fail.
        return false;
    }

    /**
     * Set identities data to AuthorizedUser object
     * 
     * @param array $genericUser         generic identity array
     * @param array $resultRowArray      success auth query user data
     * @param array $passwordNeedsRehash marks attribute if password needs rehash
     *
     * @return object
     */
    public function generateUser(GenericUser $genericUser, $resultRowArray, $passwordNeedsRehash = array())
    {
        $attributes = array(
            $this->columnIdentifier => $genericUser->getIdentifier(),
            $this->columnPassword => $resultRowArray[$this->columnPassword],
            '__rememberMe' => $genericUser->getRememberMe(),
            '__time' => ceil(microtime(true)),
        );
        /**
         * Authenticate the user and fornat auth data
         */
        $attributes = $this->formatAttributes(array_merge($resultRowArray, $attributes), $passwordNeedsRehash);

        if ($this->params['session']['regenerateSessionId']) {
            $this->regenerateSessionId(true); // Delete old session after regenerate !
        }
        if ($genericUser->getRememberMe()) {  // If user choosed remember feature
            $this->c['auth.model']->updateRememberToken(Token::getRememberToken($this->c['cookie'], $this->params), $genericUser); // refresh rememberToken
        }
        if ($this->storage->isEmpty('__temporary')) {
            $this->storage->createPermanent($attributes);
        } else {
            $this->storage->createTemporary($attributes);
        }
    }

    /**
     * Format Attributes
     * 
     * @param array $attributes       attributes
     * @param array $rehashedPassword marks attribute if password needs rehash
     * 
     * @return array
     */
    protected function formatAttributes(array $attributes, $rehashedPassword = array())
    {
        if (is_array($rehashedPassword) && isset($rehashedPassword['hash'])) {
            $attributes[$this->columnPassword] = $rehashedPassword['hash'];
            $attributes['__passwordNeedsRehash'] = 1;  // Developer needs to update password field
        }
        return $attributes;
    }

    /**
     * This method attempts to make
     * certain that only one record was returned in the resultset
     *
     * @return bool|Obullo\Authentication\Result 
     */
    protected function validateResultSet()
    {
        if (! $this->storage->isEmpty('__temporary')) {
            $this->results['code'] = AuthResult::TEMPORARY_AUTH_HAS_BEEN_CREATED;
            $this->results['messages'][] = 'Temporary auth has been created.';
            return $this->createResult();
        }
        return true;
    }

    /**
     * This method attempts to validate that
     * the record in the resultset is indeed a record that matched the
     * identity provided to this adapter.
     *
     * @return AuthResult
     */
    protected function validateResult()
    {
        if ($this->alreadyLoggedIn) {
            $this->results['code'] = AuthResult::WARNING_ALREADY_LOGIN;
            $this->results['messages'][] = 'You have already logged in.';
            return $this->createResult();
        }
        if (! is_array($this->resultRowArray) || $this->failure) {   // We set failure variable when user password is fail.
            $this->results['code'] = AuthResult::FAILURE;
            $this->results['messages'][] = 'Supplied credential is invalid.';
            return $this->createResult();
        }
        if (sizeof($this->resultRowArray) == 0) {
            $this->results['code'] = AuthResult::FAILURE_CREDENTIAL_INVALID;
            $this->results['messages'][] = 'Supplied credential is invalid.';
            return $this->createResult();
        }
        if (isset($this->resultRowArray[1]) && $this->resultRowArray[1][$this->columnIdentifier]) {
            $this->results['code'] = AuthResult::FAILURE_IDENTITY_AMBIGUOUS;
            $this->results['messages'][] = 'More than one record matches the supplied identity.';
            return $this->createResult();
        }
        $this->results['code'] = AuthResult::SUCCESS;
        $this->results['messages'][] = 'Authentication successful.';
        return $this->createResult();
    }

    /**
     * Creates a Obullo\Authentication\AuthResult object from the information that
     * has been collected during the authenticate() attempt.
     *
     * @return AuthResult
     */
    protected function createResult()
    {
        $result = new AuthResult(
            $this->results['code'],
            $this->results['identity'],
            $this->results['messages']
        );
        $result->setResultRow($this->resultRowArray);
        return $result;
    }
    
}