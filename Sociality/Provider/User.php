<?php

namespace Obullo\Sociality\Provider;

use Obullo\Sociality\AbstractUser;

class User extends AbstractUser
{
    /**
     * The user's access token.
     *
     * @var string
     */
    public $token;

    /**
     * Set the token on the user.
     *
     * @param string $token access token
     * 
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
}
