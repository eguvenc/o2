<?php

namespace Obullo\Config;

use ArrayAccess,
    DOMDocument,
    LogicException,
    RuntimeException;

/**
 * Config Class
 * 
 * @category  Config
 * @package   Config
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/config
 */
Class Config implements ArrayAccess
{
    /**
     * Env file array
     * 
     * @var array
     */
    public $env;

    /**
     * Configuration container
     * 
     * @var array
     */
    public $array = array();

    /**
     * A cache of whether file is loaded.
     * 
     * @var array
     */
    protected $loaded = array();

    /**
     * Config folder full path with current environment
     * 
     * @var string
     */
    protected $envPath;

    /**
     * Constructor
     *
     * Sets the $config data from the primary config.php file as a class variable
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->envPath = APP .'config'. DS . 'env'. DS . ENV . DS;
        $this->file = $this->envPath .'config.env';

        ini_set('display_errors', 1);
        $this->env = include $this->file;

        if (isset($this->env['environment']['file'])) {   // Load environment variables if exists
            $this->loadEnv($this->env['environment']['file']);
        }
        $this->array = include $this->envPath .'config.php';  // Bind current environment config variables 
        ini_set('display_errors', 0);
    }

    /**
     * Load Config File
     *
     * @param string $filename the config file name
     * 
     * @return array if the file was loaded correctly
     */
    public function load($filename = '')
    {
        global $c;
        $file = APP . 'config' . DS .'shared'. DS . str_replace('/', DS, $filename) . '.php';
        $envFile = $this->envPath . str_replace('/', DS, $filename) . '.php';

        if (file_exists($envFile)) {
            $file = $envFile;
        }
        if (in_array($file, $this->loaded, true)) {
            return $this->array[$filename];
        }
        ini_set('display_errors', 1);
        $config = include $file;
        ini_set('display_errors', 0);

        if (strpos($filename, 'constants/') === false  // Allow loading constant files
            AND ($config == false OR ! isset($config) OR ! is_array($config))
        ) {
            throw new LogicException(
                sprintf(
                    'Your %s file does not appear to contain a valid configuration array.', 
                    $file
                )
            );
        }
        $this->array[$filename] = $config;
        $this->loaded[] = $file;
        unset($config);
        return $this->array[$filename];
    }


    /**
     * Load env config variables file
     * 
     * @param string $file filename
     * 
     * @return void
     */
    public function loadEnv($file)
    {
        $filename = (substr($file, -4) == '.php') ? $file : $file . '.php';
        if ( ! $envVariables = include ROOT .'.'.$filename) {
            configurationError();
        }
        foreach ($envVariables as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");   
        }
    }

    /**
     * Save xml file
     *
     * @param string $xmlOutput xml file string
     * 
     * @return void
     */
    public function save($xmlOutput = null)
    {
        // $xml = empty($xmlOutput) ? $this->xml->asXML() : $xmlOutput;

        // if ( ! is_writable($this->file)) {
        //     throw new LogicException(
        //         sprintf(
        //             'Your application config/env/%s/config.xml file is not writable.', 
        //             ENV
        //         )
        //     );
        // }
        // $dom = new DOMDocument('1.0');
        // $dom->preserveWhiteSpace = true;
        // $dom->formatOutput = false;
        // $dom->loadXML($xml);
        // if ( ! $dom) {
        //     throw new RuntimeException('Error while parsing the config.xml document.');
        // }
        // $dom = simplexml_import_dom($dom);
        // $dom->saveXML($this->file);
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
        $this->array[$key] = $value;
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
        if ( ! isset($this->array[$key])) {
            return false;
        }
        return $this->array[$key];
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
        return isset($this->array[$key]);
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
        unset($this->array[$key]);
    }

}

// END Config.php File
/* End of file Config.php

/* Location: .Obullo/Config/Config.php */