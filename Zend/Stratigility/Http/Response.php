<?php

namespace Obullo\Zend\Stratigility\Http;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Response decorator
 *
 * Adds in write, end, and isComplete from RequestInterface in order
 * to provide a common interface for all PSR HTTP implementations.
 */
class Response implements
    PsrResponseInterface,
    ResponseInterface
{
    /**
     * @var bool
     */
    private $complete = false;

    /**
     * @var PsrResponseInterface
     */
    private $psrResponse;

    /**
     * @param PsrResponseInterface $response
     */
    public function __construct(PsrResponseInterface $response)
    {
        $this->psrResponse = $response;
    }

    /**
     * Return the original PSR response object
     *
     * @return PsrResponseInterface
     */
    public function getOriginalResponse()
    {
        return $this->psrResponse;
    }

    /**
     * Write data to the response body
     *
     * Proxies to the underlying stream and writes the provided data to it.
     *
     * @param string $data
     */
    public function write($data)
    {
        if ($this->complete) {
            return $this;
        }

        $this->getBody()->write($data);
        return $this;
    }

    /**
     * Mark the response as complete
     *
     * A completed response should no longer allow manipulation of either
     * headers or the content body.
     *
     * If $data is passed, that data should be written to the response body
     * prior to marking the response as complete.
     *
     * @param string $data
     */
    public function end($data = null)
    {
        if ($this->complete) {
            return $this;
        }

        if ($data) {
            $this->write($data);
        }

        $new = clone $this;
        $new->complete = true;
        return $new;
    }

    /**
     * Indicate whether or not the response is complete.
     *
     * I.e., if end() has previously been called.
     *
     * @return bool
     */
    public function isComplete()
    {
        return $this->complete;
    }

    /**
     * Proxy to PsrResponseInterface::getProtocolVersion()
     *
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->psrResponse->getProtocolVersion();
    }

    /**
     * Proxy to PsrResponseInterface::withProtocolVersion()
     *
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        $new = $this->psrResponse->withProtocolVersion($version);
        return new self($new);
    }

    /**
     * Proxy to PsrResponseInterface::getBody()
     *
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->psrResponse->getBody();
    }

    /**
     * Proxy to PsrResponseInterface::withBody()
     *
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        if ($this->complete) {
            return $this;
        }

        $new = $this->psrResponse->withBody($body);
        return new self($new);
    }

    /**
     * Proxy to PsrResponseInterface::getHeaders()
     *
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->psrResponse->getHeaders();
    }

    /**
     * Proxy to PsrResponseInterface::hasHeader()
     *
     * {@inheritdoc}
     */
    public function hasHeader($header)
    {
        return $this->psrResponse->hasHeader($header);
    }

    /**
     * Proxy to PsrResponseInterface::getHeader()
     *
     * {@inheritdoc}
     */
    public function getHeader($header)
    {
        return $this->psrResponse->getHeader($header);
    }

    /**
     * Proxy to PsrResponseInterface::getHeaderLine()
     *
     * {@inheritdoc}
     */
    public function getHeaderLine($header)
    {
        return $this->psrResponse->getHeaderLine($header);
    }

    /**
     * Proxy to PsrResponseInterface::withHeader()
     *
     * {@inheritdoc}
     */
    public function withHeader($header, $value)
    {
        if ($this->complete) {
            return $this;
        }

        $new = $this->psrResponse->withHeader($header, $value);
        return new self($new);
    }

    /**
     * Proxy to PsrResponseInterface::withAddedHeader()
     *
     * {@inheritdoc}
     */
    public function withAddedHeader($header, $value)
    {
        if ($this->complete) {
            return $this;
        }

        $new = $this->psrResponse->withAddedHeader($header, $value);
        return new self($new);
    }

    /**
     * Proxy to PsrResponseInterface::withoutHeader()
     *
     * {@inheritdoc}
     */
    public function withoutHeader($header)
    {
        if ($this->complete) {
            return $this;
        }

        $new = $this->psrResponse->withoutHeader($header);
        return new self($new);
    }

    /**
     * Proxy to PsrResponseInterface::getStatusCode()
     *
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->psrResponse->getStatusCode();
    }

    /**
     * Proxy to PsrResponseInterface::withStatus()
     *
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = null)
    {
        if ($this->complete) {
            return $this;
        }

        $new = $this->psrResponse->withStatus($code, $reasonPhrase);
        return new self($new);
    }

    /**
     * Proxy to PsrResponseInterface::getReasonPhrase()
     *
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return $this->psrResponse->getReasonPhrase();
    }

    //----------- OBULLO METHODS ----------//

    /**
     * Create a JSON response with the given data.
     *
     * Default JSON encoding is performed with the following options, which
     * produces RFC4627-compliant JSON, capable of embedding into HTML.
     * 
     * - JSON_HEX_TAG
     * - JSON_HEX_APOS
     * - JSON_HEX_AMP
     * - JSON_HEX_QUOT
     * 
     * @param array   $data            json data
     * @param integer $status          http status code
     * @param array   $headers         json headers
     * @param integer $encodingOptions json ecoding options
     * 
     * @return object
     */
    public function json(array $data, $status = 200, array $headers = [], $encodingOptions = 15)
    {
        return $this->psrResponse->json($data, $status, $headers, $encodingOptions);
    }

    /**
     * Create an HTML response.
     *
     * Produces an HTML response with a Content-Type of text/html and a default
     * status of 200.
     * 
     * @param string  $html    content
     * @param integer $status  status
     * @param array   $headers headers
     * 
     * @return void
     */
    public function html($html, $status = 200, array $headers = [])
    {
        return $this->psrResponse->html($html, $status, $headers);
    }

    /**
     * Redirect response
     * 
     * @param string  $uri     uri
     * @param integer $status  status
     * @param array   $headers headers
     * 
     * @return void
     */
    public function redirect($uri, $status = 301, array $headers = [])
    {
        return $this->psrResponse->redirect($uri, $status, $headers);
    }

    /**
     * Empty response
     * 
     * @param integer $status  status
     * @param array   $headers headers
     * 
     * @return object
     */
    public function emptyContent($status = 204, array $headers = [])
    {
        return $this->psrResponse->emptyContent($status, $headers);
    }


}
