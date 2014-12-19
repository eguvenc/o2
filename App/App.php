<?php

namespace Obullo\App;

use LogicException,
    SimpleXmlElement,
    Controller;

/**
 * Application Class
 * 
 * @category  App
 * @package   App
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/app
 */
Class App
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Closure templates
     * 
     * @var object callable
     */
    protected $closure;

    /**
     * Keeps current env in memory.
     * 
     * @var array
     */
    protected static $env = null;

    /**
     * Env array
     * 
     * @var array
     */
    protected $envArray = array();

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Detects application environment using "app/config/env/environments.php" file.
     * 
     * @return string environment
     */
    public function detectEnvironment()
    {
        $this->envArray = $this->c->envArray;
        $hostname = gethostname();
        if (self::$env != null) {
            return self::$env;
        }
        if (in_array($hostname, $this->envArray['env']['production']['server']['hostname'])) {
            return self::$env = 'production';
        }
        if (in_array($hostname, $this->envArray['env']['test']['server']['hostname'])) {
            return self::$env = 'test';
        }
        if (in_array($hostname, $this->envArray['env']['local']['server']['hostname'])) {
            return self::$env = 'local';
        }
        die('We could not detect your application environment, please correct your <b>app/config/env/environments.php</b> hostname array.');
    }

    /**
     * Returns to environment config array
     * 
     * @return array
     */
    public function getEnvArray()
    {
        return $this->envArray;
    }

    /**
     * Store an object into controller
     * 
     * @param string $key   object name
     * @param object $value object value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        if (class_exists('\Controller', false) AND is_object($value)) {
            Controller::$instance->{$key} = $value;
        }
    }
}

// END App.php File
/* End of file App.php

/* Location: .Obullo/App/App.php */