<?php

namespace Obullo\Translation;

use ArrayAccess, 
    LogicException,
    Obullo\Container\Container;

/**
 * Translator Class
 * 
 * @category  I18n
 * @package   Translator
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/i18n
 */
Class Translator implements ArrayAccess
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Framework logger object
     * 
     * @var object
     */
    public $logger;

    /**
     * Translator config
     * 
     * @var array
     */
    public $translator;

    /**
     * Translate variable
     * 
     * @var array
     */
    public $translate = array();  // Translation array

    /**
     * Translate file is loaded
     * 
     * @var array
     */
    public $isLoaded = array();  // Let we know if its loaded

    /**
     * Current locale code ( en, de, es )
     * 
     * @var string
     */
    public $locale;

    /**
     * Use fallback locale if locale not found
     * 
     * @var string
     */
    public $fallback = 'en';

    /**
     * Locale code cookie name
     * 
     * @var string
     */
    protected $cookieName = 'locale';

    /**
     * Locale code cookie prefix
     * 
     * @var string
     */
    protected $cookiePrefix = '';

    /**
     * Translate array
     * 
     * @var array
     */
    public $translateArray = array();

    /**
     * System translate notice
     */
    const NOTICE = 'translate:';

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;

        /**
         * If cookie not exists ( especially in Cli mode ) we cannot get the default locale
         * so first of all we need set default locale code.
         */
        $this->translator = $this->c['config']->load('translator');  // Get package config file
        $this->locale = $this->translator['locale']['default'];  // Default lang code
        $this->config = $this->c->load('config');  // Get package config file

        $this->setFallback($this->locale);

        $this->cookieName = $this->translator['cookie']['name'];
        $this->cookiePrefix = $this->config['cookie']['prefix'];  // Set cookie prefix

        $this->setDefault(); // Initialize to default language

        $this->logger = $this->c->load('logger');
        $this->logger->debug('Translator Class Initialized');
    }

    /**
     * Check translation exists.
     * 
     * @param string $key translate string
     * 
     * @return boolean returns to false if key not exists
     */
    public function exists($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Sets a parameter or an object.
     *
     * @param string $key   The unique identifier for the parameter
     * @param mixed  $value The value of the parameter
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {        
        $this->translateArray[$key] = $value;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($key)
    {
        if ( ! isset($this->translateArray[$key])) {
            $translateNotice = ($this->translator['notice']) ? static::NOTICE : '';
            return $translateNotice . $key;
        }
        return $this->translateArray[$key];
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return Boolean
     */
    public function offsetExists($key)
    {
        return isset($this->translateArray[$key]);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->translateArray[$key]);
    }

    /**
     * Load a translation file
     * 
     * @param string  $filename filename
     * @param boolean $return   return to array or not
     * 
     * @return object translator
     */
    public function load($filename = '', $return = false)
    {
        $locale = $this->getLocale();
        if (in_array($filename, $this->isLoaded, true)) {
            return $this->translateArray;
        }
        if ( ! is_dir(APP .'translations'. DS .$locale)) {
            throw new LogicException(sprintf('The translator %s path is not a folder.', APP .'translations'. DS .$locale));
        }
        $fileUrl = APP .'translations'. DS .$locale. DS .$filename. '.php';
        $translateArray = include $fileUrl;
        if ( ! isset($translateArray)) {
            $this->logger->error('Translation file does not contain $translate variable: ' . APP .'translations'. DS .$locale. DS .$filename. '.php');
            return;
        }
        if ($return) {
            return $translateArray;
        }
        $this->isLoaded[] = $fileUrl;
        $this->translateArray = array_merge($this->translateArray, $translateArray);
        unset($translateArray);

        $this->logger->debug('Translation file loaded: ' . APP .'translations'. DS .$locale. DS .$filename. '.php');
        return $this;
    }

    /**
     * Get formatted translator item
     * 
     * @return string
     */
    public function sprintf()
    {
        $args = func_get_args();
        $item = $args[0];
        if (strpos($item, 'translate:') === 0) {    // Do we need to translate the message ?
            $item = substr($item, 10);              // Grab the variable
        }
        if (isset($this->translateArray[$item])) {
            if (sizeof($args) > 1) {
                unset($args[0]);
                return vsprintf($this->translateArray[$item], $args);
            }
            return $this->translateArray[$item];
        }
        $translateNotice = ($this->translator['notice']) ? static::NOTICE : '';
        return $translateNotice . $item;  // Let's notice the developers this line has no translate text
    }

    /**
     * Set default translation
     * 
     * @return void
     */
    public function setDefault()
    {
        if ( ! defined('STDIN')) { // Disable console & task errors
            return;
        }
        $cookie = $this->getCookie();

        if ($this->translator['uri']['segment']) {         // Set via URI Segment
            $segment = $this->c->load('uri')->segment($this->translator['uri']['segmentNumber']);
            if ( ! empty($segment)) {
                $bool = ($cookie == $segment) ? false : true; // Do not write if cookie == segment value same
                if ($this->setLocale($segment, $bool)) {
                    return;
                }
            }
        }
        // If have a cookie then set locale using cookie.
        if ( ! empty($cookie)) {              // Check cookie if we have not lang cookie
            $this->setLocale($cookie, false); // Do not write to cookie just set variables.
            return;
        }
        $intl = extension_loaded('intl');
        if ($intl = false) {
            $this->logger->notice('Load php intl extension to enable language auto detect feature using browser.');
        }
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) AND $intl) {   // Set via browser default value
            $this->setLocale(\Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']));
            return;
        }
        $this->setLocale(); // Set from global config file
    }

    /**
     * Get translator class cookie
     * 
     * @return string
     */
    public function getCookie()
    {
        $name = $this->cookiePrefix . $this->cookieName;
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * Set locale name
     * 
     * @param string  $lang        language ( en, es )
     * @param boolean $writeCookie write cookie on / off
     *
     * @return boolean
     */
    public function setLocale($lang = null, $writeCookie = true)
    {
        if ($lang != null AND ! isset($this->translator['languages'][$lang])) {    // If its not in defined languages.
            return false;  // Good bye ..
        }
        $this->locale = empty($lang) ? $this->getFallback() : $lang;

        if ($writeCookie AND $this->translator['locale']['setCookie']) {  // use locale_set_default function ?
            $this->setCookie();  // write to cookie
        }
        return true;
    }

    /**
     * Get the default locale being used.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the fallback locale being used.
     *
     * @return string
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * Set the fallback locale being used.
     *
     * @param string $fallback locale name
     * 
     * @return void
     */
    public function setFallback($fallback)
    {
        $this->fallback = $fallback;
    }

    /**
     * Write to cookies
     *
     * @return void
     */
    public function setCookie()
    {   
        if (defined('STDIN')) {  // Disable command line interface errors
            return;
        }
        $this->cookieDomain = $this->config['cookie']['domain'];
        $this->cookiePath   = $this->config['cookie']['path'];
        $this->expiration   = $this->translator['cookie']['expire'];
        
        setcookie($this->cookiePrefix.$this->cookieName, $this->getLocale(), time() + $this->expiration, $this->cookiePath, $this->cookieDomain, 0);
    }

}

// END Translator.php File
/* End of file Translator.php

/* Location: .Obullo/Translation/Translator.php */