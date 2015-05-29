<?php

namespace Obullo\Authentication;

use Obullo\Utils\Random;

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
     * Cookie params
     *
     * @var array
     */
    protected $cookie;

    /**
     * Constructor
     *
     * @param array $cookie params
     */
    public function __construct(array $cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * Run cookie reminder
     *
     * @return string token
     */
    public function getRememberToken()
    {
        $this->cookie['value'] = Random::generate('alnum', 32);
        $this->c['cookie']->set($this->cookie);

        return $this->cookie['value'];
    }
}

// END Token.php File
/* End of file Token.php

/* Location: .Obullo/Authentication/Token.php */