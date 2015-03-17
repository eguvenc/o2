<?php

namespace Obullo\Authentication\Storage;

use Obullo\Container\Container;
use Obullo\ServiceProviders\ServiceProviderInterface;

/**
 * Cache storage interface
 * 
 * @category  Authentication
 * @package   StorageInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
interface StorageInterface
{
    /**
     * Constructor
     * 
     * @param object $c        container
     * @param object $provider ServiceProviderInterface
     */
    public function __construct(Container $c, ServiceProviderInterface $provider);

    /**
     * Returns true if temporary credentials "not" exists
     *
     * @param string $block __temporary or __permanent | full key
     * 
     * @return bool
     */
    public function isEmpty($block = '__permanent');

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
    public function createTemporary(array $credentials);

    /**
     * Register credentials to permanent block
     * 
     * @param array $credentials user identities
     * 
     * @return void
     */
    public function createPermanent(array $credentials);

     /**
     * Makes temporary credential attributes as permanent and authenticate the user
     * 
     * @return void
     */
    public function makeTemporary();

    /**
     * Makes unauthorized permanent credential attributes as permanent and unauthenticate the user
     * 
     * @return void
     */
    public function makePermanent();

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

    /**
     * Update identity value
     * 
     * @param string $key string
     * @param value  $val value
     *
     * @return void
     */
    public function update($key, $val);

    /**
     * Remove identity key ( one item )
     * 
     * @param string $key string
     * 
     * @return void
     */
    public function remove($key);

    /**
     * Get multiple authenticated sessions
     * 
     * @return array|false
     */
    public function getAllSessions();

    /**
     * Kill authority of user using auth id
     * 
     * @param string $aid auth id (10 chars)  e.g:  ahtrzflp79
     * 
     * @return boolean
     */
    public function killSession($aid);

}

// END StorageInterface.php File
/* End of file StorageInterface.php

/* Location: .Obullo/Authentication/Storage/StorageInterface.php */