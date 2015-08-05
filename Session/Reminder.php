<?php

namespace Obullo\Session;

use Obullo\Container\ContainerInterface;

/**
 * Session Reminder Class
 * 
 * @category  Session
 * @package   Reminder
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
class Reminder
{
    /**
     * Config parameters
     * 
     * @var array
     */
    public $config;

    /**
     * Session Class
     * 
     * @var object
     */
    public $session;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->session = $c['session'];
        $this->config = $c['config']['session'];
    }

    /**
     * Set the TTL (in seconds) for the session cookie expiry
     *
     * Can safely be called in the middle of a session.
     *
     * @param null $ttl              expiration   null or integer
     * @param bool $deleteOldSession whether to delete old session data after renenerate
     * 
     * @return void
     */
    public function rememberMe($ttl = null, $deleteOldSession = true)
    {
        $this->setSessionCookieLifetime($ttl, $deleteOldSession);
    }

    /**
     * Set a 0s TTL for the session cookie
     *
     * Can safely be called in the middle of a session.
     *
     * @return SessionManager
     */
    public function forgetMe()
    {
        $this->setSessionCookieLifetime(0);
    }

    /**
     * Set the session cookie lifetime
     *
     * If a session already exists, destroys it (without sending an expiration
     * cookie), regenerates the session ID, and restarts the session.
     *
     * @param int  $lifetime         expiration
     * @param bool $deleteOldSession whether to delete old session data after renenerate
     * 
     * @return void
     */
    protected function setSessionCookieLifetime($lifetime, $deleteOldSession = true)
    {
        session_set_cookie_params(
            $lifetime,
            $this->config['cookie']['path'],
            $this->config['cookie']['domain'],
            $this->config['cookie']['secure'],
            $this->config['cookie']['httpOnly']
        );
        if ($this->session->exists()) {
            $this->session->regenerateId($deleteOldSession, $lifetime); // There is a running session so we will regenerate id to send a new cookie.
        }
    }
}