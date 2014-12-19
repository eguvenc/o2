<?php

namespace Obullo\Http;

use ArrayAccess;

/**
 * Fetches data from superglobal $_GET
 * 
 * @category  Http
 * @package   Get
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/get
 */
Class Get implements ArrayAccess
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Logger instance 
     * 
     * @var object
     */
    public $logger;

    /**
     * Constructor
     *
     * @param array $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->logger = $c->load('service/logger');
        $this->logger->debug('Get Class Initialized');
    }
    /**
     * Sets a parameter or an object.
     *
     * @param string $key   The unique identifier for the parameter
     * @param mixed  $value The value of the parameter
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {        
        $_GET[$key] = $value;
    }

    /**
     * Gets data from $_GET variable.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($key = null)
    {
        if ($key === true) {
            return $this->c->load('http/sanitizer')->sanitize($_GET);
        }
        if ($key === false) {
            return $_GET;
        }
        if ( ! isset($_GET[$key])) {
            return false;
        }
        return $this->c->load('http/sanitizer')->sanitize($_GET[$key]);
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return Boolean
     */
    public function offsetExists($key)
    {
        return isset($_GET[$key]);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($_GET[$key]);
    }

}

// END Get.php File
/* End of file Get.php

/* Location: .Obullo/Http/Get.php */