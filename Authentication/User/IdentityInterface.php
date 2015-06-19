<?php

namespace Obullo\Authentication\User;

use Auth\Identities\GenericUser;
use Obullo\Session\SessionInterface;
use Obullo\Container\ContainerInterface;
use Obullo\Authentication\Storage\StorageInterface;

/**
 * Identity Interface
 * 
 * @category  Authentication
 * @package   IdentityInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
interface IdentityInterface
{
    /**
     * Constructor
     *
     * @param object $c       container
     * @param object $session storage
     * @param object $storage auth storage
     * @param object $params  auth config parameters
     */
    public function __construct(ContainerInterface $c, SessionInterface $session, StorageInterface $storage, array $params);

    /**
     * Initializer
     *
     * @return void
     */
    public function initialize();

    /**
     * Check user has identity
     *
     * Its ok if returns to true otherwise false
     *
     * @return boolean
     */
    public function check();

    /**
     * Opposite of check() function
     *
     * @return boolean
     */
    public function guest();

    /**
     * Check recaller cookie exists
     *
     * @return string|boolean false
     */
    public function recallerExists();

    /**
     * Returns to "1" if user authenticated on temporary memory block otherwise "0".
     *
     * @return boolean
     */
    public function isTemporary();

    /**
     * Move permanent identity to temporary block
     * 
     * @return void
     */
    public function makeTemporary();

    /**
     * Move temporary identity to permanent block
     * 
     * @return void
     */
    public function makePermanent();

    /**
     * Check user is verified after succesfull login
     *
     * @return boolean
     */
    public function isVerified();

    /**
     * Checks new identity data available in storage.
     *
     * @return boolean
     */
    public function exists();

    /**
     * Returns to unix microtime value.
     *
     * @return string
     */
    public function getTime();

    /**
     * Set all identity attributes
     *
     * @param array $attributes identity array
     *
     * @return $object identity
     */
    public function setArray(array $attributes);

    /**
     * Get all identity attributes
     *
     * @return array
     */
    public function getArray();

    /**
     * Get the password needs rehash array.
     *
     * @return mixed false|string new password hash
     */
    public function getPasswordNeedsReHash();

    /**
     * Returns to "1" user if used remember me
     *
     * @return integer
     */
    public function getRememberMe();

    /**
     * Returns to remember token
     *
     * @return integer
     */
    public function getRememberToken();

    /**
     * Sets authority of user to "0" don't touch to cached data
     *
     * @return void
     */
    public function logout();

    /**
     * Logout User and destroy cached identity data
     *
     * @return void
     */
    public function destroy();

    /**
     * Update temporary credentials
     * 
     * @param string $key key
     * @param string $val value
     * 
     * @return void
     */
    public function updateTemporary($key, $val);

    /**
     * Update remember token if it exists in the memory and browser header
     *
     * @return int|boolean
     */
    public function updateRememberToken();

    /**
     * Refresh the rememberMe token
     *
     * @param object $genericUser GenericUser
     *
     * @return int|boolean
     */
    public function refreshRememberToken(GenericUser $genericUser);

    /**
     * Removes rememberMe cookie from user browser
     *
     * @return void
     */
    public function forgetMe();

    /**
     * Kill authority of user using auth id
     * 
     * @param integer $loginId e.g: 87060e89
     * 
     * @return boolean
     */
    public function killSignal($loginId);

    /**
     * Do finish operations
     * 
     * @return void
     */
    public function close();
}

// END IdentityInterface.php File
/* End of file IdentityInterface.php

/* Location: .Obullo/Authentication/User/IdentityInterface.php */