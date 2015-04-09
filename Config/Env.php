<?php

namespace Obullo\Config;

use ArrayAccess;
use Obullo\Container\Container;

/**
 * Config Object Class
 * 
 * @category  Config
 * @package   Config
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/config
 */
class Env implements ArrayAccess
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Resolved items
     * 
     * @var array
     */
    protected static $resolvedItems = array();

    /**
     * Container 
     * 
     * @param Container $c object
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
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
        $_ENV[$key] = $value;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $key The unique identifier for the parameter
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($key)
    {
        return $this->get($key);
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
        return isset($_ENV[$key]);
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
        unset($_ENV[$key]);
    }

    /**
     * Return to configuration item and parse env variables
     * 
     * @param string $value string
     * 
     * @return mixed
     */
    public function get($value)
    {
        if (isset(static::$resolvedItems[$value])) {    // lazy loading
            return static::$resolvedItems[$value];
        }
        $arguments = static::parseArguments($value);
        return static::$resolvedItems[$value] = $this->env($arguments['value'], $arguments['default'], $arguments['required']);
    }

    /**
     * Gets environment variable from $_ENV global
     * 
     * @param string $key      key
     * @param string $default  default value
     * @param string $required if true people know any explicit required variables that your app will not work without
     * 
     * @return string value
     */
    protected function env($key, $default = '', $required = false)
    {
        $empty = empty($_ENV[$key]);
        if ($required AND $empty) {
            die('<b>Configuration error: </b>'.$key.' key not found or value is empty in .env.'.$this->c['app']->getEnv().'.php file array.');
        }
        if ($empty AND $default != '') {     // default value
            return $default;
        }
        if ( ! isset($_ENV[$key])) {
            die('<b>Configuration error: </b>'.$key.' key not found in .env.'.$this->c['app']->getEnv().'.php file array.');
        }
        return $_ENV[$key];
    }

    /**
     * Parse env arguments
     *
     * @param string $value env value
     * 
     * @return array
     */
    protected static function parseArguments($value)
    {   
        $arguments = array(
            'value' => $value,
            'default' => null,
            'required' => '',
        );
        if (strpos($value, '.') > 0) {
            $exp = explode('.', $value);
            $arguments['value'] = strstr($value, '.', true);
            $arguments['default'] = $exp[1];
            if (strtolower($exp[1]) == 'null' OR empty($exp[1])) {
                $arguments['default'] = null;
            }
            $arguments['required'] = (isset($exp[2])) ? true : false;
        }
        return $arguments;
    }
}

// END Env.php File
/* End of file Env.php

/* Location: .Obullo/Config/Env.php */