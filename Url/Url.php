<?php

namespace Obullo\Url;

/**
 * Url Class
 *
 * Modeled after Codeigniter Url helper (✿◠‿◠)
 * 
 * @category  Url
 * @package   Url
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/url
 */
class Url
{
    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->uri = $c['uri'];
        $this->c['logger']->debug('Url Class Initialized');
    }

    /**
     * Anchor Link
     *
     * Creates an anchor based on the local URL.
     *
     * @param string $uri        the URL
     * @param string $title      the link title
     * @param mixed  $attributes any attributes
     * @param bool   $suffix     switch off suffix by manually
     * 
     * @return string
     */
    public function anchor($uri = '', $title = '', $attributes = '', $suffix = true)
    {
        if (strpos($uri, '@WEBHOST') !== false) {
            $uri = str_replace('@WEBHOST', $this->c['config']['url']['webhost'], $uri);
        }
        $siteUrl = $this->getSiteUrl($uri, $suffix);
        
        if (empty($title)) {
            $title = $siteUrl;
        }
        $attributes = ($attributes != '') ? self::parseAttributes($attributes) : '';

        return '<a href="' . $siteUrl . '"' . $attributes . '>' . (string)$title . '</a>';
    }
    
    /**
     * Get site url
     * 
     * @param string $uri    uri
     * @param string $suffix uri suffix
     * 
     * @return string site url
     */
    protected function getSiteUrl($uri, $suffix = true)
    {
        // "?" Question mark support
        // If we have question mark beginning of the  the uri
        // example:  example.com/?service_type=email&user_id=50  replace with:  example.com?service_type=email&user_id=50

        $queryString = strstr($uri, '?');

        if ( ! empty($queryString)) {
            $uri = rtrim(strstr($uri, '?', true), '/').$queryString;
        }
        $siteUri = $this->uri->getSiteUrl($uri, $suffix);

        return ( ! preg_match('!^\w+://! i', $uri)) ? $siteUri : $uri;
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
        if ( ! empty($protocol) || is_bool($protocol)) {
            $uri = preg_replace('#^https?:\/\/#i', '', $uri);
        }
        return $protocol.$uri;
    }
    
    /**
     * Header Redirect
     *
     * Header redirect in two flavors
     * For very fine grained control over headers, you could use the Response
     * package setHeader() function.
     * 
     * @param string  $uri              uri string
     * @param string  $method           method
     * @param integer $httpResponseCode response code
     * @param boolean $suffix           suffix
     * 
     * @return void
     */
    public function redirect($uri = '', $method = 'location', $httpResponseCode = 302, $suffix = true)
    {
        if ($this->c['config']->load('logger')['app']['benchmark'] && isset($_SERVER['REQUEST_TIME_START'])) {     // Do we need to generate benchmark data ?

            $end = microtime(true) - $_SERVER['REQUEST_TIME_START'];  // End Timer
            $usage = 'memory_get_usage() function not found on your php configuration.';
            if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '') {
                $usage = round($usage/1024/1024, 2). ' MB';
            }
            $extra['time'] = number_format($end, 4);
            $extra['memory'] = $usage;
            $this->c['logger']->debug('Redirect header sent to browser', $extra, -99);
        }

        if ( ! preg_match('#^https?:\/\/#i', $uri)) {
            $uri = $this->uri->getSiteUrl($uri, $suffix);
        }
        if (strpos($method, '[')) {
            $index = explode('[', $method);
            $param = str_replace(']', '', $index[1]);
            header("Refresh:$param;url=" . $uri);
            exit;
        }
        switch ($method) {
        case 'refresh' : header("Refresh:0;url=" . $uri);
            break;
        default : header("Location: " . $uri, true, $httpResponseCode);
            break;
        }
        exit;
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
    public static function parseAttributes($attributes, $javascript = false)
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
        if ( ! parse_url($str, PHP_URL_SCHEME)) {
            $str = 'http://' . $str;
        }
        return $str;
    }

}

// END Url Class
/* End of file Url.php

/* Location: .Obullo/Url/Url.php */