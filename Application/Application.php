<?php

namespace Obullo\Application;

use BadMethodCallException;
use Obullo\Annotations\Filter;
use Obullo\Container\Container;

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
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Environments.php array data
     * 
     * @var array
     */
    protected $envArray = array();

    /**
     * Global filters, they works on every http request
     * 
     * @var array
     */
    protected $filters = array();

    /**
     * Current environent
     * 
     * @var string
     */
    protected static $env = null;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->c['annotation.filter'] = function () use ($c) {
            return new Filter($c);
        };
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
            if (in_array($hostname, $this->envArray[$current])) {
                self::$env = $current;
                break;
            }
        }
        if (self::$env == null) {
            die('We could not detect your application environment, please correct your <b>app/environments.php</b> hostname array.');
        }
    }

    /**
     * Register global application filter
     * 
     * @param string $namespace filter class
     * @param string $method    before, after, finish
     * 
     * @return void
     */
    public function filter($namespace, $method = 'before')
    {
        $this->filters[$method][] = $namespace;
    }

    /**
     * Run filters
     * 
     * @param string $method directions ( before, after, finish )
     * @param array  $params user parameters
     * 
     * @return void
     */
    public function initFilters($method = 'before', $params = array())
    {
        if ( ! isset($this->filters[$method])) {  // Return if filter not exists
            return;
        }
        foreach ($this->filters[$method] as $key => $Class) {

            $Class = '\\'.ucfirst($this->filters[$method][$key]);
            $class = new $Class($this->c, $params);

            if ( ! method_exists($class, $method)) {   // Throw exception if filter method not exists.
                throw new BadMethodCallException(
                    sprintf(
                        'Filter class %s requires %s method but not found.',
                        ltrim($Class, '\\'),
                        $method
                    )
                );
            }
            $class->$method();
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
        return array_keys($this->envArray);
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