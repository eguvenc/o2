<?php

namespace Obullo\Application;

/**
 * Application Class
 * 
 * @category  Application
 * @package   Environment
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/application
 */
Class Application
{
    /**
     * Current environent
     * 
     * @var string
     */
    protected static $env;

    /**
     * Environments.php array data
     * 
     * @var array
     */
    protected $envArray = array();

    /**
     * Constructor
     * 
     * Fetch environments array
     */
    public function __construct()
    {
        $this->envArray = include ROOT .'app'. DS .'environments.php';
    }

    /**
     * Detects application environment using "app/environments.php" file.
     * 
     * @return void or die if fail
     */
    public function detectEnvironment()
    {
        $hostname = gethostname();
        if (self::$env != null) {
            return;
        }
        foreach ($this->getEnvironments() as $current) {
            if (in_array($hostname, $this->envArray['env'][$current]['server']['hostname'])) {
                self::$env = $current;
                break;
                echo $current;
            }
        }
        if (self::$env == null) {
            die('We could not detect your application environment, please correct your <b>app/environments.php</b> hostname array.');
        }
    }

    /**
     * Returns to detected environment
     * 
     * @return string
     */
    public function getEnv()
    {
        return self::$env;
    }

    /**
     * Returns to all environment names
     * 
     * @return array
     */
    public function getEnvironments()
    {
        return array_keys($this->envArray['env']);
    }

    /**
     * Returns to all environments data
     * 
     * @return array
     */
    public function getEnvArray()
    {
        return $this->envArray;
    }

    /**
     * Returns to valid environment path
     * 
     * @return string
     */
    public function getEnvPath()
    {
        return ENV_PATH;
    }

}

// END Application.php File
/* End of file Application.php

/* Location: .Obullo/Application/Application.php */