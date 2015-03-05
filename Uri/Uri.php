<?php

namespace Obullo\Uri;

use Obullo\Http\Sanitizer;
use Obullo\Container\Container;

/**
 * Uri Class
 *
 * Modeled after Codeigniter Uri class.
 * 
 * @category  Http
 * @package   Uri
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/uri
 */
Class Uri
{
    public $c;
    public $logger;
    public $keyval = array();
    public $uriString;
    public $segments = array();
    public $rsegments = array();
    public $uriExtension = '';
    public $uriProtocol = 'REQUEST_URI';

    /**
     * Constructor
     *
     * @param object $c container
     * 
     * Simply globalizes the $RTR object.  The front
     * loads the Router class early on so it's not available
     * normally as other classes are.
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $c['config'];
        $this->logger = $c['logger'];

        $this->logger->debug('Uri Class Initialized', array(), 8); // Warning : Don't load any library in __construct level you may get a Fatal Error.
    }

    /**
     * Get the URI String
     *
     * @return string
     */
    public function fetchUriString()
    {
        if ($this->uriString != '') {  // Don't run again if we have uri string
            return;
        }
        $protocol = $this->config['uri']['protocol'];
        
        if (strtoupper($protocol) == 'AUTO') {
            if ($uri = $this->detectUri()) {           // Let's try the REQUEST_URI first, this will work in most situations
                $this->uriProtocol = 'REQUEST_URI';
                $this->setUriString($uri);
                return;
            }
            // Is there a PATH_INFO variable?
            // Note: some servers seem to have trouble with getenv() so we'll test it two ways
            $path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');

            if (trim($path, '/') != '' AND $path != "/" . SELF) {
                $this->uriProtocol = 'PATH_INFO';
                $this->setUriString($path);
                return;
            }
            $path = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING'); // No PATH_INFO?... What about QUERY_STRING?

            if (trim($path, '/') != '') {
                $this->uriProtocol = 'QUERY_STRING';
                $this->setUriString($path);
                return;
            }
            if (is_array($_GET) AND count($_GET) == 1 AND trim(key($_GET), '/') != '') { // As a last ditch effort lets try using the $_GET array
                $this->setUriString(key($_GET));
                return;
            }
            $this->uriString = ''; // We've exhausted all our options...
            return;
        }
        $uri = strtoupper($protocol);

        if ($uri == 'REQUEST_URI') {
            $this->setUriString($this->detectUri());
            return;
        }
        $path = (isset($_SERVER[$uri])) ? $_SERVER[$uri] : @getenv($uri);
        $this->setUriString($path);
    }

    /**
     * Parse the REQUEST_URI
     *
     * Due to the way REQUEST_URI works it usually contains path info
     * that makes it unusable as URI data.  We'll trim off the unnecessary
     * data, hopefully arriving at a valid URI that we can use.
     *
     * @return string
     */
    public function detectUri()
    {
        if ( ! isset($_SERVER['REQUEST_URI']) OR ! isset($_SERVER['SCRIPT_NAME'])) {
            return '';
        }
        $uri = $_SERVER['REQUEST_URI'];

        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

        if (strncmp($uri, '?/', 2) === 0) {     // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
            $uri = substr($uri, 2);             // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
        }
        $parts = preg_split('#\?#i', $uri, 2);
        $uri = $parts[0];

        if (isset($parts[1])) {
            $_SERVER['QUERY_STRING'] = $parts[1];
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        } else {
            $_SERVER['QUERY_STRING'] = '';
            $_GET = array();
        }
        if ($uri == '/' || empty($uri)) {
            return '/';
        }
        $uri = parse_url($uri, PHP_URL_PATH);

        return str_replace(array('//', '../'), '/', trim($uri, '/'));  // Do some final cleaning of the URI and return it
    }

    /**
     * Filter segments for malicious characters
     *
     * @param string $str uri
     * 
     * @return string
     */
    public function filterUri($str)
    {
        // defined STDIN FOR task requests
        // we should not prevent "base64encode" characters in CLI mode
        // the "sync" task controller and some schema libraries use "base64encode" function
        if ($str != '' AND $this->config['uri']['permittedChars'] != '' AND $this->config['uri']['queryStrings'] == false AND ! defined('STDIN')) {

            // preg_quote() in PHP 5.3 escapes -, so the str_replace() and addition of - to preg_quote() is to maintain backwards
            // compatibility as many are unaware of how characters in the permitted_uri_chars will be parsed as a regex pattern

            if ( ! preg_match('|^[' . str_replace(array('\\-', '\-'), '-', preg_quote($this->config['uri']['permittedChars'], '-')) . ']+$|i', $str)) {
                $this->c['response']->showError('The URI you submitted has disallowed characters.', 400);
            }
        }
        // Convert programatic characters to entities and return
        return str_replace(
            array(
                '$', '(', ')', '%28', '%29'
            ), // Bad
            array(
                '&#36;', '&#40;', '&#41;', '&#40;', '&#41;'
            ), // Good
            $str
        );
    }

    /**
     * Remove the suffix from the URL if needed
     *
     * @return void
     */
    public function removeUrlSuffix()
    {
        if ($this->config['url']['rewrite']['suffix'] != '') {
            $this->uriString = preg_replace("|" . preg_quote($this->config['url']['rewrite']['suffix']) . "$|", '', $this->uriString);
        }
    }

    /**
     * Explode the URI Segments. The individual segments will
     * be stored in the $this->segments array.
     *
     * @return void
     */
    public function explodeSegments()
    {
        foreach (explode('/', preg_replace("|/*(.+?)/*$|", "\\1", $this->uriString)) as $val) {
            $val = trim($this->filterUri($val)); // Filter segments for security
            if ($val != '') {
                $this->segments[] = $this->parseSegmentExtension($val);
            }
        }
    }

    /**
     * Used in Lvc 
     * 
     * @param string  $str    uri str
     * @param boolean $filter option on / off
     *
     * @return void
     */
    public function setUriString($str = '', $filter = true)
    {
        if ($filter) {  // Filter out control characters
            $str = Sanitizer::sanitizeInvisibleCharacters($str, false);
        }
        $this->uriString = ($str == '/') ? '' : $str;  // If the URI contains only a slash we'll kill it
    }

    /**
     * Fetch a URI Segment
     *
     * This function returns the URI segment based on the number provided.
     * 
     * @param integer $number    number
     * @param boolean $no_result custom no result output
     * 
     * @return string
     */
    public function segment($number, $no_result = false)
    {
        return ( ! isset($this->segments[$number])) ? $no_result : $this->segments[$number];
    }

    /**
     * Segment Array
     * 
     * @return array
     */
    public function getSegments()
    {
        return $this->uri->segments;
    }

    /**
     * Returns to routed segment array
     * 
     * @return array
     */
    public function getRoutedSegments()
    {
        return $this->rsegments;
    }

    /**
     * Fetch a URI "routed" Segment ( Sub module isn't a rsegment based.)
     *
     * This function returns the re-routed URI segment (assuming routing rules are used)
     * based on the number provided.  If there is no routing this function returns the
     * same result as $this->segment()
     * 
     * @param integer $number    segment number
     * @param boolean $no_result custom no result output
     * 
     * @return string
     */
    public function getRoutedSegment($number, $no_result = false)
    {
        return ( ! isset($this->rsegments[$number])) ? $no_result : $this->rsegments[$number];
    }

    /**
     * Get Assets URL
     * 
     * @param string $uri asset uri
     * 
     * @return string
     */
    public function getAssetsUrl($uri = '')
    {
        return rtrim($this->config['url']['assets'], '/') .'/'. ltrim($uri, '/');
    }

    /**
     * Get the current server uri
     * protocol.
     * 
     * @return string
     */
    public function getProtocol()
    {
        return $this->uriProtocol;
    }

    /**
     * Get the complete request uri like native php
     * $_SERVER['REQUEST_URI'].
     * 
     * @param boolean $urlencode encoded url or not
     * 
     * @return string
     */
    public function getRequestUri($urlencode = false)
    {
        if (isset($_SERVER[$this->getProtocol()])) {
            return ($urlencode) ? urlencode($_SERVER[$this->getProtocol()]) : $_SERVER[$this->getProtocol()];
        }
        return false;
    }

    /**
     * Get Base URL
     * 
     * @param string $uri custom uri
     * 
     * @return string
     */
    public function getBaseUrl($uri = '')
    {
        return rtrim($this->config['url']['baseurl'], '/') .'/'. ltrim($uri, '/');
    }

    /**
     * Site URL
     *
     * @param string  $uri_str the URI string
     * @param boolean $suffix  switch off suffix by manually
     * 
     * @return string
     */
    public function getSiteUrl($uri_str = '', $suffix = true)
    {
        if (is_array($uri_str)) {
            $uri_str = implode('/', $uri_str);
        }
        if ($uri_str == '') {
            return $this->getBaseUrl() . $this->config['rewrite']['indexPage'];
        } else {
            $suffix = ($this->config['url']['rewrite']['suffix'] == false OR $suffix == false) ? '' : $this->config['url']['rewrite']['suffix'];
            return $this->getBaseUrl() . $this->config['url']['rewrite']['indexPage'] . trim($uri_str, '/') . $suffix;
        }
    }

    /**
     * Fetch the entire Uri string
     *
     * @return string
     */
    public function getUriString()
    {
        return $this->uriString;
    }

    /**
     * Get current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->getSiteUrl($this->getUriString());
    }

    /**
     * Get extension of uri
     *
     * @return string
     */
    public function extension()
    {
        return $this->uriExtension;
    }

    /**
     * Parse uri string for any possible file
     * extensions
     *
     * @param string $segment segment part
     * 
     * @return string
     */
    protected function parseSegmentExtension($segment)
    {
        if (strpos($segment, '.') !== false) {
            $extension = explode('.', $segment);
            $uriExtension = end($extension);

            if (in_array('.' . $uriExtension, $this->config['uri']['extensions'])) {
                $this->uriExtension = $uriExtension;  // Set extension 
                return rtrim(strstr($segment, $uriExtension, true), '.');  // Remove extension from end of the uri segment
            }
        }
        return $segment;
    }

    /**
     * When we use Layers we need to clean all data.
     *
     * @return void
     */
    public function clear()
    {
        $this->keyval = array();
        $this->uriString = '';
        $this->segments = array();
        $this->rsegments = array();
        $this->uriExtension = '';
    }
}

// END Uri Class
/* End of file Uri.php

/* Location: .Obullo/Uri/Uri.php */