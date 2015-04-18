<?php

namespace Obullo\Layer;

use Controller;
use Obullo\Layer\Json;
use Obullo\Layer\Error;
use Obullo\Container\Container;

/**
 * Request Class
 * 
 * @category  Layers
 * @package   Request
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/layers
 */
class Request
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {   
        $this->c = $c;
        $this->params = $c['config']['layer'];
    }

    /**
     * Layers GET Request
     * 
     * @param string  $uri        uri string
     * @param array   $data       get data
     * @param integer $expiration cache ttl
     * 
     * @return string
     */
    public function get($uri, $data = array(), $expiration = '')
    {
        if (is_numeric($data)) { // Set expiration as second param if data not provided
            $expiration = $data;
            $data = array();
        }
        $raw = $this->raw('GET', $uri, $data, $expiration);
        return $this->isJson($uri, $raw);
    }

    /**
     * Layers POST Request
     * 
     * @param string  $uri        uri string
     * @param array   $data       post data
     * @param integer $expiration cache ttl
     * 
     * @return string
     */
    public function post($uri, $data = array(), $expiration = '')
    {
        if (is_numeric($data)) {  // Set expiration as second param if data not provided
            $expiration = $data;
            $data = array();
        }
        $raw = $this->raw('POST', $uri, $data, $expiration);
        return $this->isJson($uri, $raw);
    }

    /**
     * Check request is json
     * 
     * @param string $uri layer uri
     * @param string $raw data
     * 
     * @return boolean
     */
    protected function isJson($uri, $raw)
    {
        if (strpos(trim($uri, '/'), 'jsons/') === 0) {
            return $this->json($raw);
        }
        return $raw;
    }

    /**
     * Json Response Request
     *
     * @param string $raw json encoded string
     * 
     * @return string
     */
    public function json($raw)
    {
        $json = new Json($this->c);
        return $json->decode($raw);
    }

    /**
     * Send Request
     * 
     * @param string  $method     request method
     * @param string  $uriString  uri string
     * @param array   $data       request data
     * @param integer $expiration ttl
     * 
     * @return string
     */
    public function raw($method, $uriString, $data = array(), $expiration = '')
    {
        $layer = new Layer($this->c, $this->params);  // Layer always must create new instance other ways we can't use nested layers !!
        $layer->clear();       // Clear layer variables
        $layer->setHeaders();  // Headers must be at the top
        $layer->setUrl($uriString);
        $layer->setMethod($method, $data);
        $response = $layer->execute($expiration); // Execute the process
        $layer->restore();  // Restore controller objects

        if (strpos(trim($response), '@ErrorTemplate@') === 0) {  // Error template support
            $error = new Error($this->c);
            return $error->getError(str_replace('@ErrorTemplate@', '', $response));
        }
        return (string)$response;
    }
}

// END Request class

/* End of file Request.php */
/* Location: .Obullo/Layer/Request.php */