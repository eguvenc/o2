<?php

namespace Obullo\Auth\Adapter;

use Auth\Provider\DatabaseProvider,
    Auth\Credentials,
    Auth\Identities\GenericIdentity,
    Auth\Identities\UserIdentity,
    Obullo\Auth\Token,
    Obullo\Auth\AuthResult,
    Obullo\Auth\UserService,
    Obullo\Auth\AbstractAdapter,
    Obullo\Auth\AdapterInterface,
    RuntimeException;

/**
 * O2 Authentication - Associative Array Adapter
 * 
 * @category  Auth
 * @package   AssociativeArray
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/auth
 */
class AssociativeArray extends AbstractAdapter
{
    /**
     * User service
     * 
     * @var object
     */
    public $user;

    /**
     * Token
     * 
     * @var object
     */
    protected $token;

    /**
     * Logger class
     * 
     * @var object
     */
    protected $logger;

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
    protected $resultRowArray = null;

    /**
     * Auth model
     * 
     * @var object
     */
    protected $modelUser;

    /**
     * Check temporary identity exists in storage
     * 
     * @var boolean
     */
    protected $isTemporary = false;

    /**
     * Old identifier
     * 
     * @var string
     */
    protected $trashIdentifier;

    /**
     * Failure switch
     * 
     * @var boolean
     */
    protected $failure = false;

    /**
     * Constructor
     * 
     * @param object $c    container object
     * @param object $user user service object
     */
    public function __construct($c, UserService $user)
    {
        $this->user = $user;;
        $this->storage = $c['auth.storage'];
        $this->session = $c->load('return session');
        $this->logger = $c->load('return service/logger');
        $this->token = new Token($c);

        parent::__construct($c);
    }

