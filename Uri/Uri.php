<?php

namespace Obullo\Uri;

use Obullo\Log\LoggerInterface;
use Obullo\Config\ConfigInterface;
use Obullo\Container\ContainerInterface;

/**
 * Uri Class
 *
 * Modeled after Codeigniter Uri class 
 * 
 * @category  Http
 * @package   Uri
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/uri
 */
class Uri
{
    public $config;
    public $logger;
    public $keyval = array();
    public $uriString;
    public $segments = array();
    public $rsegments = array();
    public $uriExtension = '';
    public $uriProtocol = 'REQUEST_URI';

    /**
     * Don't explode segments two times, otherwise default method index will not
     * work.
     * 
     * @var string
     */
    protected $resolvedUri;

    /**
     * Constructor
     *
     * @param object $config \Obullo\Config\ConfigInterface
     * @param object $logger \Obullo\Log\LogInterface
     * 
     * Simply globalizes the $RTR object.  The front
     * loads the Router class early on so it's not available
     * normally as other classes are.
     */
    public function __construct(ConfigInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;

        $this->logger->debug(
            'Uri Class Initialized', 
            array('uri' => $this->getRequestUri()), 
            9999
        );
        // Warning : Don't load any library in __construct level you may get a Fatal Error.
    }

    /**
     * Initialize to URI String
     *
     * @return string
     */
    public function init()
    {
        if ($this->uriString != '') {  // Don't run again if we have uri string
            return;
        }
        $protocol = $this->config['uri']['protocol'];
        empty($protocol) && $protocol = 'REQUEST_URI';  // Default protocol REQUEST_URI

        switch ($protocol)
        {
        case 'REQUEST_URI':
            $uri = $this->parseRequestUri();
            break;

        case 'QUERY_STRING':
            $uri = $this->parseQueryString();
            break;
        case 'PATH_INFO':
        default:
            if (isset($_SERVER[$protocol])) {
                $this->uriProtocol = $protocol;
                $uri = $_SERVER[$protocol];
            }
            $uri = isset($_SERVER[$protocol]) ? $_SERVER[$protocol] : $this->parseRequestUri();
            break;
        }
        $this->setUriString($uri);
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
    protected function parseRequestUri()
    {
        if (! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
            return '';
        }
        list($uri, $query) = $this->parseUrl();

        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }
        $uri = static::detectQueryString($uri, $query);

        if ($uri === '/' || $uri === '') {
            return '/';
        }
        if ($this->config['uri']['sanitizer']) {
            $uri = filter_var(urldecode($uri), FILTER_SANITIZE_URL);  // Filter out control characters
        }
        $this->uriProtocol = 'REQUEST_URI';
        return static::removeRelativeDirectory($uri);  // Do some final cleaning of the URI and return it
    }

    /**
     * Parse server request uri
     * 
     * @return array
     */
    protected function parseUrl()
    {
        $uri = parse_url($_SERVER['REQUEST_URI']);
        $query = isset($uri['query']) ? $uri['query'] : '';
        $uri = isset($uri['path']) ? $uri['path'] : '';
        return array($uri, $query);
    }

    /**
     * Set server query string
     *
     * @param string $uri   request uri
     * @param string $query query
     * 
     * @return string
     */
    protected static function detectQueryString($uri, $query)
    {
        // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
        // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
        
        if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0) {
            $query = explode('?', $query, 2);
            $uri = $query[0];
            $_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
        } else {
            $_SERVER['QUERY_STRING'] = $query;
        }
        parse_str($_SERVER['QUERY_STRING'], $_GET);
        return $uri;
    }

    /**
     * Parse QUERY_STRING
     *
     * Will parse QUERY_STRING and automatically detect the URI from it.
     *
     * @return string
     */
    protected function parseQueryString()
    {
        $uri = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
        if (trim($uri, '/') === '') {
            return '';
        } elseif (strncmp($uri, '/', 1) === 0) {
            $uri = explode('?', $uri, 2);
            $_SERVER['QUERY_STRING'] = isset($uri[1]) ? $uri[1] : '';
            $uri = $uri[0];
        }
        parse_str($_SERVER['QUERY_STRING'], $_GET);
        $this->uriProtocol = 'QUERY_STRING';

        return static::removeRelativeDirectory($uri);
    }
    /**
     * Remove relative directory (../) and multi slashes (///)
     *
     * Do some final cleaning of the URI and return it, currently only used in self::_parse_request_uri()
     *
     * @param string $uri uri string
     * 
     * @return string
     */
    protected static function removeRelativeDirectory($uri)
    {
        $uris = array();
        $tok = strtok($uri, '/');
        while ($tok !== false) {
            if (( ! empty($tok) || $tok === '0') && $tok !== '..') {
                $uris[] = $tok;
            }
            $tok = strtok('/');
        }
        return implode('/', $uris);
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
        if ($this->resolvedUri == $this->uriString) {
            return;
        }
        foreach (explode('/', preg_replace("|/*(.+?)/*$|", "\\1", $this->uriString)) as $val) {
            $val = trim($val);
            if ($val != '') {
                $this->segments[] = $this->parseExtension($val);
            }
        }
        $this->resolvedUri = $this->uriString;
    }

    /**
     * Also used in Layers
     * 
     * @param string $str uri str
     *
     * @return void
     */
    public function setUriString($str = '')
    {
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
    public function segmentArray()
    {
        return $this->segments;
    }

    /**
     * Returns to routed segment array
     * 
     * @return array
     */
    public function routedSegments()
    {
        return $this->rsegments;
    }

    /**
     * Fetch a URI "routed" Segment
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
    public function routedSegment($number, $no_result = false)
    {
        return ( ! isset($this->rsegments[$number])) ? $no_result : $this->rsegments[$number];
    }

    /**
     * Get Assets URL
     * 
     * @param string $uri    asset uri
     * @param string $folder whether to add asset folder
     * 
     * @return string
     */
    public function getAssetsUrl($uri = '', $folder = true)
    {
        $assetsFolder = ($folder) ? trim($this->config['url']['assets']['folder'], '/').'/' : '';
        return $this->config['url']['assets']['url'].$assetsFolder.ltrim($uri, '/');
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
            return $this->getBaseUrl() . $this->config['rewrite']['index.php'];
        } else {
            $suffix = ($this->config['url']['rewrite']['suffix'] == false || $suffix == false) ? '' : $this->config['url']['rewrite']['suffix'];
            return $this->getBaseUrl() . $this->config['url']['rewrite']['index.php'] . trim($uri_str, '/') . $suffix;
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
     * Get current url
     *
     * @return string
     */
    public function getWebHost()
    {
        return trim($this->config['url']['webhost'], '/');
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
    protected function parseExtension($segment)
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
        $this->resolvedUri = '';
    }
}