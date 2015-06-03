<?php

namespace Obullo\Authentication;

use Obullo\Utils\Random;
use Obullo\Cookie\CookieInterface;

/**
 * O2 Authentication - Token
 *
 * @category  Authentication
 * @package   Token
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class Token
{
    /**
     * Run cookie reminder
     *
     * @param object $cookie CookieInterface
     * @param array  $params parameters
     * 
     * @return string token
     */
    public static function getRememberToken(CookieInterface $cookie, array $params)
    {
        $cookieParams = $params['login']['rememberMe']['cookie'];

        $token = $cookieParams['value'] = Random::generate('alnum', 32);
        $cookie->set($cookieParams);
        return $token;
    }
}

// END Token.php File
/* End of file Token.php

/* Location: .Obullo/Authentication/Token.php */