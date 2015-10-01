<?php

namespace Obullo\Http;

use Closure;
use Obullo\Http\Response\Headers;
use Obullo\Config\ConfigInterface;
use Obullo\Container\ContainerInterface;
use Obullo\Container\ContainerAwareInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Http response Class.
 * 
 * Set Http Response Code, Write Outputs, Set & Finalize Headers
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Response implements ResponseInterface, ContainerAwareInterface
{
    use MessageTrait;

    /**
     * Map of standard HTTP status code/reason phrases
     *
     * @var array
     */
    private $phrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /**
     * Http status code
     * 
     * @var string
     */
    protected $statusCode;

    /**
     * Http status code reason phrase
     * 
     * @var string
     */
    protected $reasonPhrase;

    /**
     * Constructor
     * 
     * @param mixed $body    Stream  identifier and/or actual stream resource
     * @param int   $status  Status  code for the response, if any.
     * @param array $headers Headers for the response, if any.
     * 
     * @throws InvalidArgumentException on any invalid element.
     */
    public function __construct($body = 'php://memory', $status = 200, array $headers = [])
    {
        if (! is_string($body) && ! is_resource($body) && ! $body instanceof StreamInterface) {
            throw new InvalidArgumentException(
                'Stream must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }
        if (null !== $status) {
            $this->validateStatus($status);
        }
        $this->stream     = ($body instanceof StreamInterface) ? $body : new Stream($body, 'wb+');
        $this->statusCode = $status ? (int) $status : 200;

        list($this->headerNames, $headers) = $this->filterHeaders($headers);
        $this->assertHeaders($headers);
        $this->headers = $headers;
    }

    /**
     * Constructor
     * 
     * @param mixed $body    Stream  identifier and/or actual stream resource
     * @param int   $status  Status  code for the response, if any.
     * @param array $headers Headers for the response, if any.
     * 
     * @throws InvalidArgumentException on any invalid element.
     *
     * @return object
     */
    public function newInstance($body = 'php://memory', $status = 200, array $headers = [])
    {
        return new Self($body, $status, $headers);
    }

    /**
     * Set container object
     * 
     * @param object $c container
     * 
     * @return object
     */
    public function setContainer(ContainerInterface $c = null)
    {
        $this->c = $c;
        return $this;
    }

    /**
    * Set HTTP Status Header
    * 
    * @param int    $code         the status code
    * @param string $reasonPhrase reason
    * 
    * @return object
    */    
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->validateStatus($code);

        $new = clone $this;
        $new->statusCode   = (int) $code;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    /**
     * Validate a status code.
     *
     * @param int|string $code code
     * 
     * @throws InvalidArgumentException on an invalid status code.
     */
    private function validateStatus($code)
    {
        if (! is_numeric($code)
            || is_float($code)
            || $code < 100
            || $code >= 600
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid status code "%s"; must be an integer between 100 and 599, inclusive',
                    (is_scalar($code) ? $code : gettype($code))
                )
            );
        }
    }

    /**
     * Returns to response status
     * 
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Returns http status code reason phrase
     * 
     * @return string
     */
    public function getReasonPhrase()
    {
        if (! $this->reasonPhrase
            && isset($this->phrases[$this->statusCode])
        ) {
            $this->reasonPhrase = $this->phrases[$this->statusCode];
        }
        return $this->reasonPhrase;
    }

    /**
    * 404 Page Not Found Handler
    *
    * @param string $page page name
    * 
    * @return void|string
    */
    public function show404($page = '')
    {
        return $this->c['error']->show404($page);
    }

    /**
    * Manually Set General Http Errors
    *
    * @param string $message message
    * @param int    $heading heading text
    *
    * @return void|string
    */
    public function showError($message, $heading = 'An Error Was Encountered')
    {
        return $this->c['error']->showError($message, ($this->getStatusCode() == 200) ? 500 : $this->getStatusCode(), $heading);
    }

    /**
     * Encode json data and set json headers.
     * 
     * @param array  $data    array data
     * @param string $headers optional headers
     * 
     * @return string json encoded data
     */
    public function json(array $data, $headers = array())
    {
        $this->c['output']->disable(); // Disable output, we need to send json headers.

        $jsonHeaders = [
                'Cache-Control' => 'no-cache, must-revalidate',
                'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
                'Content-type' => 'application/json;charset=UTF-8'
        ];
        if (! empty($headers)) {
            $jsonHeaders = $headers;
        }
        foreach ($jsonHeaders as $key => $value) {
            $response = $this->withAddedHeader($key, $value);
        }
        list($statusCode, $headers) = $this->c['output']->finalize($response);

        $this->c['output']->sendHeaders($response, $statusCode, $headers);

        return json_encode($data);
    }

    /**
     * Ensure header names and values are valid.
     *
     * @param array $headers headers
     * 
     * @throws InvalidArgumentException
     */
    private function assertHeaders(array $headers)
    {
        foreach ($headers as $name => $headerValues) {
            HeaderSecurity::assertValidName($name);
            array_walk($headerValues, __NAMESPACE__ . '\HeaderSecurity::assertValid');
        }
    }
}