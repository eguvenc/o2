<?php

namespace Obullo\Auth\User;

use Auth\Identities\UserIdentitiy,
    Auth\Identities\GenericIdentity,
    Obullo\Auth\UserService;

/**
 * O2 Authentication - Online Users Activity Class
 *
 * @category  Auth
 * @package   Activity
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/auth
 */
Class Activity
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Auth config
     * 
     * @var array
     */
    protected $config;

    /**
     * User identifier ( id or username )
     * 
     * @var mixed
     */
    protected $identifier;

    /**
     * User service
     * 
     * @var object
     */
    protected $user;

    /**
     * Constructor
     * 
     * @param array $params object parameters
     */
    public function __construct(array $params)
    {
        $this->c = $params['c'];
        $this->config = $params['config'];
        $this->storage = $this->params['storage'];
        $this->user = $params['user'];

        $this->session = $c->load('return session');
        $this->request = $c->load('return request');

        $this->identifier = $this->storage->getIdentifier();

    }

    public function add(){}

    public function remove() {}

    public function isOnline()
    {
        if (empty($this->identifier)) {
            return false;
        }
    }


    public function setAttribute(){}
    public function getAttribute(){}

    /**
     * Update user activity time
     * 
     * @return [type] [description]
     */
    public function refreshTime()
    {
        if (empty($this->identifier)) {
            return false;
        }
    }

}

// END Online.php File
/* End of file Online.php

/* Location: .Obullo/Auth/User/Online.php */