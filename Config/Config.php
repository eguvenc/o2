<?php

namespace Obullo\Config;

use ArrayAccess,
    LogicException,
    RuntimeException,
    Obullo\Container\Container,
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
     * Array stack
     * 
     * @var array
     */
    public $array = array();    // Configuration files stack

    /**
     * Current config folder
     * 
     * @var string
     */
    protected $path;            // Config folder full path with current environment

    /**
     * Constructor
     *
     * Sets the $config data from the primary config.php file as a class variable
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->path  = APP .'config'. DS . 'env'. DS . ENV . DS;
        $this->local = APP .'config'. DS . 'env'. DS .'local'. DS;
        
        ini_set('display_errors', 1);
        $this->assignEnvironments();
        $this->array = include $this->local .'config.php';  // Load current environment config variables 
        
        if (ENV != 'local') {
            $envConfig = include $this->path .'config.php';
            $this->array = array_replace_recursive($this->array, $envConfig);  // Merge config variables if env not local.
        }
        $this->array['domain'] = include $this->path .'domain.php';
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
            $this->configurationError();
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
        global $c; //  Make available $c variable in config files.

        if (isset($this->array[$filename])) {   // Is file loaded before ?
            return $this->array[$filename];
        }
        if ($filename == 'config') {  //  Config already loaded but someone may want to load it again.
            return $this->array;
        }
        $fileUrl = str_replace('/', DS, $filename);
        $envFile = $this->path . $fileUrl.'.php';
        $file = $this->local . $fileUrl.'.php';  // Default config path

        if (file_exists(APP .'config'. DS .$fileUrl.'.php')) {  // If shared file exists 
            $file = APP .'config'. DS .$fileUrl.'.php';
        }
        $isEnvFile = false;
        if (file_exists($envFile)) {   // Do we able to locate environment file ?
            $isEnvFile = true;
            $file = $envFile;
        }
        ini_set('display_errors', 1);
        $config = include $file;

        if (ENV != 'local' AND $isEnvFile) { // Merge config variables if env not local.
            $localConfig = include $this->local . $fileUrl .'.php';
            return $this->array[$filename] = array_replace_recursive($localConfig, $config);
        } else {
            $this->array[$filename] = $config;
        }
        ini_set('display_errors', 0);
        return $this->array[$filename];
    }

    /**
     * Save array data config file
     *
     * @param string $filename full path of the file
     * @param array  $data     config data
     * 
     * @return void
     */
    public function write($filename, $data)
    {
        $writer = new PhpArray;
        $writer->addDoc("\n/* End of file */\n/* Location: .$filename */");
        $writer->toFile($filename, $data);
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

    /**
     * Include file errors
     * 
     * @param string $errorStr message
     * 
     * @return void exit
     */
    protected function configurationError($errorStr = null)
    {
        $error = error_get_last();
        $message = (is_null($errorStr)) ? $error['message'] : $errorStr;
        die('<b>Configuration error:</b> '.$message. ' line: '.$error['line']);
    }

}

// END Config.php File
/* End of file Config.php

/* Location: .Obullo/Config/Config.php */