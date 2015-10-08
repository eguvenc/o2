<?php

namespace Obullo\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Output Control ( Manage application output )
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Output
{
    /**
     * Http Response
     * 
     * @var object
     */
    protected $response;

    /**
     * Enable / disable sending output
     * 
     * @var boolean
     */
    protected $enabled = true;    

    /**
     * Set response object
     * 
     * @param ResponseInterface $response \Obullo\Http\Response
     *
     * @return object
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Append to output string
     * 
     * @param string $output output
     * 
     * @return string output
     */
    public function write($output = '')
    {
        $this->response->getBody()->write($output);
        return $this->response->getBody();
    }

    /**
     * Finalize
     *
     * This prepares this response and returns an array
     * of (status, headers, body).
     * 
     * @return object Response
     */
    // public function finalize()
    // {
    //     if (in_array($this->response->getStatusCode(), [204, 205, 304])) {
    //         return $this->response
    //             ->withoutHeader('Content-Type')
    //             ->withoutHeader('Content-Length');
    //     }
    //     $size = $this->response->getBody()->getSize();
    //     if ($size !== null) {
    //         $this->response
    //             ->withAddedHeader('Content-Length', (string) $size);
    //     }
    //     return $this->response;
    // }

    /**
     * Send response headers
     *
     * @param object $response response class
     * @param array  $headers  http headers
     * 
     * @return object
     */
    // public function sendHeaders(ResponseInterface $response, $headers = null)
    // {
    //     if (empty($headers)) {
    //         $headers = $response->getHeaders();
    //     }
    //     if (headers_sent() === false) {   // Send headers

    //         header(
    //             sprintf(
    //                 'HTTP/%s %s %s',
    //                 $response->getProtocolVersion(),
    //                 $response->getStatusCode(),
    //                 $response->getReasonPhrase()
    //             )
    //         );
    //         if (count($headers) > 0) {  // Are there any server headers to send ?
    //             foreach ($headers as $name => $values) {
    //                 foreach ($values as $value) {
    //                     header(sprintf('%s: %s', $name, $value), false);
    //                 }
    //             }            
    //         }
    //     }
    //     return $this;
    // }

    /**
     * Send headers and echo output
     * 
     * @return object
     */
    // public function flush()
    // {
    //     if ($this->isAllowed()) {  // Send output

    //         $this->response = $this->finalize();
    //         $this->sendHeaders($this->response);

    //         echo $this->response->getBody();
    //     }
    //     return $this;
    // }

    /**
     * Encode json data and set json headers.
     * 
     * @param array  $data    array data
     * @param string $headers optional headers
     * 
     * @return string json encoded data
     */
    // public function json(array $data, $headers = array())
    // {
    //     $this->disable(); // Disable output, we need to send json headers.

    //     $jsonHeaders = [
    //             'Cache-Control' => 'no-cache, must-revalidate',
    //             'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
    //             'Content-type' => 'application/json;charset=UTF-8'
    //     ];
    //     if (! empty($headers)) {
    //         $jsonHeaders = $headers;
    //     }
    //     foreach ($jsonHeaders as $key => $value) {
    //         $this->response = $this->response->withAddedHeader($key, $value);
    //     }
    //     $this->response = $this->finalize();
    //     $this->sendHeaders($this->response);

    //     return json_encode($data);
    // }

    /**
     * Enables flush method
     * 
     * @return object
     */
    public function enable()
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * Disables flush method
     * 
     * @return object
     */
    public function disable()
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * Returns to true if output enabled otherwise false
     * 
     * @return boolean
     */
    public function isAllowed()
    {
        return $this->enabled;
    }
}