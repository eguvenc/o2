<?php

namespace Obullo\Utils\Curl;

use RuntimeException;
use InvalidArgumentException;

use Obullo\Utils\Curl\Request;
use Obullo\Utils\Curl\Response;
use Obullo\Application\Application;
use Obullo\Utils\CaseInsensitiveArray;

/**
 * Curl Helper
 * 
 * @category  Curl
 * @package   Utils
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/curl
 */
class Client
{
    const TIMEOUT = 30;

    protected $ch;                  // Curl init
    protected $fields;              // Get or post request fields
    protected $method;              // Request method
    protected $headers = [];        // Headers
    protected $body = null;         // Body content
    protected $cookies = [];        // Cookies
    protected $constants = [];      // Curl constants
    protected $queryParams = [];    // Query parameters
    protected $postParams = [];     // Post parameters
    protected $rawResponseHeaders;  // Callback respoonse headers storage

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
        if (! extension_loaded('curl')) {
            throw new RuntimeException('The cURL IO handler requires the cURL extension to be enabled');
        }
        $this->headers = new CaseInsensitiveArray;
        $this->setOpt(CURLINFO_HEADER_OUT, true);
        $this->setOpt(CURLOPT_HEADERFUNCTION, array($this, 'headerCallback'));
        $this->setUserAgent(Application::version());
        $this->setTimeout(static::TIMEOUT);
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
    protected function setUrl($url, array $queryParams = array())
    {
        $this->url = $url;
        $sign = '?';
        if (strpos($url, '?') !== false) {
            $sign = '&';
        }
        if (! empty($queryParams)) {
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
    public function setHeader($k, $v = null)
    {
        if (is_array($k)) {
            foreach ($k as $key => $value) {
                $this->headers[$key] = $value;
            }
        } else {
            $this->headers[$k] = $v;
        }
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
     * @link http://php.net/manual/en/curl.constants.php
     * 
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
     *
     * @return object
     */
    public function setPort($port)
    {
        $this->setOpt(CURLOPT_PORT, intval($port));
        return $this;
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
     * Set Authentication
     *
     * @param string $username username
     * @param string $password password
     * @param string $type     Basic / Digest
     * 
     * @return object
     */
    public function setAuth($username, $password = '', $type = 'basic')
    {
        $method = 'set'.ucfirst($type).'Authentication';
        return $this->$method($username, $password);
    }

    /**
     * Set Basic Authentication
     *
     * @param string $username username
     * @param string $password password
     * 
     * @return object
     */
    protected function setBasicAuthentication($username, $password = '')
    {
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
        return $this;
    }

    /**
     * Set Digest Authentication
     *
     * @param string $username username
     * @param string $password password
     *
     * @return object
     */
    protected function setDigestAuthentication($username, $password = '')
    {
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
        return $this;
    }

    /**
     * Verbose
     * 
     * @param bool $on on / off
     * 
     * @return object
     */
    public function setVerbose($on = true)
    {
        $this->setOpt(CURLOPT_VERBOSE, $on);
        return $this;
    }

    /**
     * Get
     * 
     * @param string $url         url
     * @param mixed  $queryParams array data
     *
     * @return string
     */
    public function get($url, $queryParams = array())
    {
        $this->method = 'GET';
        $this->setUrl($url, $queryParams);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->setOpt(CURLOPT_HTTPGET, true);
        return $this->exec();
    }

    /**
     * Post ( Insert )
     *
     * @param string $url        url
     * @param mixed  $postFields array data
     * 
     * @return string
     */
    public function post($url, $postFields = array())
    {
        $this->method = 'POST';
        if (is_array($postFields) && empty($postFields)) {
            $this->unsetHeader('content-length');
        }
        $this->setUrl($url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, $this->buildPostData($postFields));
        return $this->exec();
    }

    /**
     * Put ( Update )
     * 
     * @param string $url  url
     * @param mixed  $data array data
     * 
     * @return string
     */
    public function put($url, $data = null)
    {
        $this->method = 'PUT';
        $this->setUrl($url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');

        $putData = $this->buildPostData($data);
        if (empty($this->options[CURLOPT_INFILE]) && empty($this->options[CURLOPT_INFILESIZE])) {
            $this->setHeader('content-length', strlen($putData));
        }
        $this->setOpt(CURLOPT_POSTFIELDS, $putData);
        return $this->exec();
    }

    /**
     * Delete
     *
     * @param string $url        url
     * @param mixed  $postFields array data
     * 
     * @return string
     */
    public function delete($url, $postFields = array())
    {
        $this->method = 'DELETE';
        $this->setUrl($url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->setOpt(CURLOPT_POSTFIELDS, $this->buildPostData($postFields));
        return $this->exec();
    }

    /**
     * Patch
     *
     * @param string $url  url
     * @param mixed  $data array data
     * 
     * @return string
     */
    public function patch($url, $data = null)
    {
        $this->method = 'PATCH';
        $this->setUrl($url);
        $this->unsetHeader('content-length');
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PATCH');
        $this->setOpt(CURLOPT_POSTFIELDS, $data);
        return $this->exec();
    }

    /**
     * Options
     *
     * @param string $url         url
     * @param mixed  $queryParams array postFields
     * 
     * @return string
     */
    public function options($url, $queryParams = array())
    {
        $this->method = 'OPTIONS';
        $this->unsetHeader('Content-Length');
        $this->setUrl($url, $queryParams);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'OPTIONS');
        return $this->exec();
    }

    /**
     * Head ( Like GET )
     *
     * @param string $url         url
     * @param mixed  $queryParams array data
     * 
     * @return string
     */
    public function head($url, $queryParams = array())
    {
        $this->method = 'HEAD';
        $this->setURL($url, $queryParams);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'HEAD');
        $this->setOpt(CURLOPT_NOBODY, true);
        return $this->exec();
    }

    /**
     * Build custom method
     * 
     * @param string $method name
     * @param string $url    name
     * @param array  $data   post data
     * 
     * @return string
     */
    public function createRequest($method, $url, $data = null)
    {
        $this->method = ucfirst($method);
        $this->setURL($url);
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
        $this->postParams = $data;
        if (isset($data['body'])) {
            $this->detectContentType($data['body']);
            return $data['body'];
        }
        if ($this->body != null) {
            $this->detectContentType($this->body);
            return $this->body;
        }
        if (isset($this->headers['content-type'])  
            && 0 === strpos($this->headers['content-type'], 'multipart/form-data')  // If we have multipart header
        ) {
            return $data;
        }
        return $this->buildRawData($data);
    }

    /**
     * Detect content type
     * 
     * @param string $body body
     * 
     * @return void
     */
    protected function detectContentType($body)
    {
        if (is_object(json_decode($body))) {  // Is Json ?
            $this->setHeader('content-type', 'application/json; charset=utf-8');
        } elseif (false !== simplexml_load_string($body)) {
            $this->setHeader('content-type', 'application/xml; charset=utf-8');
        } else {
            $this->setHeader('content-type', 'text/plain'); // http://stackoverflow.com/questions/871431/raw-post-using-curl-in-php
        }
        $this->setHeader('content-length', strlen($body));
    }

    /**
     * Build raw data
     * 
     * @param mixed $data data
     * 
     * @return string
     */
    protected function buildRawData($data)
    {
        return http_build_query($data, '', '&');
    }

    /**
     * Set body
     * 
     * @param string $body plain-text
     * 
     * @return object
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
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
            $name = implode('-', array_map('ucfirst', explode('-', $key)));
            $headers[$key] = $name .':'. $value;
        }
        if (! empty($headers)) {
            $this->setOpt(CURLOPT_HTTPHEADER, $headers);
        }
        if (! empty($this->cookies)) {
            $this->setOpt(CURLOPT_COOKIE, str_replace(' ', '%20', urldecode(http_build_query($this->cookies, '', '; '))));
        }
    }

    /**
     * Execute curl
     * 
     * @return object
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
        $this->body = null;  // Reset body data
        return $this;
    }

    /**
     * Returns response or request objects
     * 
     * @param string $key object name
     * 
     * @return object|null
     */
    public function __get($key)
    {
        if ($key == 'request') {
            return new Request($this->ch, $this->method, $this->body, $this->postParams);
        }
        return new Response($this->ch, $this->rawResponse, $this->rawResponseHeaders);
    }

    /**
     * Echo raw response without using response class
     * 
     * @return string
     */
    public function getBody()
    {
        return $this->rawResponse;
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
        $ch = null;
        $this->rawResponseHeaders .= $header;
        return strlen($header);
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