<?php

namespace Obullo\Session\MetaData;

use Obullo\Log\LoggerInterface;
use Obullo\Session\SessionInterface;
use Obullo\Http\Request\RequestInterface;

/**
 * MetaData Storage
 * 
 * @category  Session
 * @package   MetaData
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
class MetaData implements MetaDataInterface
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
     * Service parameters
     * 
     * @var array
     */
    protected $params;

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
     * @param object $session \Obullo\Session\SessionInterface
     * @param object $logger  \Obullo\Log\LogInterface
     * @param object $request \Obullo\Http\Request\RequestInterface
     * @param array  $params  service parameters
     */
    public function __construct(SessionInterface $session, LoggerInterface $logger, RequestInterface $request, array $params)
    {
        $this->logger  = $logger;
        $this->session = $session;
        $this->params  = $params;

        $this->now = $this->session->getTime();
        $this->ipAddress = $request->getIpAddress();
        $this->userAgent = substr($request->server('HTTP_USER_AGENT'), 0, 50);
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

        if (! isset($this->meta['sid'], $this->meta['ip'], $this->meta['ua'], $this->meta['la'])) {
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
        if (($this->meta['la'] + $this->params['storage']['lifetime']) < $this->now) {
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
        if ($this->params['meta']['matchIp'] == true && $this->meta['ip'] != $this->ipAddress) {
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
        if ($this->params['meta']['matchUserAgent'] == true && trim($this->meta['ua']) != $this->userAgent) {
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
        if (($this->meta['la'] + $this->params['meta']['refresh']) >= $this->now) {  // We only update the session every 5 seconds by default
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