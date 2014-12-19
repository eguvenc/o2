<?php

namespace Obullo\Auth\Storage;

/**
 * Cache storage interface
 * 
 * @category  Auth
 * @package   StorageInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/auth
 */
interface StorageInterface
{
    /**
     * Returns true if temporary credentials does "not" exists
     *
     * @param string $storage temporary or permanent
     * 
     * @return bool
     */
    public function isEmpty($storage);

    /**
     * Sets identifier value to session
     *
     * @param string $identifier user id
     * 
     * @return void
     */
    public function setIdentifier($identifier);

    /**
     * Returns to user identifier
     * 
     * @return mixed string|id
     */
    public function getIdentifier();

    /**
     * Unset identifier from session
     * 
     * @return void
     */
    public function unsetIdentifier();

    /**
     * Get credentials and check authority
     * 
     * @return mixed bool
     */
    public function isAuthenticated();

    /**
     * Register credentials to temporary block
     * 
     * @param array $credentials user identities
     * 
     * @return void
     */
    public function loginAsTemporary(array $credentials);

    /**
     * Register credentials to permanent block
     * 
     * @param array $credentials user identities
     * 
     * @return void
     */
    public function loginAsPermanent(array $credentials);

     /**
     * Makes temporary credential attributes as permanent and authenticate the user
     * 
     * @return void
     */
    public function authenticateTemporaryIdentity();

    /**
     * Update credentials
     * 
     * @param string $oldCredentials user identity old data
     * @param string $newCredentials user identity new data
     * @param string $storage        storage persistence type permanent / temporary
     * 
     * @return boolean
     */
    public function setCredentials(array $oldCredentials, $newCredentials = null, $storage = '__temporary');

    /**
     * Get temporary|permanent credentials Data
     *
     * @param string $storage name
     * 
     * @return void
     */
    public function getCredentials($storage = '__temporary');

    /**
     * Delete temporary|permanent credentials Data
     *
     * @param string $storage name
     * 
     * @return void
     */
    public function deleteCredentials($storage = '__temporary');

}

// END StorageInterface.php File
/* End of file StorageInterface.php

/* Location: .Obullo/Auth/Storage/StorageInterface.php */