<?php

namespace Obullo\Http\Response;

/**
 * Manage Http Response Headers
 * 
 * @category  Http
 * @package   Error
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http
 */
class Headers
{
    /**
     * Response Headers
     * 
     * @var array
     */
    protected $headers = array();

    /**
     * Header replace option and any
     * possible option handler
     * 
     * @var array
     */
    protected $options = array();

    /**
     * Set response header
     * 
     * @param string  $name    header key
     * @param string  $value   header value
     * @param boolean $replace header replace option
     *
     * @return void
     */
    public function set($name, $value = null, $replace = true)
    {
        $name = strtolower($name);
        $this->options[$name] = ['replace' => $replace];
        $this->headers[$name] = $value;
    }

    /**
     * Get header
     *
     * @param string $name header key
     * 
     * @return void
     */
    public function get($name)
    {
        $name = strtolower($name);
        return $this->headers[$name];
    }

    /**
     * Remove header
     * 
     * @param string $name header key
     * 
     * @return void
     */
    public function remove($name)
    {
        $name = strtolower($name);
        unset($this->headers[$name]);
    }

    /**
     * Returns to all headers
     * 
     * @return array
     */
    public function all()
    {
        return $this->headers;
    }

    /**
     * Get header options
     * 
     * @return array
     */
    public function options()
    {
        return $this->options;
    }

}