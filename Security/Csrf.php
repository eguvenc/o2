<?php

namespace Obullo\Security;

use Obullo\Container\Container;

/**
 * Csrf Class
 *
 * About csrf protection
 *
 * http://shiflett.org/articles/cross-site-request-forgeries
 * http://blog.beheist.com/csrf-protection-in-codeigniter-2-0-a-closer-look/
 * 
 * @category  Security
 * @package   Csrf
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/security
 */
class Csrf 
{
     /**
      * Session class
      * 
      * @var object
      */
     protected $session;

     /**
      * Token refresh seconds
      * 
      * @var integer
      */
     protected $refresh;

     /**
     * Token name for Cross Site Request Forgery Protection
     *
     * @var string
     */
     protected $tokenName = 'csrf_token';

     /**
      * Token session data
      * 
      * @var array | false
      */
     protected $tokenData;

    /**
     * Constructor
     *
     * @param object $c container 
     * 
     * @return  void
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->logger = $c['logger'];
        $this->session = $c['session'];
        
        $this->config = $c['config']->load('security');
        $this->refresh = $this->config['csrf']['token']['refresh'];
        $this->tokenName = $this->config['csrf']['token']['name'];
        $this->init();

        $this->logger->channel('security');
        $this->logger->debug('Csrf Class Initialized');
    }

    /**
     * Initalizer csrf
     * 
     * @return void
     */
    public function init()
    {
        $this->tokenData = $this->session->get($this->tokenName);
    }

    /**
     * Verify Cross Site Request Forgery Protection
     *
     * @return boolean
     */
    public function verify()
    {
        if ($this->c['request']->method() !== 'POST') { // If it's not a POST request we will set the CSRF token
            return $this->setSession();
        }
        if ( ! isset($_POST[$this->tokenName]) 
            OR ! isset($this->tokenData['value'])
            OR ($_POST[$this->tokenName] != $this->tokenData['value'])
        ) {
            return false;
        }
                                          // We kill this since we're done and we don't want to
                                          // polute the _POST array
        unset($_POST[$this->tokenName]);  // Nothing should last forever

        $this->refreshToken();

        $this->logger->channel('security');
        $this->logger->debug('Csrf token verified');

        return true;
    }

    /**
     * Set Cross Site Request Forgery Protection Cookie
     *
     * @return object
     */
    public function setSession()
    {
        if (empty($this->tokenData['value'])) {
            $this->tokenData = ['value' => $this->generateHash(), 'time' => time()];
            $this->session->set($this->tokenName, $this->tokenData);

            $this->logger->channel('security');
            $this->logger->debug('Csrf token session set');
        }
        return $this;
    }

    /**
     * Check csrf time every "x" seconds and update the
     * session if token expired.
     * 
     * @return void
     */
    protected function refreshToken()
    {
        $tokenRefresh = strtotime('- '.$this->refresh.' seconds'); // Create a old time belonging to refresh seconds.

        if (isset($this->tokenData['time']) AND $tokenRefresh > $this->tokenData['time']) {  // Refresh token
            $this->tokenData = array();  // Reset data for update the token
            $this->setSession();
        }
        return $this->getToken();
    }

    /**
     * Get CSRF Hash
     *
     * Getter Method
     *
     * @return string
     */
    public function getToken()
    {
        return $this->tokenData['value'];
    }

    /**
     * Get CSRF Token Name
     *
     * Getter Method
     *
     * @return string csrf token name
     */
    public function getTokenName()
    {
        return $this->tokenName;
    }

    /**
     * Set Cross Site Request Forgery Protection Cookie
     * 
     * @return string
     */
    protected function generateHash()
    {
        return md5(uniqid(rand(), true));
    }

}

// END Csrf Class

// END Csrf.php File
/* End of file Csrf.php

/* Location: .Obullo/Security/Csrf.php */