<?php

namespace Obullo\Config;

use ArrayAccess;
use Obullo\Config\Writer\PhpArray;
use Obullo\Container\ContainerInterface;

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
class Config implements ArrayAccess
{
    /**
     * Current environment
     * 
     * @var string
     */
    protected $env;

    /**
     * Current config folder
     * 
     * @var string
     */
    protected $path;

    /**
     * Array stack
     * 
     * @var array
     */
    public $array = array();

    /**
     * Constructor
     *
     * Sets the $config data from the primary config.php file as a class variable
     * 
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->env = $c['app']->env();
        
        $this->path  = CONFIG .'env'. DS .$this->env. DS;
        $this->local = CONFIG .'env'. DS .'local'. DS;
        
        $this->assignEnvironments();
        $this->array = include $this->local .'config.php';  // Load current environment config variables 
        
        if ($this->env != 'local') {
            $envConfig   = include $this->path .'config.php';
            $this->array = array_replace_recursive($this->array, $envConfig);  // Merge config variables if env not local.
        }
        $this->array['domain'] = include $this->path .'domain.php';
    }

    /**
     * Assign environment variables
     * 
     * @return void
     */
    protected function assignEnvironments()
    {
        $dotenv = '.env.'. $this->env .'.php';
        $filename = (substr($dotenv, -4) == '.php') ? $dotenv : $dotenv . '.php';
        if (! $envVariables = include ROOT .'.'.ltrim($filename, '.')) {
            static::configurationError();
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

        if (file_exists(CONFIG .$fileUrl.'.php')) {  // If shared file exists 
            $file = CONFIG .$fileUrl.'.php';
        }
        $isEnvFile = false;
        if (file_exists($envFile)) {   // Do we able to locate environment file ?
            $isEnvFile = true;
            $file = $envFile;
        }
        $config = include $file;

        if ($this->env != 'local' && $isEnvFile) { // Merge config variables if env not local.
            $localConfig = include $this->local . $fileUrl .'.php';
            return $this->array[$filename] = array_replace_recursive($localConfig, $config);
        } else {
            $this->array[$filename] = $config;
        }
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
        $fullpath = CONFIG .'env'. DS .$this->env. DS;

        if (strpos($filename, '../') === 0) {  // If we have shared config request
            $fullpath = CONFIG;
            $filename = substr($filename, 3);
        }
        $writer = new PhpArray;
        $writer->addDoc("\n/* End of file */\n/* Location: .config/env/$this->env/$filename */");
        $writer->toFile($fullpath . str_replace('/', DS, $filename), $data);
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
        if (! isset($this->array[$key])) {
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
    protected static function configurationError($errorStr = null)
    {
        $heading = $message = '';
        $error = error_get_last();
        $heading = 'Configuration Error';
        $message = 'Config file error '.(is_null($errorStr)) ? $error['message'] : $errorStr. ' at line: '.$error['line'];
        include APP. 'templates'. DS . 'errors'. DS .'general.php';
        die;

    }

}

// END Config.php File
/* End of file Config.php

/* Location: .Obullo/Config/Config.php */