<?php

namespace Obullo\Authentication\Identities;

/**
 * AuthorizedUser Inderface
 *
 * @category  Authentication
 * @package   IdentityInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
interface AuthorizedUserInterface
{
    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getIdentifier();

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getPassword();
}

// END AuthorizedUserInterface.php File
/* End of file AuthorizedUserInterface.php

/* Location: .Obullo/Authentication/AuthorizedUserInterface.php */