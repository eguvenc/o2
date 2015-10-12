<?php

namespace Obullo\Url;

use Obullo\Container\ContainerInterface;
use Obullo\Config\ConfigInterface;
use Obullo\Log\LoggerInterface;

use Psr\Http\Message\UriInterface;

/**
 * Url Class
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Url implements UrlInterface
{
    /**
     * Constructor
     * 
     * @param ContainerInterface $c      container
     * @param UriInterface       $uri    uri
     * @param ConfigInterface    $config config
     * @param LoggerInterface    $logger config
     */
    public function __construct(ContainerInterface $c, UriInterface $uri, ConfigInterface $config, LoggerInterface $logger)
    {
        $this->c = $c;
        $this->uri = $uri;
        $this->config = $config;
        $this->logger = $logger;
        $this->logger->debug('Url Class Initialized');
    }

    /**
     * Anchor Link
     *
     * Creates an anchor based on the local URL.
     *
     * @param string $uri        the URL
     * @param string $title      the link title
     * @param mixed  $attributes any attributes
     * 
     * @return string
     */
    public function anchor($uri = '', $title = '', $attributes = '')
    {
        $siteUrl = $this->_getSiteUrl($uri);
        
        if (empty($title)) {
            $title = $siteUrl;
        }
        $attributes = ($attributes != '') ? self::parseAttributes($attributes) : '';

        return '<a href="' . $siteUrl . '"' . $attributes . '>' . (string)$title . '</a>';
    }
    
    /**
     * Get site url
     * 
     * @param string $uri uri
     * 
     * @return string site url
     */
    private function _getSiteUrl($uri)
    {
        // "?" Question mark support
        // If we have question mark beginning of the  the uri
        // example:  example.com/?service_type=email&user_id=50  replace with:  example.com?service_type=email&user_id=50

        $queryString = strstr($uri, '?');

        if (! empty($queryString)) {
            $uri = rtrim(strstr($uri, '?', true), '/').$queryString;
        }
        $siteUri = $this->getSiteUrl($uri);

        return ( ! preg_match('!^\w+://! i', $uri)) ? $siteUri : $uri;
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
     * @param string $uriStr the URI string
     * 
     * @return string
     */
    public function getSiteUrl($uriStr = '')
    {
        if (is_array($uriStr)) {
            $uriStr = implode('/', $uriStr);
        }
        if ($uriStr == '') {
            return $this->getBaseUrl() . $this->config['rewrite']['index.php'];
        } 
        return $this->getBaseUrl() . $this->config['url']['rewrite']['index.php'] . trim($uriStr, '/');
    }

    /**
     * Get current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->getSiteUrl($this->getUrl());
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
     * Create static assets urls
     * 
     * @param string $uri      /images/example.png
     * @param mixed  $protocol http:// or https://
     * @param mixed  $url      dynamic url ( overrides to asset url in config )
     * 
     * @return string
     */
    public function asset($uri, $protocol = '', $url = '')
    {
        $url = empty($url) ? $this->c['config']['url']['assets']['url'] : $url;
        $uri = $url.trim($this->c['config']['url']['assets']['folder'], '/').'/'.ltrim($uri, '/');

        if ($protocol == false) {
            $uri = preg_replace('#^https?:\/\/#i', '', $uri);
            $protocol = '';
        }
        if ($protocol == true) {  // Auto detect
            $protocol = ($this->c['request']->isSecure()) ? 'https://' : 'http://';
        }
        if (! empty($protocol) || is_bool($protocol)) {
            $uri = preg_replace('#^https?:\/\/#i', '', $uri);
        }
        return $protocol.$uri;
    }

    /**
     * Parse out the attributes
     *
     * Some of the functions use this
     *
     * @param array $attributes atributes
     * @param bool  $javascript javascript attributes
     * 
     * @return string
     */
    protected static function parseAttributes($attributes, $javascript = false)
    {
        if (is_string($attributes)) {
            return ($attributes != '') ? ' ' . $attributes : '';
        }
        $att = '';
        foreach ($attributes as $key => $val) {
            if ($javascript == true) {
                $att .= $key . '=' . $val . ',';
            } else {
                $att .= ' ' . $key . '="' . $val . '"';
            }
        }
        if ($javascript == true && $att != '') {
            $att = substr($att, 0, -1);
        }
        return $att;
    }
    
    /**
     * Prep URL
     *
     * Simply adds the http:// part if missing
     *
     * @param string $str the URL
     * 
     * @return string
     */
    public function prep($str = '')
    {
        if ($str == 'http://' || $str == '') {
            return '';
        }
        if (! parse_url($str, PHP_URL_SCHEME)) {
            $str = 'http://' . $str;
        }
        return $str;
    }

}