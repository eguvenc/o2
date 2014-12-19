<?php

namespace Obullo\Url;

/**
 * Url Class
 *
 * Modeled after Codeigniter Url helper.
 * 
 * @category  Url
 * @package   Url
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/url
 */
Class Url
{
    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->uri = $c->load('uri');
        $c->load('service/logger')->debug('Url Class Initialized');
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
        if (strpos($uri, '@HOST') !== false) {
            $uri = str_replace('@HOST', $this->c['config']['url']['host'], $uri);
        }
        $siteUri = $this->uri->getSiteUrl($uri, $suffix);

        // "?" Question mark support
        // If we have question mark beginning of the  the uri
        // example:  example.com/?service_type=email&user_id=50
        // replace with:  example.com?service_type=email&user_id=50

        if (strpos(trim($uri, '/'), '?') === 0) { 
            $siteUri = (strpos($uri, '/') === 0) ? $siteUri : trim($siteUri, '/');
        }
        $siteUrl = ( ! preg_match('!^\w+://! i', $uri)) ? $siteUri : $uri;
        if ($title == '') {
            $title = $siteUrl;
        }
        if ($attributes != '') {
            $attributes = self::parseAttributes($attributes);
        }
        return '<a href="' . $siteUrl . '"' . $attributes . '>' . (string)$title . '</a>';
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
        if ( ! preg_match('#^https?://#i', $uri)) {
            $uri = $this->uri->getSiteUrl($uri, $suffix);
        }
        if (strpos($method, '[')) {
            $index = explode('[', $method);
            $param = str_replace(']', '', $index[1]);
            header("Refresh:$param;url=" . $uri);
            return;
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
        if ($javascript == true AND $att != '') {
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
        if ($str == 'http://' OR $str == '') {
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