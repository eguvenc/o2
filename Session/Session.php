<?php

namespace Obullo\Session;

use Obullo\Session\MetaData\MetaData;
use Obullo\Session\MetaData\Disabled;

/**
 * Session Class
 * 
 * @category  Session
 * @package   Session
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/session
 */
Class Session
{
    /**
     * Config class
     * 
     * @var object
     */
    public $config;

    /**
     * Session handler
     * 
     * @var object
     */
    public $handler;

    /**
     * Logger class
     * 
     * @var object
     */
    public $logger;

    /**
     * Configuration params
     * 
     * @var array
     */
    public $params;

    /**
     * Metadata Class
     * 
     * @var object
     */
    public $metaData;

    /**
     * Constructor
     *
     * @param array $c      container
     * @param array $params configuration
     */
    public function __construct($c, $params = array()) 
    {
        $this->params = $params;        
        $handler = '\Obullo\Session\Handler\\'.ucfirst($params['handler']);
        $this->handler = new $handler($c, $params);
        $this->config = $c->load('config');
        $this->logger = $c->load('service/logger');

        ini_set('session.cookie_domain', $this->params['cookie']['domain']);

        session_set_save_handler(
            array($this->handler, 'open'),
            array($this->handler, 'close'),
            array($this->handler, 'read'),
            array($this->handler, 'write'),
            array($this->handler, 'destroy'),
            array($this->handler, 'gc')
        );
        $this->metaData = ($this->params['metaData']['enabled']) ? new MetaData($c, $params, $this) : new Disabled;
        register_shutdown_function(array($this, 'close'));

        session_set_cookie_params(
            ($this->params['expireOnClose']) ? 0 : $this->params['lifetime'],
            $this->params['cookie']['path'],
            $this->params['cookie']['domain'],
            $this->params['cookie']['secure'], 
            $this->params['cookie']['httpOnly']
        );

        session_name($this->getName());
        if (session_status() == PHP_SESSION_NONE) { // If another session_start() used before ?
            session_start();
        }
        $this->logger->debug('Session Class Initialized');
    }

    /**
     * Get name of the session
     * 
     * @return string
     */
    public function getName()
    {
        return $this->params['cookie']['prefix'].$this->params['cookie']['name'];
    }

    /**
     * Read Cookie and validate Meta Data
     * 
     * @return boolean
     */
    public function readSession()
    {
        $cookie = (isset($_COOKIE[$this->getName()])) ? $_COOKIE[$this->getName()] : false;
        if ($cookie === false) {
            return false;
        }
        if ( ! $this->metaData->isValid()) { // If meta data is not valid say good bye to user !
            return false;
        }
        return true;
    }

    /**
     * Regenerate id
     *
     * Regenerate the session ID, using session save handler's
     * native ID generation Can safely be called in the middle of a session.
     *
     * @param bool $deleteOldSession whether to delete previous session data
     * @param int  $lifetime         max lifetime of session
     * 
     * @return string new session id
     */
    public function regenerateId($deleteOldSession = true, $lifetime = null)
    {
        $oldSessionId = session_id();
        session_regenerate_id((bool) $deleteOldSession);
        $storageLifetime = ($lifetime == null) ? $this->params['lifetime'] : $lifetime;
        $this->handler->setLifetime($storageLifetime);
        $this->remove($oldSessionId);  // Removes old Session id value
        $this->metaData->create();

        return session_id(); // new session_id
    }

    /**
     * Does a session exist and is it currently active ?
     *
     * @return bool
     */
    public function exists()
    {
        if (session_status() == PHP_SESSION_ACTIVE AND session_id()) {  // Session is active and session not empty.
            return true;
        }
        if (headers_sent()) {
            return true;
        }
        return false;
    }

    /**
     * Destroy the current session
     *
     * @return void
     */
    public function destroy()
    {
        if ( ! $this->exists()) {
            return;
        }
        session_destroy();
        if ( ! headers_sent()) {
            setcookie(
                $this->getName(),                 // session name
                '',                               // value
                $_SERVER['REQUEST_TIME'] - 42000, // TTL for cookie
                $this->params['cookie']['path'],
                $this->params['cookie']['domain'],
                $this->params['cookie']['secure'], 
                $this->params['cookie']['httpOnly']
            );
        }
    }

    /**
     * Check session id is expired
     * 
     * @return boolean 
     */
    public function isExpired()
    {
        $metaData = $this->metaData->read();
        if ( ! isset($metaData['la'])) {  // la = meta data last activity.
            return false;
        }
        $expire = $this->getTime() - $this->params['lifetime'];
        if ($metaData['la'] <= $expire) {
            return true;
        }
        return false;
    }

    /**
     * Fetch a specific item from the session array
     *
     * @param string $item   session key
     * @param string $prefix session key prefix
     * 
     * @return string
     */
    public function get($item, $prefix = '')
    {
        if ($item == 'session_id') {
            return session_id();
        }
        return ( ! isset($_SESSION[$prefix . $item])) ? false : $_SESSION[$prefix . $item];
    }

    /**
     * Add or change data in the $_SESSION
     * 
     * @param mixed  $newData key or array
     * @param string $newval  value
     * @param string $prefix  prefix
     * 
     * @return   void
     */
    public function set($newData = array(), $newval = '', $prefix = '')
    {
        if (is_string($newData)) {
            $newData = array($newData => $newval);
        }
        if (sizeof($newData) > 0) {
            foreach ($newData as $key => $val) {
                $_SESSION[$prefix . $key] = $val;
            }
        }
        $this->metaData->buildUserData($newData);
    }

    /**
     * Delete a session variable from the $_SESSION
     *
     * @param mixed  $newData key or array
     * @param string $prefix  sesison key prefix
     * 
     * @return void
     */
    public function remove($newData = array(), $prefix = '')
    {
        if (is_string($newData)) {
            $newData = array($newData => '');
        }
        if (sizeof($newData) > 0) {
            foreach ($newData as $key => $val) {
                $val = null;
                unset($_SESSION[$prefix . $key]);
            }
        }
        if (sizeof($_SESSION) == 0) {               // If metadata option closed and when we want to unset() data we couldn't remove the last session key from storage.
            $this->handler->destroy(session_id());  // This solution fix the issue.
        }
    }

    /**
     * Get the "now" time
     *
     * @return string
     */
    public function getTime()
    {
        $time = time();
        if (strtolower($this->config['locale']['timezone']) == 'gmt') {
            $now = time();
            $time = mktime(gmdate("H", $now), gmdate("i", $now), gmdate("s", $now), gmdate("m", $now), gmdate("d", $now), gmdate("Y", $now));
        }
        return $time;
    }

    /**
     * Close session writer
     * 
     * @return void
     */
    public function close()
    {
        if ( ! $this->readSession()) {
            $this->metaData->create();
        } else {
            $this->metaData->update();
        }
        session_write_close();
    }
}

// END Session.php File
/* End of file Session.php

/* Location: .Obullo/Session/Session.php */