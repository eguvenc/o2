<?php

namespace Obullo\Layer;

use Controller;
use Obullo\Container\ContainerInterface;

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
    public function __construct(ContainerInterface $c)
    {   
        $this->c = $c;
        $this->params = $c['config']['layer'];

        $this->c['layer.flush'] = function () use ($c) {
            return new Flush($c);
        };
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
        return $this->request('GET', $uri, $data, $expiration);
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
        return $this->request('POST', $uri, $data, $expiration);
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
    public function request($method, $uriString, $data = array(), $expiration = '')
    {
        $layer = new Layer($this->c, $this->params);  // Layer always must create new instance other ways we can't use nested layers !!
        $layer->clear();       // Clear layer variables
        $layer->setHeaders();  // Headers must be at the top
        $layer->setUrl($uriString);
        $layer->setMethod($method, $data);
        $response = $layer->execute($expiration); // Execute the process
        $layer->restore();  // Restore controller objects

        if (strpos(trim($response), '@LayerNotFound@') === 0) {  // Error template support
            $error = new Error($this->c);
            return $error->getError(str_replace('@LayerNotFound@', '', $response));
        }
        return (string)$response;
    }

    /**
     * Call helpers ( flush class .. ) $this->c['layer']->flush('views/header');
     * 
     * @param string $uri  string
     * @param array  $data params
     * 
     * @return boolean
     */
    public function flush($uri, $data = array())
    {
        return $this->c['layer.flush']->uri($uri, $data);
    }
    
}

// END Request class

/* End of file Request.php */
/* Location: .Obullo/Layer/Request.php */