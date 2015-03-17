<?php

namespace Obullo\Authentication;

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
     * Sets identifier value to session
     *
     * @param string $identifier user id
     * 
     * @return void
     */
    public function setIdentifier($identifier)
    {
        $this->session->set($this->getCacheKey().'/Identifier', $identifier.':'.$this->getLoginId());
    }

    /**
     * Returns to user identifier
     * 
     * @return mixed string|id
     */
    public function getIdentifier()
    {
        $id = $this->session->get($this->getCacheKey().'/Identifier');

        return empty($id) ? '__empty' : $id;
    }

    /**
     * Unset identifier from session
     * 
     * @return void
     */
    public function unsetIdentifier()
    {   
        $this->session->remove($this->getCacheKey().'/Identifier');
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
            return '__empty';
        }
        $exp = explode(':', $identifier);
        return $exp[0];  // user@example.com
    }

    /**
     * Get random id
     * 
     * @return string
     */
    public function getLoginId()
    {
        $id = $this->session->get($this->getCacheKey().'/LoginId');
        if ($id == false) {
            $id = $this->setLoginId();
            return $id;
        }
        return $id;
    }

    /**
     * Set random auth session id to sessions
     * 
     * @return string
     */
    public function setLoginId()
    {
        $userAgent = substr($this->c['request']->server('HTTP_USER_AGENT'), 0, 50);  // First 50 characters of the user agent
        $id = hash('adler32', trim($userAgent));
        $this->session->set($this->getCacheKey().'/LoginId', $id);
        return $id;
    }

    /**
     * Gey cache key
     * 
     * @return string
     */
    public function getCacheKey()
    {
        return '__'.$this->cacheKey;
    }

}

// END AbstractStorage.php File
/* End of file AbstractStorage.php

/* Location: .Obullo/Authentication/AbstractStorage.php */