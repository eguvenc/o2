<?php

namespace Obullo\Cli;

use Obullo\Log\Logger,
    InvalidArgumentException;

/**
 * Parser Class
 * 
 * @category  Cli
 * @package   Parser
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/Cli
 */
Class Parser
{
    /**
     * Argument seperator
     */
    const SEPARATOR = '=';

    /**
     * Argument sign
     */
    const SIGN = '--';

    /**
     * All Cli arguments not values
     * 
     * @var array
     */
    public $segments;

    /**
     * All Cli arguments with key and values ( associative array )
     * 
     * @var array
     */
    public $arguments;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->logger = $c['logger'];
        if ($this->logger instanceof Logger) {  // We need to sure logger object is available
            $this->logger->debug('Cli Parser Class Initialized');
        }
    }

    /**
     * Resolve command line parameters
     * 
     * @param array $parameters parameter array
     * 
     * @return array resolved parameters
     */
    public function parse($parameters = array())
    {
        if ( ! is_array($parameters) OR ! isset($parameters[0])) {
            return array();
        }
        $this->arguments['command'] = $parameters[0];
        $params = array();
        foreach ($parameters as $value) {
            if (strpos($value, static::SIGN) === 0) {
                $val = explode(static::SEPARATOR, $value);
                $paramKey = trim($val[0], static::SIGN);
                $params[] = $paramKey;
                $this->arguments[$paramKey] = isset($val[1]) ? $val[1] : $paramKey;
            } else {
                $params[] = trim($value, static::SIGN);
            }
        }
        if ($this->logger instanceof Logger) {
            $this->logger->debug('Cli parameters resolved', array('parameters' => $params));
        }
        $this->segments = $params;
        return $params;
    }

    /**
     * Get one segment
     * 
     * @param mixed $segment integer number or string segment
     * @param mixed $default default value of segment
     * 
     * @return mixed valid segment or null
     */
    public function segment($segment, $default = null)
    {
        if (is_numeric($segment) AND isset($this->segments[$segment])) {
            return $this->segments[$segment];
        }
        return $default;
    }

    /**
     * Get all segments
     * 
     * @return array all segments
     */
    public function segmentArray()
    {
        return $this->segments;
    }

    /**
     * Get one argument
     * 
     * @param mixed $key     key of argument
     * @param mixed $default default value of argument
     * 
     * @return mixed valid argument or null
     */
    public function argument($key, $default = null)
    {
        if (is_string($key) AND isset($this->arguments[$key])) {
            return $this->arguments[$key];
        }
        return $default;
    }

    /**
     * Get all arguments
     * 
     * @return array all arguments
     */
    public function argumentArray() 
    {
        return $this->arguments;
    }

}

// END Parser.php File
/* End of file Parser.php

/* Location: .Obullo/Cli/Parser.php */