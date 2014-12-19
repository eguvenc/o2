<?php

namespace Obullo\Http;

use ArrayAccess;

/**
 * Fetches data from $_POST variable.
 * 
 * @category  Http
 * @package   Post
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/post
 */
Class Post implements ArrayAccess
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
        $this->logger->debug('Post Class Initialized');
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
        $_POST[$key] = $value;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($key = null)
    {
        if ($key === true) {
            return $this->c->load('http/sanitizer')->sanitize($_POST);
        }
        if ($key === false) {
            return $_POST;
        }
        if ( ! isset($_POST[$key])) {
            return false;
        }
        return $this->c->load('http/sanitizer')->sanitize($_POST[$key]);
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
        return isset($_POST[$key]);
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
        unset($_POST[$key]);
    }

}

// END Post.php File
/* End of file Post.php

/* Location: .Obullo/Http/Post.php */