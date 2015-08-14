<?php

namespace Obullo\Authentication\Adapter;

use Auth\Identities\GenericUser;

/**
 * Adapter Interface
 * 
 * @category  Authentication
 * @package   AdapterInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
interface AdapterInterface
{
    /**
     * Performs an authentication attempt
     *
     * @param object  $genericUser generic identity object
     * @param boolean $login       whether to authenticate user
     * 
     * @return object authResult
     */
    public function login(GenericUser $genericUser, $login = true);

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
    public function authenticate(GenericUser $genericUser, $login = true);

     /**
     * Set identities data to AuthorizedUser object
     * 
     * @param array $genericUser         generic identity array
     * @param array $resultRowArray      success auth query user data
     * @param array $passwordNeedsRehash marks attribute if password needs rehash
     *
     * @return object
     */
    public function generateUser(GenericUser $genericUser, $resultRowArray, $passwordNeedsRehash = array());
}