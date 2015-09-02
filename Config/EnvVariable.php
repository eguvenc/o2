<?php

namespace Obullo\Config;

use ArrayAccess;
use Obullo\Container\ContainerInterface;

/**
 * Config Env Variable Object
 * 
 * @category  Config
 * @package   Config
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/config
 */
class EnvVariable implements ArrayAccess
{
    /**
     * Current Environment
     * 
     * @var string
     */
    protected $env;

    /**
     * Resolved items
     * 
     * @var array
     */
    protected static $resolvedItems = array();

    /**
     * Container 
     * 
     * @param object $c \Obullo\Container\ContainerInterface
     */
    public function __construct(ContainerInterface $c)
    {
        $this->env = $c['app']->env();
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
        $heading = $message = '';
        
        if ($required && $empty) {
            $heading = 'Undefined Env Variable';
            $message = 'Env configuration key <b>'.$key.'</b> is not defined or empty in your <b>.env.'.$this->env.'.php</b> file.';
            include APP. 'templates'. DS . 'errors'. DS .'general.php';
            die;
        }
        if ($empty && $default != '') {     // default value
            return $default;
        }
        if (! isset($_ENV[$key])) {
            $heading = 'Undefined Env Variable';
            $message = 'Env configuration key <b>'.$key.'</b> is not defined in your <b>.env.'.$this->env.'.php</b> file.';
            include APP. 'templates'. DS . 'errors'. DS .'general.php';
            die;
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
            if (strtolower($exp[1]) == 'null' || empty($exp[1])) {
                $arguments['default'] = null;
            }
            $arguments['required'] = ($arguments['default'] == 'required' || isset($exp[2])) ? true : false;
        }
        return $arguments;
    }
}