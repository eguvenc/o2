<?php

namespace Obullo\Session;

use Obullo\Container\Container,
    Obullo\Session\MetaData\MetaData,
    Obullo\Session\MetaData\NullMetaData;

/**
 * Session Class
 * 
 * @category  Session
 * @package   Session
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
Class Session
{
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
     * MetaData Class
     * 
     * @var object
     */
    public $meta;

    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     *
     * @param array $c container
     */
    public function __construct(Container $c) 
    {
        $this->c = $c;
        $this->params = $c['config']->load('session');
        $handlerClass = $this->params['default']['handler'];

        $this->handler = new $handlerClass($c, $this->params);
        $this->logger = $c['logger'];

        ini_set('session.cookie_domain', $this->params['cookie']['domain']);

        session_set_save_handler(
            array($this->handler, 'open'),
            array($this->handler, 'close'),
            array($this->handler, 'read'),
            array($this->handler, 'write'),
            array($this->handler, 'destroy'),
            array($this->handler, 'gc')
        );
        $this->meta = ($this->params['meta']['enabled']) ? new MetaData($c, $this->params, $this) : new NullMetaData;

        session_set_cookie_params(
            ($this->params['session']['expireOnClose']) ? 0 : $this->params['session']['lifetime'],
            $this->params['cookie']['path'],
            $this->params['cookie']['domain'],
            $this->params['cookie']['secure'], 
            $this->params['cookie']['httpOnly']
        );
        session_name($this->getName());
        
        if (session_status() == PHP_SESSION_NONE) { // If another session_start() used before ?
            session_start();
        }
        register_shutdown_function(array($this, 'close'));

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
        if ( ! $this->meta->isValid()) { // If meta data is not valid say good bye to user !
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
        $storageLifetime = ($lifetime == null) ? $this->params['session']['lifetime'] : $lifetime;
        $this->handler->setLifetime($storageLifetime);
        $this->remove($oldSessionId);  // Removes old Session id value
        $this->meta->create();

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
        $meta = $this->meta->read();
        if ( ! isset($meta['la'])) {  // la = meta data last activity.
            return false;
        }
        $expire = $this->getTime() - $this->params['session']['lifetime'];
        if ($meta['la'] <= $expire) {
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
     * @param mixed  $new    key or array
     * @param string $newval value
     * @param string $prefix prefix
     * 
     * @return void
     */
    public function set($new = array(), $newval = '', $prefix = '')
    {
        if (is_string($new)) {
            $new = array($new => $newval);
        }
        if (sizeof($new) > 0) {
            foreach ($new as $key => $val) {
                $_SESSION[$prefix . $key] = $val;
            }
        }
    }

    /**
     * Delete a session variable from the $_SESSION
     *
     * @param mixed  $new    key or array
     * @param string $prefix sesison key prefix
     * 
     * @return void
     */
    public function remove($new = array(), $prefix = '')
    {
        if (is_string($new)) {
            $new = array($new => '');
        }
        if (sizeof($new) > 0) {
            foreach ($new as $key => $val) {
                $val = null;
                unset($_SESSION[$prefix . $key]);
            }
        }
        if (sizeof($_SESSION) == 0) {               // If meta option closed and when we want to unset() data we couldn't remove the last session key from storage.
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
        if (strtolower($this->c['config']['locale']['timezone']) == 'gmt') {
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
            $this->meta->create();
        } else {
            $this->meta->update();
        }
        session_write_close();
    }
}

// END Session.php File
/* End of file Session.php

/* Location: .Obullo/Session/Session.php */