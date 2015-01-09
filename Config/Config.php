<?php

namespace Obullo\Config;

use ArrayAccess,
    LogicException,
    RuntimeException,
    Obullo\Config\Writer\PhpArray;

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
     * Config folder full path with current environment
     * 
     * @var string
     */
    protected $path;

    /**
     * A cache of whether file is loaded.
     * 
     * @var array
     */
    protected $loaded = array();

    /**
     * Constructor
     *
     * Sets the $config data from the primary config.php file as a class variable
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->path  = APP .'config'. DS . ENV . DS;
        $this->local = APP .'config'. DS . 'local'. DS;
        $this->file  = $this->path .'config.env';

        ini_set('display_errors', 1);
        $this->env = include $this->file;

        $this->assignEnvironments();

        $this->array = include $this->local .'config.php';  // Load current environment config variables 

        if (ENV != 'local') {
            $config = include $this->path .'config.php';
            $this->array = array_replace_recursive($this->array, $config);  // Merge config variables if env not local.
        }
        ini_set('display_errors', 0);
    }

    /**
     * Assign environment variables
     * 
     * @return void
     */
    protected function assignEnvironments()
    {
        $dotenv = '.env.'. ENV .'.php';
        $filename = (substr($dotenv, -4) == '.php') ? $dotenv : $dotenv . '.php';
        if ( ! $envVariables = include ROOT .'.'.ltrim($filename, '.')) {
            configurationError();
        }
        $_ENV = $envVariables;
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
        global $c; //  Available container variable in config files.

        $fileUrl = str_replace('/', DS, $filename);
        $envFile = $this->path . $fileUrl .'.php';
        $file = $this->local . $fileUrl .'.php';  // Default config path

        if (file_exists(APP . 'config' . DS . $fileUrl .'.php')) {  // If shared file exists 
            $file = APP . 'config' . DS . $fileUrl .'.php';
        }
        if (file_exists($envFile)) {
            $file = $envFile;
        }
        if (in_array($file, $this->loaded, true)) {
            return $this->array[$filename];
        }
        ini_set('display_errors', 1);
        $config = include $file;
        ini_set('display_errors', 0);

        $this->array[$filename] = $config;
        $this->loaded[] = $file;
        unset($config);
        return $this->array[$filename];
    }

    /**
     * Save to config.env file
     * 
     * @return void
     */
    public function write()
    {
        $writer = new PhpArray;
        $writer->addDoc("\n/* End of file config.env */\n/* Location: .app/config/".ENV."/config.env */");
        $writer->toFile($this->file, $this->env);
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