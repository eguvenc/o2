<?php

namespace Obullo\Curl;

use LogicException;
use RuntimeException;
use InvalidArgumentException;
use Obullo\Utils\CaseInsensitiveArray;
use Obullo\Container\ContainerInterface;

/**
 * Curl Class
 *
 * Modeled after https://github.com/php-curl-class/php-curl-class
 * 
 * @category  Curl
 * @package   Curl
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @author    Ersin Güvenç <eguvenc@gmail.com>
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/curl
 */
class Curl
{
    const DEFAULT_TIMEOUT = 30;

    /**
     * Curl init
     * 
     * @var resource
     */
    protected $ch;

    /**
     * Get or post request fields
     * 
     * @var mixed
     */
    protected $fields;

    /**
     * Request method
     * 
     * @var string
     */
    protected $method;

    /**
     * Headers
     * 
     * @var array
     */
    protected $headers = [];

    /**
     * Cookies
     * 
     * @var array
     */
    protected $cookies = [];

    /**
     * Curl constants
     * 
     * @var array
     */
    protected $constants = [];

    /**
     * Query parameters
     * 
     * @var array
     */
    protected $queryParams = [];

    /**
     * Request headers
     * 
     * @var array
     */
    protected $requestHeaders;

    /**
     * Response headers
     * 
     * @var array
     */
    protected $responseHeaders;

    /**
     * Callback respoonse headers storage
     * 
     * @var string
     */
    protected $rawResponseHeaders;

