<?php

namespace Obullo\Http;

use LogicException;
use InvalidArgumentException;

/**
 * Http Client
 * 
 * @category  Http
 * @package   Client
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
class Client
{
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
     * Curl constants
     * 
     * @var array
     */
    protected $constants = [];

    /**
     * Is allowed method
     * 
     * @var array
     */
    protected $allowedMethods = ['get', 'post'];

    /**
     * Curl options
     * 
     * @var array
     */
    protected $curlArray = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
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
            $error = 'The cURL IO handler requires the cURL extension to be enabled';
            $this->c['logger']->error($error);
            throw new LogicException($error);
        }
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
        if (method_exists($this, $this->method)) {
            $this->{$this->method}();
            // $this->curlArray[CURLOPT_CUSTOMREQUEST] = $this->method;
        }
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
    public function reset()
    {
        $this->curlArray = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
    }

    /**
     * Set url
     * 
     * @param string $url request url
     * 
     * @return void
     */
    public function setRequestUrl($url)
    {
        $this->curlArray[CURLOPT_URL] = $url;
        return $this;
    }

    /**
     * Set fields
     * 
     * @param array $fields fields
     * 
     * @return object
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Set method
     * 
     * @param string $method request method
     * 
     * @return object
     */
    public function setMethod($method)
    {
        if (! in_array($method, $this->allowedMethods)) {
            throw new LogicException("You have selected an unsupported method: $method");
        }
        $this->method = $method;
        return $this;
    }

    /**
     * Set post fields
     * 
     * @param array $postFields request post fields
     * 
     * @return void
     */
    public function setPostFields($postFields)
    {
        $this->curlArray[CURLOPT_POST] = 1;
        $this->curlArray[CURLOPT_POSTFIELDS] = $postFields;
        return $this;
    }

    /**
     * Set post fields
     * 
     * @param mixed $getFields request get fields
     * 
     * @return void
     */
    public function setGetFields($getFields)
    {
        if (empty($getFields)) {
            return $this;
        }
        $this->curlArray[CURLOPT_URL] .= '?'. (is_array($getFields) ? http_build_query($getFields) : $getFields);
        return $this;
    }

    /**
     * Set headers
     * 
     * @param array $headers headers
     * 
     * @return void
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $val) {
            if (strpos($val, ':') === false) {
                $headers[$key] = $key .':'. $val;
            }
        }
        $this->curlArray[CURLOPT_HTTPHEADER] = $headers;
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
    public function setOption($key, $val)
    {
        if (empty($this->constants)) {
            $this->constants = get_defined_constants(true)['curl'];
        }
        if (! isset(array_flip($this->constants)[$key])) {
            throw new LogicException(
                'This is not a valid constant.
                Please use one of the constants contained:
                <a href="http://php.net/manual/en/curl.constants.php" target="_blank">http://php.net/manual/en/curl.constants.php</a>'
            );
        }
        $this->curlArray[$key] = $val;
        return $this;
    }

    /**
     * Set the maximum request time in seconds.
     * 
     * @param int $timeout in seconds
     * 
     * @return void
     */
    public function setTimeout($timeout)
    {
        // Since this timeout is really for putting a bound on the time
        // we'll set them both to the same. If you need to specify a longer
        // CURLOPT_TIMEOUT, or a tigher CONNECTTIMEOUT, the best thing to
        // do is use the setOptions method for the values individually.
        $this->curlArray[CURLOPT_CONNECTTIMEOUT] = $timeout;
        $this->curlArray[CURLOPT_TIMEOUT] = $timeout;
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
     * @return void
     */
    public function setUserAgent($agent)
    {
        $this->curlArray[CURLOPT_USERAGENT] = $agent;
        return $this;
    }

    /**
     * Post
     * 
     * @return string
     */
    public function post()
    {
        $this->setPostFields($this->fields);
        if (! isset($this->curlArray[CURLOPT_POSTFIELDS]) || empty($this->curlArray[CURLOPT_POSTFIELDS])) {
            throw new LogicException('You must first set the data using this "setFields()" function.');
        }
    }

    /**
     * Get
     * 
     * @return string
     */
    public function get()
    {
        $this->setGetFields($this->fields);
        if (isset($this->curlArray[CURLOPT_POST]) && $this->curlArray[CURLOPT_POST]) {
            throw new LogicException('You must first delete post data.');
        }
    }

    /**
     * Send
     * 
     * @return void
     */
    public function send()
    {
        $this->init();  // Init curl
        curl_setopt_array(
            $this->ch,
            $this->curlArray
        );
        $result = curl_exec($this->ch);
        $this->close(); // Close curl connection

        return $result;
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
    public static function jsonDecode($json, $assoc = false, $depth = 512, $options = 0)
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
}