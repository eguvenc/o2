<?php

namespace Obullo\Auth\Adapter;

use Auth\Credentials,
    Auth\Model\User,
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
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
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
     * Application config
     * 
     * @var object
     */
    protected $config;

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
     * Check identifier exists after login query
     * 
     * @var boolean
     */
    protected $isValidIdentifier = false;

    /**
     * Auth model
     * 
     * @var object
     */
    protected $modelUser;

    /**
     * Constructor
     * 
     * @param object $c           container object
     * @param object $userService user service object
     * @param object $storage     storage
     */
    public function __construct($c, UserService $userService, $storage)
    {
        $this->user = $userService;
        $this->config = $c->load('config')->load('auth');
        $this->storage = $storage;
        $this->session = $c->load('session');

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
        $this->storage->setIdentifier($genericUser->getIdentifier()); // Set identifier to storage

        $this->results = array(
            'code'     => AuthResult::FAILURE,
            'identity' => $this->storage->getIdentifier(),
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

        if ($this->user->identity->isAuthenticated()  // If user is already authenticated regenerate the user !
            AND $genericUser->getIdentifier() == $this->user->identity->getIdentifier()
        ) {
            $this->results['code'] = AuthResult::FAILURE_ALREADY_LOGGEDIN;
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
        $modelUser = new User($this->c, $this->storage);
        $this->resultRowArray = $storageResult = $modelUser->execStorageQuery();  // First do query to memory storage if user exists in memory

        if ($storageResult == false) {
            $this->resultRowArray = $modelUser->execQuery($genericUser);  // If user does not exists in memory do sql query
        }
        if (is_array($this->resultRowArray)) {

            if ( ! isset($this->resultRowArray[Credentials::IDENTIFIER])) {
                $this->results['code'] = AuthResult::FAILURE_IDENTIFIER_CONSTANT_ERROR;
                return false;
            }
            $this->isValidIdentifier = true;  // There is no identifier error.

            $plain = $genericUser->getPassword();
            $hash = $this->resultRowArray[Credentials::PASSWORD];

            if ( ! $this->isHashedPassword($hash)) {  // Password must be hashed.
                $this->results['code'] = AuthResult::FAILURE_UNHASHED_PASSWORD;
                return false;
            }
            if ($passwordNeedsRehash = $this->verifyPassword($plain, $hash)) {

                if ($login) {  // If login process allowed.
                    $this->generateUser($genericUser, $this->resultRowArray, $modelUser, ($storageResult) ? false : true, $passwordNeedsRehash);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Set identities data to UserIdentity object
     * 
     * @param array $genericUser         generic identity array
     * @param array $resultRowArray      success auth query user data
     * @param array $modelUser           model user object
     * @param array $push2Storage        creates identity on memory storage
     * @param array $passwordNeedsRehash marks attribute if password needs rehash
     *
     * @return object
     */
    public function generateUser(GenericIdentity $genericUser, $resultRowArray, $modelUser, $push2Storage = false, $passwordNeedsRehash = array())
    {
        $token = new Token($this->c);

        $attributes = array(
            Credentials::IDENTIFIER => $genericUser->getIdentifier(),
            Credentials::PASSWORD => $resultRowArray[Credentials::PASSWORD],
            '__rememberMe' => $genericUser->getRememberMe(),
            '__isTemporary' => ($this->isEnabledVerification()) ? 1 : 0,
            '__token' => $token->refresh(),
        );
        $attributes = $this->formatAttributes(array_merge($attributes, $resultRowArray), $passwordNeedsRehash);
        
        // $this->user->identity->setArray($attributes);

        if ($this->config['login']['session']['regenerateSessionId']) {
            $this->regenerateSessionId($this->config['login']['session']['deleteOldSessionAfterRegenerate']);
        }
        if ($genericUser->getRememberMe()) {  // If user choosed remember feature
            $modelUser->refreshRememberMeToken($this->getRememberToken(), $genericUser); // refresh rememberToken
        }
        if ($push2Storage) {                                        // If we haven't got identity data in memory push it to cache the identity.
            $this->push2Storage($attributes); // Push database query result to memory storage !
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
        if ( ! $this->isEnabledVerification()) {    // If verification disabled however we authenticate the user.
            $attributes['__isAuthenticated'] = 1;
            $attributes['__type'] = static::AUTHORIZED;
            return $attributes;
        }
        if ($this->user->identity->isVerified() == 0) {  // Otherwise verification enabled
            $attributes['__isAuthenticated'] = 0;
            $attributes['__type'] = static::UNVERIFIED;
            return $attributes;
        }
        if ($this->user->identity->isVerified() == 1) {  // If temporary login verified by $this->storage->authenticateTemporaryLogin() method.
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
        if ($this->results['code'] === AuthResult::FAILURE_IDENTIFIER_CONSTANT_ERROR) {
            $this->results['messages'][] = 'Credentials::IDENTIFIER constant error: Db column name doesn\'t match constant value."
';
            return $this->createResult();
        }
        if ($this->results['code'] === AuthResult::FAILURE_UNHASHED_PASSWORD) {
            $this->results['messages'][] = 'User password not hashed.';
            return $this->createResult();
        }
        if ($this->results['code'] === AuthResult::FAILURE_ALREADY_LOGGEDIN) {
            $this->results['messages'][] = 'You are already logged in.';
            return;
        }
        if ( ! is_array($this->resultRowArray)) {
            $this->results['code'] = AuthResult::FAILURE;
            $this->results['messages'][] = 'Supplied credential is invalid.';
            return $this->createResult();
        }
        if (isset($this->resultRowArray[1]) AND $this->resultRowArray[1][Credentials::IDENTIFIER]) {
            $this->results['code'] = AuthResult::FAILURE_IDENTITY_AMBIGUOUS;
            $this->results['messages'][] = 'More than one record matches the supplied identity.';
            return $this->createResult();
        }
        if ($this->isValidIdentifier == false) {
            $this->results['code'] = AuthResult::FAILURE_IDENTITY_NOT_FOUND;
            $this->results['messages'][] = 'A record with the supplied identity could not be found.';
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
        if (sizeof($this->resultRowArray) == 0) {
            $this->results['code'] = AuthResult::FAILURE_CREDENTIAL_INVALID;
            $this->results['messages'][] = 'Supplied credential is invalid.';
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