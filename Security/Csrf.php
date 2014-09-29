<?php

namespace Obullo\Security;

/**
 * Csrf Class
 * 
 * @category  Security
 * @package   Csrf
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/security
 */
Class Csrf 
{
     /**
     * Random Hash for Cross Site Request Forgery Protection Cookie
     *
     * @var string
     */
     protected $hash = '';

     /**
     * Expiration time for Cross Site Request Forgery Protection Cookie
     * Defaults to two hours (in seconds)
     *
     * @var int
     */
     protected $expire = 7200;

     /**
     * Token name for Cross Site Request Forgery Protection Cookie
     *
     * @var string
     */
     protected $tokenName = 'csrf_token';

     /**
     * Cookie name for Cross Site Request Forgery Protection Cookie
     *
     * @var string
     */
     protected $cookieName = 'csrf_token';

    /**
     * Constructor
     *
     * @return  void
     */
    public function __construct()
    {
        global $c;
        $this->config   = $c->load('config');
        $this->logger   = $c->load('loggger');
        $this->response = $c->load('response');

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
        if ($this->config['csrf']['protection']) {  // Is CSRF protection enabled?

            $this->expire     = $this->config['csrf']['expire'];
            $this->tokenName  = $this->config['csrf']['token_name'];
            $this->cookieName = $this->config['csrf']['cookie_name'];

            if ($this->config['cookie']['prefix']) { // Append application specific cookie prefix
                $this->cookieName = $this->config['cookie']['prefix'].$this->cookieName;
            }
            $this->setHash();  // Set the CSRF hash
        }
    }

    /**
     * Verify Cross Site Request Forgery Protection
     *
     * @return  object
     */
    public function verify()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') { // If it's not a POST request we will set the CSRF cookie
            return $this->setCookie();
        }
        if ( ! isset($_POST[$this->tokenName], $_COOKIE[$this->cookieName])) {  // Do the tokens exist in both the _POST and _COOKIE arrays ?
            $this->showError();
        }
        if ($_POST[$this->tokenName] != $_COOKIE[$this->cookieName]) { // Do the tokens match? 
            $this->showError();
        }
                                          // We kill this since we're done and we don't want to
                                          // polute the _POST array
        unset($_POST[$this->tokenName]);  // Nothing should last forever     
        unset($_COOKIE[$this->cookieName]);   

        $this->setHash();
        $this->setCookie();

        $this->logger->channel('security');
        $this->logger->debug('Csrf token verified');

        return $this;
    }

    /**
     * Set Cross Site Request Forgery Protection Cookie
     *
     * @return object
     */
    public function setCookie()
    {
        $expire = time() + $this->expire;
        $secureCookie = ($this->config['cookie_secure'] === true) ? 1 : 0;

        if ($secureCookie) {
            global $c;
            if ( ! $c->load('request')->isSecure()) {
                return false;
            }
        }
        setcookie($this->cookieName, $this->hash, $expire, $this->config['cookie_path'], $this->config['cookie_domain'], $secureCookie);

        $this->logger->channel('security');
        $this->logger->debug('Csrf cookie Set');
        return $this;
    }

    /**
     * Show CSRF Error
     *
     * @return  void
     */
    public function showError()
    {
        $this->response->showError('The action you have requested is not allowed.');
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
        return $this->hash;
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
    protected function setHash()
    {
        if ($this->hash == '') {

            // If the cookie exists we will use it's value.
            // We don't necessarily want to regenerate it with
            // each page load since a page could contain embedded
            // sub-pages causing this feature to fail
        
            if (isset($_COOKIE[$this->cookieName]) AND preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[$this->cookieName]) === 1) {
                return $this->hash = $_COOKIE[$this->cookieName];
            }
            return $this->hash = md5(uniqid(rand(), true));
        }
        return $this->hash;
    }

}

// END Csrf Class

// END Csrf.php File
/* End of file Csrf.php

/* Location: .Obullo/Security/Csrf.php */