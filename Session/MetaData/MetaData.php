<?php

namespace Obullo\Session\MetaData;

use Obullo\Container\Container;

/**
 * MetaData Storage
 * 
 * @category  Session
 * @package   MetaData
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
Class MetaData
{
    /**
     * Time
     * 
     * @var integer
     */
    public $now;

    /**
     * Ip address
     * 
     * @var string
     */
    public $ipAddress;

    /**
     * User Agent
     * 
     * @var string
     */
    public $userAgent;

    /**
     * Session class instance
     * 
     * @var object
     */
    public $session;

    /**
     * Logger class
     * 
     * @var object
     */
    public $logger;

    /**
     * Cache provider
     * 
     * @var object
     */
    public $cache;

    /**
     * Container 
     * 
     * @var object
     */
    protected $c;

    /**
     * Meta data stack
     * 
     * @var array
     */
    protected $meta;

    /**
     * Constructor
     * 
     * @param object $c       container
     * @param object $params  parameters
     * @param object $session session object
     */
    public function __construct(Container $c, $params, $session)
    {
        $this->c = $c;
        $this->params = $params;
        $this->session = $session;
        $this->now = $this->session->getTime();
        $this->ipAddress = $this->c['request']->getIpAddress();
        $this->userAgent = $this->c['request']->server('HTTP_USER_AGENT');
        $this->cache = $this->c->load(
            'service provider cache', 
            [
                'driver' => $params['cache']['storage'],
                'serializer' => 'SERIALIZER_NONE'
            ]
        );
        $this->logger = $this->c->load('logger');
    }

    /**
     * Compare meta data with user data if something went 
     * wrong destroy the session and say good bye to user.
     * 
     * @return boolean
     */
    public function isValid()
    {
        $this->meta = $this->read();
        if ( ! isset($this->meta['sid'])
            OR ! isset($this->meta['ip']) 
            OR ! isset($this->meta['ua']) 
            OR ! isset($this->meta['la'])
        ) {
            $this->session->destroy();
            return false;
        }
        if (($this->meta['la'] + $this->params['session']['lifetime']) < $this->now) {  // Is the session current?
            $this->logger->notice('Session expired', array('session_id' => session_id()));
            $this->session->destroy();
            return false;
        }
        if ($this->params['meta']['matchIp'] == true AND $this->meta['ip'] != $this->ipAddress) {  // Does the IP Match?
            $this->logger->notice('Session meta data is not valid', $this->meta);
            $this->session->destroy();
            return false;
        }
        if ($this->params['meta']['matchUserAgent'] == true AND trim($this->meta['ua']) != $this->userAgent) {  // Does the User Agent Match?
            $this->session->destroy();
            return false;
        }
        return true;
    }

    /**
     * Stores meta data into $this->meta variable.
     * 
     * @return void
     */
    public function build()
    {
        $this->meta['sid'] = session_id(); // Don't reset array data like $this->meta = array()
        $this->meta['ip'] = $this->ipAddress;
        $this->meta['ua'] = $this->userAgent;
        $this->meta['la'] = $this->now; // last activity
    }

    /**
     * When first initializiation of session we create the session user 
     * meta data and we could not reach "user_id" and "username" items from $_SESSION variable.
     * Thats why we need to use this function in $this->set() method.
     * 
     * @param array $new new session set data
     * 
     * @return void
     */
    public function buildUserData($new = array())
    {
        if (isset($new['user_id'])) {
            $this->meta['uid'] = $new['user_id'];
        }
        if (isset($new['username'])) {
            $this->meta['uname'] = $new['username'];
        }
        if ($user_id = $this->session->get('user_id')) {
            $this->meta['uid'] = $user_id;
        }
        if ($username = $this->session->get('username')) {
            $this->meta['uname'] = $username;
        }
    }

    /**
     * Get latest meta 
     * 
     * @return array meta
     */
    public function getmeta()
    {
        return $this->meta;
    }

    /**
     * Create meta data
     * 
     * @return void
     */
    public function create()
    {
        $this->build();
        $this->buildUserData();
        $_SESSION['_o2_meta'] = json_encode($this->meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update meta data
     * 
     * @return void
     */
    public function update()
    {
        if (($this->meta['la'] + $this->params['session']['timeToUpdate']) >= $this->now) {  // We only update the session every 5 seconds by default
            return;
        }
        $this->buildUserData();
        $this->meta['la'] = $this->now; // Update the session ID and la
        $this->create($this->meta);
    }

    /**
     * Remove meta data
     * 
     * @return void
     */
    public function remove()
    {
        unset($_SESSION['_o2_meta']);
    }

    /**
     * Read meta data
     * 
     * @return array
     */
    public function read()
    {
        if (isset($_SESSION['_o2_meta'])) {
            return json_decode($_SESSION['_o2_meta'], true);
        }
        return array();
    }
}

// END MetaData.php File
/* End of file MetaData.php

/* Location: .Obullo/Session/MetaData/MetaData.php */