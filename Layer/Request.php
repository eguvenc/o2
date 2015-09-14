<?php

namespace Obullo\Layer;

use Obullo\Log\LoggerInterface;
use Obullo\Config\ConfigInterface;
use Obullo\Container\ContainerInterface;

/**
 * Request Class
 * 
 * @category  Layers
 * @package   Request
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
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
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Config parameters
     * 
     * @var array
     */
    protected $params;

    /**
     * Constructor
     *
     * @param object $c      ContainerInterface
     * @param object $logger LoggerInterface
     * @param object $config ConfigInterface
     */
    public function __construct(ContainerInterface $c, LoggerInterface $logger, ConfigInterface $config)
    {   
        $this->c = $c;
        $this->logger = $logger;
        $this->params = $config['layer'];
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
    public function get($uri = '/', $data = array(), $expiration = '')
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
    public function post($uri = '/', $data = array(), $expiration = '')
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
    public function request($method, $uriString = '/', $data = array(), $expiration = '')
    {
        $layer = new Layer($this->c, $this->logger, $this->params);  // Layer always must create new instance other ways we can't use nested layers !!
        $layer->clear();       // Clear layer variables
        $layer->setHeaders();  // Headers must be at the top
        $layer->setUrl($uriString);
        $layer->setMethod($method, $data);
        $response = $layer->execute($expiration); // Execute the process
        $layer->restore();  // Restore controller objects

        if (strpos(trim($response), '@Layer404@') === 0) {  // Error template support
            return Error::getError($response);
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
        $flush = new Flush($this->logger, $this->c['cache']);
        return $flush->uri($uri, $data);
    }
    
}