    /**
     * Creates array data before authenticate
     *
     * @param object $genericUser generic identity object
     * 
     * @return boolean if success
     */
    protected function initialize(GenericIdentity $genericUser)
    {
        if ($this->user->identity->isGuest()) {
            $this->trashIdentifier = $this->storage->getIdentifier();     // Set old identifier for trash
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
     * @param object $genericUser generic identity object
     * 
     * @return object authResult
     */
    public function login(GenericIdentity $genericUser)
    {
        $this->initialize($genericUser);

        if ($this->user->identity->check()) {    // If user already authenticated ?
            $this->results['code'] = AuthResult::FAILURE_ALREADY_LOGGEDIN;
        } elseif ( ! $this->storage->isEmpty('__temporary')) {
            $this->isTemporary = true;
        } else {
            $this->authenticate($genericUser);  // Perform Query
        }
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
    public function authenticate(GenericIdentity $genericUser, $login = true)
    {                
        $this->resultRowArray = $storageResult = $this->storage->query();  // First do query to memory storage if user exists in memory
        $database = new DatabaseProvider($this->c, $this->storage);
        
        if ($storageResult == false) {
            $this->resultRowArray = $database->execQuery($genericUser);  // If user does not exists in memory do sql query
        }
        if (is_array($this->resultRowArray)) {

            if ( ! isset($this->resultRowArray[Credentials::IDENTIFIER])) {
                $this->results['code'] = AuthResult::FAILURE_IDENTIFIER_CONSTANT_ERROR;
                $this->failure = true;
                return false;
            }
            $plain = $genericUser->getPassword();
            $hash = $this->resultRowArray[Credentials::PASSWORD];

            if ( ! $this->isHashedPassword($hash)) {  // Password must be hashed.
                $this->results['code'] = AuthResult::FAILURE_UNHASHED_PASSWORD;
                $this->failure = true;
                return false;
            }
            if ($passwordNeedsRehash = $this->verifyPassword($plain, $hash)) {
                
                if ($login) {  // If login process allowed.
                    $this->generateUser($genericUser, $this->resultRowArray, $database, ($storageResult) ? false : true, $passwordNeedsRehash);
                }
                return true;
            }
        }
        $this->resultRowArray = array();
        $this->failure = true; // We set failure variable when user password is fail.
        return false;
    }

    /**
     * Set identities data to UserIdentity object
     * 
     * @param array $genericUser         generic identity array
     * @param array $resultRowArray      success auth query user data
     * @param array $database            database provider
     * @param array $write2Storage       creates identity on memory storage
     * @param array $passwordNeedsRehash marks attribute if password needs rehash
     *
     * @return object
     */
    public function generateUser(GenericIdentity $genericUser, $resultRowArray, DatabaseProvider $database, $write2Storage = false, $passwordNeedsRehash = array())
    {
        $attributes = array(
            Credentials::IDENTIFIER => $genericUser->getIdentifier(),
            Credentials::PASSWORD => $resultRowArray[Credentials::PASSWORD],
            '__rememberMe' => $genericUser->getRememberMe(),
            '__isTemporary' => ($this->isEnabledVerification()) ? 1 : 0,
            '__token' => $this->token->get(),
            '__time' => ceil(microtime(true)),
        );
        /**
         * Authenticate the user and fornat auth data
         */
        $attributes = $this->formatAttributes(array_merge($attributes, $resultRowArray), $passwordNeedsRehash);

        if ($this->config['login']['session']['regenerateSessionId']) {
            $deleteOldSession = $this->config['login']['session']['deleteOldSessionAfterRegenerate'];
            $getRandomId = $this->storage->getRandomId();
            $this->regenerateSessionId($deleteOldSession);  // If session data destroyed we need to keep auth ids.
            if ($deleteOldSession) {  
                $this->storage->setRandomId($getRandomId);
                $this->storage->setIdentifier($genericUser->getIdentifier());
            }
        }
        if ($genericUser->getRememberMe()) {  // If user choosed remember feature
            $database->refreshRememberMeToken($this->getRememberToken(), $genericUser); // refresh rememberToken
        }

        if ($write2Storage OR $this->isEnabledVerification()) {   // If we haven't got identity data in memory write database query result to memory storage
            $this->write2Storage($attributes);  
        } else {
            /**
             * Authenticate cached auth data. We override __isAuthenticated item value as "1"
             * then we update the token.
             */
            $this->storage->authenticatePermanentIdentity($this->resultRowArray, $this->token);
        }
        /**
         * If verification enabled We store use as temporay That's why we delete current auth data
         */
        $trashKey = $this->config['memory']['key'].':__permanent:Authorized:'.$this->trashIdentifier;

        if ($this->isEnabledVerification() AND ! $this->storage->isEmpty($trashKey)) {  // If verification enabled "delete" old permanent credentials if exists
            $this->storage->deleteCredentials($trashKey);
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
        if (is_array($rehashedPassword) AND isset($rehashedPassword['hash'])) {
            $attributes[Credentials::PASSWORD] = $rehashedPassword['hash'];
            $attributes['__passwordNeedsRehash'] = 1;  // Developer needs to update password field
        }
        $attributes = $this->setAuthType($attributes);
        return $attributes;
    }

    /**
     * Sets "isAuthenticated" attribute
     *
     * @param array $attributes identity attributes
     * 
     * @return array $attributes
     */
    protected function setAuthType($attributes)
    {
        if ( ! $this->isEnabledVerification()) {    // If verification disabled we authenticate the user.
            $attributes['__isAuthenticated'] = 1;
            $attributes['__type'] = static::AUTHORIZED;
            return $attributes;
        }
        if ($this->user->identity->isVerified() == 0) {  // Otherwise verification enabled we don't do authenticate
            $attributes['__isAuthenticated'] = 0;
            $attributes['__type'] = static::UNVERIFIED;
            return $attributes;
        }
        if ($this->user->identity->isVerified() == 1) {  // If temporary login verified by $this->storage->authenticateTemporaryIdentity() method.
            $attributes['__isAuthenticated'] = 1;
            $attributes['__type'] = static::AUTHORIZED;
            return $attributes;
        }
    }

    /**
     * This method attempts to make
     * certain that only one record was returned in the resultset
     *
     * @return bool|Obullo\Auth\Result 
     */
    protected function validateResultSet()
    {
        if ($this->isEnabledVerification() AND $this->isTemporary) {
            $this->results['code'] = AuthResult::FAILURE_UNVERIFIED;
            $this->results['messages'][] = 'Unverified account.';
            return $this->createResult();
        }
        if ($this->isEnabledVerification() AND ! $this->storage->isEmpty('__temporary')) {
            $this->results['code'] = AuthResult::FAILURE_TEMPORARY_AUTH_HAS_BEEN_CREATED;
            $this->results['messages'][] = 'Temporary auth has been created.';
            return $this->createResult();
        }
        if ($this->results['code'] === AuthResult::FAILURE_IDENTIFIER_CONSTANT_ERROR) {
            $this->results['messages'][] = 'Credentials::IDENTIFIER constant error: Db column name doesn\'t match constant value."';
            return $this->createResult();
        }
        if ($this->results['code'] === AuthResult::FAILURE_UNHASHED_PASSWORD) {
            $this->results['messages'][] = 'User password not hashed.';
            return $this->createResult();
        }
        if ($this->results['code'] === AuthResult::FAILURE_ALREADY_LOGGEDIN) {
            $this->results['messages'][] = 'You are already logged in.';
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
        if ( ! is_array($this->resultRowArray) OR $this->failure) {   // We set failure variable when user password is fail.
            $this->results['code'] = AuthResult::FAILURE;
            $this->results['messages'][] = 'Supplied credential is invalid.';
            return $this->createResult();
        }
        if (sizeof($this->resultRowArray) == 0) {
            $this->results['code'] = AuthResult::FAILURE_CREDENTIAL_INVALID;
            $this->results['messages'][] = 'Supplied credential is invalid.';
            return $this->createResult();
        }
        if (isset($this->resultRowArray[1]) AND $this->resultRowArray[1][Credentials::IDENTIFIER]) {
            $this->results['code'] = AuthResult::FAILURE_IDENTITY_AMBIGUOUS;
            $this->results['messages'][] = 'More than one record matches the supplied identity.';
            return $this->createResult();
        }
        $this->results['code'] = AuthResult::SUCCESS;
        $this->results['messages'][] = 'Authentication successful.';
        return $this->createResult();
    }

    /**
     * Creates a Obullo\Auth\AuthResult object from the information that
     * has been collected during the authenticate() attempt.
     *
     * @return AuthResult
     */
    protected function createResult()
    {
        return new AuthResult(
            $this->results['code'],
            $this->results['identity'],
            $this->results['messages']
        );
    }
}

// END AssociativeArray.php File
/* End of file AssociativeArray.php

/* Location: .Obullo/Auth/Adapter/AssociativeArray.php */