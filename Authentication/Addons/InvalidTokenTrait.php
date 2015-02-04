<?php

namespace Obullo\Authentication\Addons;

use RuntimeException,
    Obullo\Container\Container,
    Obullo\Authentication\User\UserIdentity;

trait InvalidTokenTrait
{
    /**
     * Invalid token event addon
     * 
     * @param object $identity UserIdentity
     * @param string $cookie   user token that we read from cookie
     * 
     * @return void
     */
    public function onInvalidToken(UserIdentity $identity, $cookie)
    {
        $this->c->load('flash/session')->error(
            sprintf(
                'Invalid auth token : %s identity %s destroyed',
                $cookie,
                $identity->getIdentifier()
            )
        );
        $this->c->load('url')->redirect($this->c['config']['auth']['login']['route']);
    }
}

// END InvalidTokenTrait.php File
/* End of file InvalidTokenTrait.php

/* Location: .Obullo/Authentication/Addons/InvalidTokenTrait.php */