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
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
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
        $envArray = include ROOT.'app'. DS .'config'. DS .'env'. DS .'environments.php';
        $hostname = gethostname();
        if (self::$env != null) {
            return self::$env;
        }
        if (in_array($hostname, $envArray['prod']['server']['hostname'])) {
            return self::$env = 'prod';
        }
        if (in_array($hostname, $envArray['test']['server']['hostname'])) {
            return self::$env = 'test';
        }
        if (in_array($hostname, $envArray['local']['server']['hostname'])) {
            return self::$env = 'local';
        }
        die('We could not detect your application environment, please correct your <b>app/config/env/environments.php</b> hostname array.');
        return 'local';
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