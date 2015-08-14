<?php

namespace Obullo\Curl;

use Obullo\Utils\CaseInsensitiveArray;

/**
 * Curl Response Helper
 * 
 * @category  Curl
 * @package   Utils
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/curl
 */
class Response
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
    protected $headers = [];

    /**
     * Response
     * 
     * @var string
     */
    protected $rawResponse;

    /**
     * Response headers
     * 
     * @var string
     */
    protected $rawResponseHeaders;

    /**
     * Constructor
     * 
     * @param object $ch                 curl
     * @param string $rawResponse        response
     * @param string $rawResponseHeaders response headers
     */
    public function __construct($ch, $rawResponse, $rawResponseHeaders)
    {
        $this->ch = $ch;
        $this->rawResponse = $rawResponse;
        $this->rawResponseHeaders = $rawResponseHeaders;
    }

    /**
     * Curl raw response
     * 
     * @return mixed
     */
    public function getBody()
    {
        return $this->rawResponse;
    }

    /**
     * Returns to curl_error()
     * 
     * @return string
     */
    public function getError()
    {
        return curl_error($this->ch);
    }

    /**
     * Returns to curl_errno() error number if no errors
     * returns "0".
     * 
     * @return integer
     */
    public function getErrorNo()
    {
        return curl_errno($this->ch);
    }

    /**
     * Get all info
     * 
     * @return array
     */
    public function getInfo()
    {
        return curl_getinfo($this->ch);
    }

    /**
     * Returns curl http status code
     * 
     * @return string
     */
    public function getStatusCode()
    {
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }

    /**
     * Returns to response headers
     * 
     * @return array
     */
    public function getHeaders()
    {
        return empty($this->headers) ? $this->parseHeaders($this->rawResponseHeaders) : $this->headers;
    }

    /**
     * Parse Response Headers
     *
     * @param string $rawResponseHeader raw response headers
     *
     * @return array
     */
    protected function parseHeaders($rawResponseHeader)
    {
        $responseHeaderArray = explode("\r\n\r\n", $rawResponseHeader);
        $responseHeader  = '';
        for ($i = count($responseHeaderArray) - 1; $i >= 0; $i--) {
            if (stripos($responseHeaderArray[$i], 'HTTP/') === 0) {
                $responseHeader = $responseHeaderArray[$i];
                break;
            }
        }
        $responseHeaders = new CaseInsensitiveArray();
        list($firstLine, $headers) = $this->parseRawHeaders($responseHeader);

        $responseHeaders['Status-Line'] = $firstLine;
        foreach ($headers as $key => $value) {
            $responseHeaders[$key] = $value;
        }
        return $responseHeaders;
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