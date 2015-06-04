<?php

namespace Obullo\Session\MetaData;

use Obullo\Session\Session;
use Obullo\Container\ContainerInterface;

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
class MetaData
{
    /**
     * Unix time
     * 
     * @var integer
     */
    protected $now;

    /**
     * Meta data stack
     * 
     * @var array
     */
    protected $meta;

    /**
     * Cache provider
     * 
     * @var object
     */
    protected $cache;

    /**
     * Configurations
     * 
     * @var array
     */
    protected $config;

    /**
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Ip address
     * 
     * @var string
     */
    protected $ipAddress;

    /**
     * User Agent
     * 
     * @var string
     */
    protected $userAgent;

    /**
     * Meta validation return value
     * 
     * @var boolean
     */
    protected $return = true;

    /**
     * Constructor
     * 
     * @param object $c       container
     * @param object $session \Obullo\Session\Session
     */
    public function __construct(ContainerInterface $c, Session $session)
    {
        $this->session = $session;
        $this->logger = $c['logger'];
        $this->config = $c['config']->load('session');

        $this->now = $this->session->getTime();
        $this->ipAddress = $c['request']->getIpAddress();
        $this->userAgent = substr($c['request']->server('HTTP_USER_AGENT'), 0, 50);
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

        if ( ! isset($this->meta['sid'], $this->meta['ip'], $this->meta['ua'], $this->meta['la'])) {
            $this->session->destroy();
            return false;
        }
        $this->checkSessionIsExpired();
        $this->checkSessionIpAddress();
        $this->checkSessionUserAgent();

        if ($this->return == false) {
            $this->session->destroy();
        }
        return $this->return;
    }

    /**
     *  Is the session current ?
     * 
     * @return bool|void
     */
    protected function checkSessionIsExpired()
    {
        if (($this->meta['la'] + $this->config['storage']['lifetime']) < $this->now) {
            $this->logger->notice('Session expired', array('session_id' => session_id()));
            $this->return = false;
        }
    }

    /**
     * Does the IP Match ?
     * 
     * @return bool|void
     */
    protected function checkSessionIpAddress()
    {
        if ($this->config['meta']['matchIp'] == true AND $this->meta['ip'] != $this->ipAddress) {
            $this->logger->notice('Session meta data is not valid', $this->meta);
            $this->return = false;
        }
    }

    /**
     * Does the User Agent Match ?
     * 
     * @return bool|void
     */
    protected function checkSessionUserAgent()
    {
        if ($this->config['meta']['matchUserAgent'] == true AND trim($this->meta['ua']) != $this->userAgent) {
            $this->logger->notice('Session user agent is not valid', array('session_id' => session_id()));
            $this->return = false;
        }
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
     * Create meta data
     * 
     * @return void
     */
    public function create()
    {
        $this->build();
        $_SESSION['o_meta'] = json_encode($this->meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update meta data
     * 
     * @return void
     */
    public function update()
    {
        if (($this->meta['la'] + $this->config['meta']['refresh']) >= $this->now) {  // We only update the session every 5 seconds by default
            return;
        }
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
        unset($_SESSION['o_meta']);
    }

    /**
     * Read meta data
     * 
     * @return array
     */
    public function read()
    {
        if (isset($_SESSION['o_meta'])) {
            return json_decode($_SESSION['o_meta'], true);
        }
        return array();
    }
}

// END MetaData.php File
/* End of file MetaData.php

/* Location: .Obullo/Session/MetaData/MetaData.php */