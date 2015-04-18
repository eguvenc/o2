<?php

namespace Obullo\Http\Request;

use Obullo\Container\Container;

/**
 * Parse Http Request Headers
 * 
 * @category  Http
 * @package   Error
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http
 */
class Headers
{
    /**
     * Request Headers
     * 
     * @var array
     */
    protected $headers = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parseHeaders();
    }

    /**
     * Get request header
     *
     * e.g. echo $this->c['request']->headers->get('Host');
     * 
     * @param string $key header key
     * 
     * @return void
     */
    public function get($key)
    {
        if (isset($this->headers[$key])) { // get selected header
            return $this->headers[$key];
        }
        return false;
    }

    /**
     * Get all request headers
     * 
     * @return array
     */
    public function all()
    {
        return $this->headers;
    }

    /**
     * Parse Request Headers
     *
     * @link http://php.net/manual/en/function.getallheaders.php
     * 
     * @return string | boolean | array
     */
    protected function parseHeaders()
    {
        if (function_exists('getallheaders')) {

            $this->headers = getallheaders();

        } else {  // If http server is not Apache ?

            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $this->headers[$name] = $value;
                }
            }
        }
        foreach ($this->headers as $name => $val) {  // Backup the lowercase format each of keys for max compability
            $name = strtolower($name);
            $this->headers[$name] = $val;
        }
    }

}

// END Headers.php File
/* End of file Headers.php

/* Location: .Obullo/Http/Request/Headers.php */