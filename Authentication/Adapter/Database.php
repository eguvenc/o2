<?php

namespace Obullo\Authentication\Adapter;

use Auth\Identities\GenericUser,
    Auth\Identities\AuthorizedUser,
    Obullo\Authentication\Token,
    Obullo\Authentication\AuthResult,
    Obullo\Authentication\UserService,
    Obullo\Authentication\AbstractAdapter,
    Obullo\Authentication\AdapterInterface,
    Obullo\Authentication\UserProviderInterface,
    RuntimeException;

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
class Database extends AbstractAdapter implements AdapterInterface
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
     * Constructor
     * 
     * @param object $c    container object
     * @param object $user user service object
     */
    public function __construct($c, UserService $user)
    {
        $this->user = $user;
        $this->storage = $c['auth.storage'];
        $this->session = $c['session'];
        $this->cache = $c->load('service/cache');

        $this->columnIdentifier = '';
        $this->columnPassword = '';

        parent::__construct($c);
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
        if ($this->user->identity->guest()) {
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
    public function login(GenericUser $genericUser)
    {
        $this->initialize($genericUser);

        if ( ! $this->storage->isEmpty('__temporary')) {
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
    public function authenticate(GenericUser $genericUser, $login = true)
    {
        $storageResult = $this->storage->query();  // First do query to memory storage if user exists in memory
        /**
         * If user does not exists in memory do sql query
         */
        $this->resultRowArray = ($storageResult === false) ? $this->c['user.provider']->execQuery($genericUser) : $storageResult;

        if (is_array($this->resultRowArray) AND isset($this->resultRowArray[$this->columnIdentifier])) {

            $plain = $genericUser->getPassword();
            $hash  = $this->resultRowArray[$this->$this->columnPassword];

            if ($passwordNeedsRehash = $this->verifyPassword($plain, $hash)) {  // In here hash may cause performance bottleneck depending to passwordNeedHash "cost" value
                                                                                // default is 6 for best performance.
                if ($login) {  // If login process allowed.
                    $this->generateUser($genericUser, $this->resultRowArray, ($storageResult) ? false : true, $passwordNeedsRehash);
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
     * @param array $write2Storage       creates identity on memory storage
     * @param array $passwordNeedsRehash marks attribute if password needs rehash
     *
     * @return object
     */
    public function generateUser(GenericUser $genericUser, $resultRowArray, $write2Storage = false, $passwordNeedsRehash = array())
    {
        $token = new Token($this->c);

        $attributes = array(
            $this->columnIdentifier => $genericUser->getIdentifier(),
            $this->columnPassword => $resultRowArray[$this->columnPassword],
            '__rememberMe' => $genericUser->getRememberMe(),
            '__isTemporary' => ($this->isEnabledVerification()) ? 1 : 0,
            '__token' => $token->get(),
            '__time' => ceil(microtime(true)),
        );
        /**
         * Authenticate the user and fornat auth data
         */
        $attributes = $this->formatAttributes(array_merge($resultRowArray, $attributes), $passwordNeedsRehash);

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
            $this->c['user.provider']->updateRememberToken($token->getRememberToken(), $genericUser); // refresh rememberToken
        }
        if ($write2Storage OR $this->isEnabledVerification()) {   // If we haven't got identity data in memory write database query result to memory storage
            $this->write2Storage($attributes);  
        } else {
            /**
             * Authenticate cached auth data. We override __isAuthenticated item value as "1"
             * then we update the token.
             */
            $this->storage->authenticatePermanentIdentity($attributes, $token);
        }
        $this->deleteOldAuth();
    }

    /**
     * If verification enabled we create new temporay auth so we need to delete old credentials.
     * 
     * @return void
     */
    protected function deleteOldAuth()
    {
        $trashKey = $this->config['cache']['key'].':__permanent:Authorized:'.$this->trashIdentifier;
        if ($this->isEnabledVerification() AND ! $this->storage->isEmpty($trashKey)) {
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
            $attributes[$this->columnPassword] = $rehashedPassword['hash'];
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
     * @return bool|Obullo\Authentication\Result 
     */
    protected function validateResultSet()
    {
        if ($this->isEnabledVerification() AND $this->isTemporary) {
            $this->results['code'] = AuthResult::FAILURE_UNVERIFIED;
            $this->results['messages'][] = 'Unverified account.';
            return $this->createResult();
        }
        if ($this->isEnabledVerification() AND ! $this->storage->isEmpty('__temporary')) {
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
        if (isset($this->resultRowArray[1]) AND $this->resultRowArray[1][$this->columnIdentifier]) {
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
        return new AuthResult(
            $this->results['code'],
            $this->results['identity'],
            $this->results['messages']
        );
    }
}

// END Database.php File
/* End of file Database.php

/* Location: .Obullo/Authentication/Adapter/Database.php */