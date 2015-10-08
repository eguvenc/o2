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
     * Final output string
     * 
     * @var string
     */
    protected $output = '';

    /**
     * Enable / Disable flush ( send output to browser )
     * 
     * @var boolean
     */
    protected $enabled = true;

    /**
     * Output length
     * 
     * @var int
     */
    protected $length;

    /**
    * Sets the output string
    *
    * @param string $output output
    * 
    * @return object response
    */    
    public function setOutput($output)
    {        
        $this->output = $output;
        $this->length = strlen($this->output);
        return $this;
    }

    /**
    * Returns the current output string
    *
    * @return string
    */    
    public function getOutput()
    {
        $this->length = strlen($this->output);
        return $this->output;
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
        if ($this->output == '') {
            $this->output = $output;
        } else {
            $this->output.= $output;
        }
        return $this->output;
    }

    /**
     * Finalize
     *
     * This prepares this response and returns an array
     * of (status, headers, body).
     *
     * @param object $response response class
     * 
     * @return array[int status, array headers, string body]
     */
    public function finalize(ResponseInterface $response)
    {
        if (in_array($response->getStatusCode(), [204, 205, 304])) {
            return $response->withoutHeader('Content-Type')->withoutHeader('Content-Length');
        }
        $size = $response->getBody()->getSize();
        if ($size !== null) {
            $response = $response->withAddedHeader('Content-Length', (string) $size);
        }
        return $response;
    }

    /**
     * Send response headers
     *
     * @param object $response response class
     * @param array  $headers  http headers
     * 
     * @return object
     */
    public function sendHeaders(ResponseInterface $response, $headers = null)
    {
        if (empty($headers)) {
            $headers = $response->getHeaders();
        }
        if (headers_sent() === false) {   // Send headers

            header(
                sprintf(
                    'HTTP/%s %s %s',
                    $response->getProtocolVersion(),
                    $response->getStatusCode(),
                    $response->getReasonPhrase()
                )
            );
            if (count($headers) > 0) {  // Are there any server headers to send ?
                foreach ($headers as $name => $values) {
                    foreach ($values as $value) {
                        header(sprintf('%s: %s', $name, $value), false);
                    }
                }            
            }
        }
        return $this;
    }

    /**
     * Send headers and echo output
     * 
     * @param object $response response class
     * 
     * @return object
     */
    public function flush(ResponseInterface $response)
    {
        if ($this->isAllowed()) {  // Send output
            $response = $this->finalize($response);
            $this->sendHeaders($response);
            echo $this->getOutput();    // Send output
        }
        return $this;
    }

    /**
     * Enables write output method
     * 
     * @return object
     */
    public function enable()
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * Disables write output method
     * 
     * @return object
     */
    public function disable()
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * Get content length
     * 
     * @return int
     */
    public function getLength()
    {
        return $this->length;
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