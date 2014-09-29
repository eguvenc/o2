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

    /**
     * Define closure function
     * 
     * @param string $name    function name
     * @param string $closure data
     * 
     * @return void
     */
    public function func($name, $closure)
    {
        $this->closure[$name] = $closure;
    }

    /**
     * Down application ( enter the maintenance mode ).
     *
     * @param string $func   name ( service or app )
     * @param string $domain domain key name comes from config.xml
     * 
     * @return mixed
     */
    public function down($func = 'app.down', $domain = 'all') 
    {
        $section = substr($func, 0, 3);
        if ($domain != '*' AND ! $domain instanceof SimpleXmlElement) {
            throw new LogicException('Correct your routes.php domain option it must be like this $c[\'config\']->xml->app->$name.');
        }
        if ($domain == '*') {
            $domainKey = 'all'; // Default application name
        } else {
            $domainKey = $domain->getName();  // Get xml application name
        }
        if (isset($this->c['config']->xml->{$section}->{$domainKey}->domain->regex) 
            AND $this->c['config']->xml->{$section}->{$domainKey}->maintenance == 'down'
        ) {
            $closure = $this->run($func, array('domain' => $domain));
            if (is_callable($closure)) {
                return $closure($domain);
            }
        }
        return true;
    }

    /**
     * Run closures before we store
     *
     * @param string $name fuction name
     * 
     * @return mixed closure result
     */
    protected function run($name)
    {
        if (defined('STDIN')) {  // Don't run this command for Cli.
            return null;
        }
        if ( ! isset($this->closure[$name])) {
            throw new LogicException(
                sprintf('Callback method "%s" is not defined in app closure data.', $name)
            );
        }
        return $this->closure[$name];
    }
}

// END App.php File
/* End of file App.php

/* Location: .Obullo/App/App.php */