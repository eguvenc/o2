<?php

namespace Obullo\Layer;

use Obullo\Container\ContainerInterface;

/**
 * Flush Class
 * 
 * @category  Layer
 * @package   Flush
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/layer
 */
class Flush
{
    /**
     * Cache service
     * 
     * @var object
     */
    protected $cache;

    /**
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->cache = $c['cache'];
        
        $c['logger']->debug('Layer Flush Class Initialized');
    }

    /**
     * Removes layer from cache using layer "uri" and "parameters".
     * 
     * @param string $uri  string
     * @param array  $data array
     * 
     * @return boolean
     */
    public function uri($uri = '', $data = array())
    {
        $hashString = trim($uri, '/');
        if ( sizeof($data) > 0 ) {      // We can't use count() in sub layers sizeof gives better results.
            $hashString .= str_replace('"', '', json_encode($data)); // remove quotes to fix equality problem
        }
        $KEY = $this->generateId($hashString);
        if ($this->cache->exists($KEY)) {
            return $this->cache->delete($KEY);
        }
        return false;
    }

    /**
     * Create unsigned integer id using 
     * hash string.
     * 
     * @param string $hashString resource
     * 
     * @return string id
     */
    public function generateId($hashString)
    {
        $id = trim($hashString);
        return Layer::CACHE_KEY. (int)sprintf("%u", crc32((string)$id));
    }
}

// END Flush class

/* End of file Flush.php */
/* Location: .Obullo/Layer/Flush.php */