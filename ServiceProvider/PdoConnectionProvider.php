<?php

namespace Obullo\ServiceProvider;

use RuntimeException,
    UnexpectedValueException,
    Obullo\Container\Container,
    Obullo\Database\Connection;

/**
 * Pdo Connection Provider
 * 
 * @category  Pdo
 * @package   Connector
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/provider
 */
Class PdoConnectionProvider
{
    protected $c;                       // Container
    protected $config;                  // Configuration items
    protected $prefix;                  // Config prefix
    protected $pdoClass;                // Pdo extension client name
    protected static $instance = null;  // Presence of a static member variable

    /**
     * Checks connector is registered for one time
     * 
     * @return boolean
     */
    public static function isRegistered()
    {
        if (self::$instance == null) {
            return false;
        }
        return true;
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @param object $c Container
     * 
     * @return singleton instance.
     */
    public static function getInstance(Container $c)
    {
        if (null === self::$instance) {
            self::$instance = new static($c);
        }
        return self::$instance;
    }

    /**
     * Constructor
     * 
     * Automatically check if the PDO extension has been installed / enabled.
     * 
     * @param string $c container
     */
    protected function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('database');  // Load database configuration file

        if (! extension_loaded('PDO')) {
            throw new RuntimeException('The PDO extension has not been installed or enabled.');
        }
        $this->pdoClass = '\PDO';
    }

    /**
     * Register all connections as shared services ( It should be run one time )
     * 
     * @return void
     */
    public function register()
    {
        $self = $this;
        foreach ($this->config['connections'] as $key => $val) {
            $port   = empty($val['port']) ? '' : ';port='. $val['port'];
            $server = empty($val['dsn']) ? 'mysql:host=' . $val['hostname'] . $port . ';dbname=' . $val['database'] : $val['dsn'];

            $this->c['pdo.connection.'.$key] = function () use ($self, $server, $val) {  // create shared connections
                return $self->createConnection($server, $val);
            };
        }
    }

    /**
     * Creates PDO connections
     * 
     * @param string $server dsn
     * @param array  $params connection parameters
     * 
     * @return void
     */
    protected function createConnection($server, $params)
    {
        $options = (isset($params['pdo']['options'])) ? $params['pdo']['options'] : '';
var_dump($params);
        return new $this->pdoClass($server, $params['username'], $params['password'], $options);
    }

    /**
     * Retrieve shared PDO connection instance from connection pool
     *
     * @param array $params provider parameters
     * 
     * @return object PDO
     */
    public function getConnection($params = array())
    {
        if ( ! isset($params['connection'])) {
            $params['connection'] = $this->c['config']['database']['default']['connection'];  //  Set default connection
        }
        if ( ! isset($this->config['connections'][$params['connection']])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Server key %s not exists in your database.php config file.',
                    $params['connection']
                )
            );
        }
        return $this->c['pdo.connection.'.$params['connection']];  // return to shared connection
    }

    /**
     * Create a new PDO connection if you don't want to add config file and you want to create new one connection.
     * 
     * @param array $params connection parameters
     * 
     * @return object PDO client
     */
    public function factory($params = array())
    {
        var_dump($params);
        if (isset($params['pdo.dsn'])) {
            $this->prefix = 'pdo.';
            return $this->createConnection($params[$this->prefix .'dsn'], $params);
        }
        if ( ! isset($params['connection'])) {
            throw new UnexpectedValueException("PDO connection provider requires server parameter.");
        }
        return $this->createConnection($params['connection'], $params);  // Create new connection
    }

    /**
     * Close the connections
     */
    public function __destruct()
    {
        foreach ($this->config['connections'] as $key => $val) { //  Close shared connections
            $val = null;
            $this->c['pdo.connection.'. $key] = null; // close connection
        }
    }
}

// END PdoConnectionProvider.php class
/* End of file PdoConnectionProvider.php */

/* Location: .Obullo/ServiceProvider/PdoConnectionProvider.php */