    /**
     * Curl options
     * 
     * @var array
     */
    protected $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FAILONERROR => true,    // Be slient when we have a http error
        CURLOPT_VERBOSE => true,        // Get output details
    ];

    /**
     * Json errors
     * 
     * @var array
     */
    protected $jsonErrors = [
        JSON_ERROR_UTF8           => 'JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded',
        JSON_ERROR_DEPTH          => 'JSON_ERROR_DEPTH - Maximum stack depth exceeded',
        JSON_ERROR_SYNTAX         => 'JSON_ERROR_SYNTAX - Syntax error, malformed JSON',
        JSON_ERROR_CTRL_CHAR      => 'JSON_ERROR_CTRL_CHAR - Unexpected control character found',
        JSON_ERROR_STATE_MISMATCH => 'JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        if ( ! extension_loaded('curl')) {
            throw new RuntimeException('The cURL IO handler requires the cURL extension to be enabled');
        }
        $this->setOpt(CURLINFO_HEADER_OUT, true);
        $this->setOpt(CURLOPT_HEADERFUNCTION, array($this, 'headerCallback'));
        $this->setTimeout(static::DEFAULT_TIMEOUT);
    }

    /**
     * Initializer
     * 
     * @return void
     */
    public function init()
    {
        if (! is_resource($this->ch)) {
            $this->ch = curl_init();
        }
    }

    /**
     * Set url
     * 
     * @param string $url         request url
     * @param array  $queryParams query parameters
     * 
     * @return void
     */
    public function setUrl($url, array $queryParams = array())
    {
        $this->url = $url;
        $sign = '?';
        if (strpos($url, '?') !== false) {
            $sign = '&';
        }
        if ( ! empty($queryParams)) {
            $queryParams = $sign. http_build_query($queryParams);
            $url = $this->url.$queryParams;
        }
        $this->setOpt(CURLOPT_URL, $url);
        return $this;
    }

    /**
     * Set Header
     *
     * @param string $k key
     * @param string $v value
     *
     * @return object
     */
    public function setHeader($k, $v)
    {
        $this->headers[$k] = $v;
        return $this;
    }

    /**
     * Unset Header
     *
     * @param string $key key
     *
     * @return object
     */
    public function unsetHeader($key)
    {
        unset($this->headers[$key]);
        return $this;
    }

    /**
     * Set option of curl constants
     * 
     * @param string $key key
     * @param mixed  $val value
     * 
     * @link  http://php.net/manual/en/curl.constants.php
     * @return void
     */
    public function setOpt($key, $val)
    {
        $this->options[$key] = $val;
        return $this;
    }

    /**
     * Since this timeout is really for putting a bound on the time
     * we'll set them both to the same. If you need to specify a longer
     * CURLOPT_TIMEOUT, or a tigher CONNECTTIMEOUT, the best thing to
     * do is use the setOpt method for the values individually.
     * 
     * @param int $timeout in seconds
     * 
     * @return void
     */
    public function setTimeout($timeout)
    {
        $this->setOpt(CURLOPT_CONNECTTIMEOUT, $timeout);
        $this->setOpt(CURLOPT_TIMEOUT, $timeout);
        return $this;
    }

    /**
     * Set user agent
     * 
     * $_SERVER['HTTP_USER_AGENT'];
     * string 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:36.0) Gecko/20100101 Firefox/36.0' (length=76)
     * 
     * @param string $agent user agent
     * 
     * @return object
     */
    public function setUserAgent($agent)
    {
        $this->setOpt(CURLOPT_USERAGENT, $agent);
        return $this;
    }

    /**
     * Set referer
     * 
     * @param string $referer url
     *
     * @return object
     */
    public function setReferer($referer)
    {
        $this->setOpt(CURLOPT_REFERER, $referer);
        return $this;
    }

    /**
     * Set Port
     *
     * @param int $port number
     */
    public function setPort($port)
    {
        $this->setOpt(CURLOPT_PORT, intval($port));
    }

    /**
     * Set cookie
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return object
     */
    public function setCookie($key, $value)
    {
        $this->cookies[$key] = $value;
        return $this;
    }

    /**
     * Set Cookie Jar
     * 
     * @param mixed $cookieJar cookie jar
     *
     * @return object
     */
    public function setCookieJar($cookieJar)
    {
        $this->setOpt(CURLOPT_COOKIEJAR, $cookieJar);
        return $this;
    }

    /**
     * Set Basic Authentication
     *
     * @param string $username username
     * @param string $password password
     */
    public function setBasicAuthentication($username, $password = '')
    {
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
    }

    /**
     * Set Digest Authentication
     *
     * @param string $username username
     * @param string $password password
     */
    public function setDigestAuthentication($username, $password = '')
    {
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
    }

    /**
     * Verbose
     * 
     * @param bool $on on / off
     */
    public function verbose($on = true)
    {
        $this->setOpt(CURLOPT_VERBOSE, $on);
    }

    /**
     * Get
     * 
     * @param mixed $data string or array data
     *
     * @return string
     */
    public function get($data = array())
    {
        $this->setUrl($this->url, $data);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->setOpt(CURLOPT_HTTPGET, true);
        return $this->exec();
    }

    /**
     * Post
     *
     * @param array $data data
     * 
     * @return string
     */
    public function post($data = array())
    {
        if (is_array($data) && empty($data)) {
            $this->unsetHeader('Content-Length');
        }
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, $this->buildPostData($data));
        return $this->exec();
    }

    /**
     * Put
     * 
     * @param array $data data
     * 
     * @return string
     */
    public function put($data = array())
    {
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
        $putData = $this->buildPostData($data);
        if (empty($this->options[CURLOPT_INFILE]) && empty($this->options[CURLOPT_INFILESIZE])) {
            $this->setHeader('Content-Length', strlen($putData));
        }
        $this->setOpt(CURLOPT_POSTFIELDS, $putData);
        return $this->exec();
    }

    /**
     * Delete
     *
     * @param mixed $data string or array data
     * 
     * @return string
     */
    public function delete($data = array())
    {
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->setOpt(CURLOPT_POSTFIELDS, $this->buildPostData($data));
        return $this->exec();
    }

    /**
     * Patch
     *
     * @param mixed $data string or array data
     *
     * @return string
     */
    public function patch($data = array())
    {
        $this->unsetHeader('Content-Length');
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PATCH');
        $this->setOpt(CURLOPT_POSTFIELDS, $this->buildPostData($data));
        return $this->exec();
    }

    /**
     * Options
     *
     * @param mixed $data string or array data
     * 
     * @return string
     */
    public function options($data = array())
    {
        $this->unsetHeader('Content-Length');
        $this->setUrl($this->url, $data);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'OPTIONS');
        return $this->exec();
    }

    /**
     * Head
     *
     * @param mixed $data string or array data
     * 
     * @return string
     */
    public function head($data = array())
    {
        $this->setURL($this->url, $data);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'HEAD');
        $this->setOpt(CURLOPT_NOBODY, true);
        return $this->exec();
    }

    /**
     * Build custom method
     * 
     * @param string $method name
     * @param array  $data   post data
     * 
     * @return string
     */
    public function custom($method, $data = array())
    {
        $this->setOpt(CURLOPT_CUSTOMREQUEST, strtoupper($method));
        $this->setOpt(CURLOPT_POSTFIELDS, $this->buildPostData($data));
        return $this->exec();
    }

    /**
     * Buil post data
     * 
     * @param array $data data
     * 
     * @return array
     */
    protected function buildPostData($data)
    {
        return http_build_query($data);
    }

    /**
     * Finalize headers
     * 
     * @return void
     */
    protected function finalizeHeaders()
    {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[$key] = $key .':'. $value;
        }
        if ( ! empty($headers)) {
            print_r($headers);
            $this->setOpt(CURLOPT_HTTPHEADER, $headers);
        }
        if ( ! empty($this->cookies)) {
            $this->setOpt(CURLOPT_COOKIE, str_replace(' ', '%20', urldecode(http_build_query($this->cookies, '', '; '))));
        }
    }

    /**
     * Execute curl
     * 
     * @return object Response
     */
    protected function exec()
    {
        $this->init();
        $this->finalizeHeaders();
        curl_setopt_array(
            $this->ch,
            $this->options
        );
        $this->rawResponse = curl_exec($this->ch);
        return $this;
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
     * Return to request headers
     * 
     * @return string
     */
    public function getRequestHeaders()
    {
        return ($this->requestHeaders == null) ? $this->parseRequestHeaders(curl_getinfo($this->ch, CURLINFO_HEADER_OUT)) : $this->requestHeaders;
    }

    /**
     * Returns to response headers
     * 
     * @return array
     */
    public function getResponseHeaders()
    {
        return ($this->responseHeaders == null) ? $this->parseResponseHeaders($this->rawResponseHeaders) : $this->responseHeaders;
    }

    /**
     * Wrapper for JSON decode that implements error detection with helpful
     * error messages.
     *
     * @param string $json    JSON data to parse
     * @param bool   $assoc   When true, returned objects will be converted
     *                        into associative arrays.
     * @param int    $depth   User specified recursion depth.
     * @param int    $options Bitmask of JSON decode options.
     *
     * @link   http://www.php.net/manual/en/function.json-decode.php
     * @throws InvalidArgumentException if the JSON cannot be parsed.
     * @return mixed
     */
    public function jsonDecode($json, $assoc = false, $depth = 512, $options = 0)
    {
        $data = json_decode($json, $assoc, $depth, $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $last = json_last_error();
            throw new InvalidArgumentException(
                'Unable to parse JSON data: '
                . (isset($jsonErrors[$last])
                    ? $jsonErrors[$last]
                    : 'Unknown error')
            );
        }
        return $data;
    }

    /**
     * Close curl connection.
     * 
     * @return void
     */
    public function close()
    {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
            $this->reset();
        }
    }

    /**
     * Reset curl array
     * 
     * @return void
     */
    protected function reset()
    {
        $this->options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FAILONERROR => true,    // Be slient when we have a http error
            CURLOPT_VERBOSE => true,        // Get output details
        ];
        $this->headers = array();
    }

    /**
     * Header Callback
     *
     * @param object $ch     curl
     * @param string $header part
     *
     * @return integer
     */
    public function headerCallback($ch, $header)
    {
        $this->rawResponseHeaders .= $header;
        return strlen($header);
    }

    /**
     * Parse Request Headers
     *
     * @param string $rawHeader raw request headers
     *
     * @return array
     */
    protected function parseRequestHeaders($rawHeader)
    {
        $requestHeader = new CaseInsensitiveArray();
        list($firstLine, $headers) = $this->parseHeaders($rawHeader);

        $requestHeader['Request-Line'] = $firstLine;
        foreach ($headers as $key => $value) {
            $requestHeader[$key] = $value;
        }
        return $requestHeader;
    }

    /**
     * Parse Response Headers
     *
     * @param string $rawResponseHeader raw response headers
     *
     * @return array
     */
    protected function parseResponseHeaders($rawResponseHeader)
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
        list($firstLine, $headers) = $this->parseHeaders($responseHeader);

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
    protected function parseHeaders($rawHeaders)
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

    /**
     * Close the connections
     */
    public function __destruct()
    {
        $this->close();
        $this->ch = null;
    }

}

// END Curl.php File
/* End of file Curl.php

/* Location: .Obullo/Curl/Curl.php */