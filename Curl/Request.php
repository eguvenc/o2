<?php

namespace Obullo\Curl;

use Obullo\Utils\CaseInsensitiveArray;

/**
 * Curl Request Helper
 * 
 * @category  Curl
 * @package   Utils
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/curl
 */
class Request
{
    /**
     * Curl resource
     * 
     * @var resource
     */
    protected $ch;

    /**
     * Headers
     * 
     * @var array
     */
    protected $headers = array();

    /**
     * Constructor
     * 
     * @param resource $ch         curl resource
     * @param string   $method     method
     * @param string   $body       body
     * @param string   $postParams array
     */
    public function __construct($ch, $method, $body, $postParams = array())
    {
        $this->ch = $ch;
        $this->body = $body;
        $this->method = $method;
        $this->postParams = $postParams;
    }

    /**
     * Returns to request headers
     * 
     * @return array
     */
    public function getHeaders()
    {
        return empty($this->headers) ? $this->parseHeaders(curl_getinfo($this->ch, CURLINFO_HEADER_OUT)) : $this->headers;
    }

    /**
     * Returns to current request method
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get request body
     * 
     * @return string
     */
    public function getBody()
    {
        if (0 === strpos($this->headers['Content-Type'], 'application/x-www-form-urlencoded')) {
            $body = http_build_query($this->postParams, '', '&');
            return str_replace('%7E', '~', $body); // support RFC 3986 by not encoding '~' symbol (request #15368)
        }
        return $this->body;
    }

    /**
     * Parse Request Headers
     *
     * @param string $rawHeader raw request headers
     *
     * @return array
     */
    protected function parseHeaders($rawHeader)
    {
        $requestHeader = new CaseInsensitiveArray();
        list($firstLine, $headers) = $this->parseRawHeaders($rawHeader);

        $requestHeader['Request-Line'] = $firstLine;
        foreach ($headers as $key => $value) {
            $requestHeader[$key] = $value;
        }
        return $requestHeader;
    }

    /**
     * Parse Headers
     * 
     * @param string $rawHeaders raw headers
     *
     * @return array
     */
    protected function parseRawHeaders($rawHeaders)
    {
        $rawHeaders = preg_split('/\r\n/', $rawHeaders, null, PREG_SPLIT_NO_EMPTY);
        $httpHeaders = new CaseInsensitiveArray();

        $rawHeadersCount = count($rawHeaders);
        for ($i = 1; $i < $rawHeadersCount; $i++) {
            list($key, $value) = explode(':', $rawHeaders[$i], 2);
            $key = trim($key);
            $value = trim($value);

            if (isset($httpHeaders[$key])) {  // Use isset() as array_key_exists() and ArrayAccess are not compatible.
                $httpHeaders[$key] .= ',' . $value;
            } else {
                $httpHeaders[$key] = $value;
            }
        }
        return array(isset($rawHeaders['0']) ? $rawHeaders['0'] : '', $httpHeaders);
    }

}