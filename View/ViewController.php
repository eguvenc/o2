<?php

namespace Obullo\View;

/**
 * View Controller Class
 * 
 * @category  View
 * @package   View
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/view
 */
class ViewController
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $c;
        $this->c = $c;
    }

    /**
     * View Layer GET Request
     * 
     * @param string  $uri        uri string
     * @param array   $data       get data
     * @param integer $expiration cache ttl
     * 
     * @return string
     */
    public function get($uri, $data = array(), $expiration = '')
    {
        return $this->c['layer']->get($uri, $data, $expiration);
    }

    /**
     * Removes layer from cache using layer "uri" and "parameters".
     * 
     * @param string $uri  string
     * @param array  $data array
     * 
     * @return boolean
     */
    public function flush($uri, $data = array())
    {
        return $this->c['layer']->flush($uri, $data);
    }   
}

// END ViewController class

/* End of file ViewController.php */
/* Location: .Obullo/View/ViewController.php */