<?php

namespace Obullo\Authentication\Identities;

/**
 * GenericUser Interface
 *
 * @category  Authentication
 * @package   IdentityInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
interface GenericUserInterface
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

// END GenericUserInterface.php File
/* End of file GenericUserInterface.php

/* Location: .Obullo/Authentication/GenericUserInterface.php */