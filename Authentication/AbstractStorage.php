<?php

namespace Obullo\Authentication;

use Obullo\Utils\Random;
use Obullo\Container\Container;

/**
 * Abstract Adapter
 * 
 * @category  Authentication
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
abstract class AbstractStorage
{
    /**
     * Cache storage unverified users key
     */   
    const UNVERIFIED_USERS = 'Unverified:';

    /**
     * Cache storage authorized users key
     */   
    const AUTHORIZED_USERS = 'Authorized:';

    /**
     * Sets identifier value to session
     *
     * @param string $identifier user id
     * 
     * @return void
     */
    public function setIdentifier($identifier)
    {
        $this->session->set('__'.$this->c['auth.params']['cache.key'].'/Identifier', $identifier.':'.$this->getLoginId());
    }

    /**
     * Returns to user identifier
     * 
     * @return mixed string|id
     */
    public function getIdentifier()
    {
        $id = $this->session->get('__'.$this->c['auth.params']['cache.key'].'/Identifier');

        return empty($id) ? '__emptyIdentifier' : $id;
    }

    /**
     * Unset identifier from session
     * 
     * @return void
     */
    public function unsetIdentifier()
    {   
        $this->session->remove('__'.$this->c['auth.params']['cache.key'].'/Identifier');
    }

    /**
     * Get id of identifier without random Id value
     * 
     * @return string
     */
    public function getUserId()
    {
        $identifier = $this->getIdentifier();
        if (empty($identifier)) {
            return '__emptyIdentifier';
        }
        $exp = explode(':', $identifier);
        return $exp[0];
    }

    /**
     * Get random id
     * 
     * @return string
     */
    public function getLoginId()
    {
        $id = $this->session->get('__'.$this->c['auth.params']['cache.key'].'/RandomId');
        if ($id == false) {
            $id = $this->setLoginId();
            return $id;
        }
        return $id;
    }

    /**
     * Set random auth session id to sessions
     *
     * @param string $id id
     * 
     * @return string
     */
    public function setLoginId($id = null)
    {
        if (empty($id)) {
            $id = Random::generate('alnum.lower', 10);
        }
        $this->session->set('__'.$this->c['auth.params']['cache.key'].'/RandomId', $id);
        return $id;
    }
}

// END AbstractStorage.php File
/* End of file AbstractStorage.php

/* Location: .Obullo/Authentication/AbstractStorage.php */