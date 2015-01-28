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
Class DatabaseConnectionProvider
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
            $this->c['db.connection.'.$key] = function () use ($self, $val) {  // create shared connections
                return $self->createConnection($val);
            };
        }
    }

    /**
     * Creates databse connections
     * 
     * @param array $params connection parameters
     * 
     * @return object
     */
    protected function createConnection($params)
    {
        $driver = strstr($params['dsn'], ':', true);

        if ( ! isset($this->config['handlers'][$driver])) {
            throw new RuntimeException(
                sprintf(
                    'Undefined handler " %s " in your database.php config file.', $driver
                )
            );
        }
        $Class = $this->config['handlers'][$driver];
        return new $Class($this->c, $params);
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
        return $this->c['db.connection.'.$params['connection']];  // return to shared connection
    }

    /**
     * Create a new database connection if you don't want to add config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object database
     */
    public function factory($params = array())
    {   
        $cid = 'db.connection.'. self::getConnectionId($params);

        if ( ! $this->c->exists($cid)) { // create shared connection if not exists
            $self = $this;
            $this->c[$cid] = function () use ($self, $params) { //  create shared connections
                return $self->createConnection($params);
            };
        }
        return $this->c[$cid];
    }

    /**
     * Returns to connection id
     * 
     * @param string $string serialized parameters
     * 
     * @return integer
     */
    protected static function getConnectionId($string)
    {
        return sprintf("%u", crc32(serialize($string)));
    }

    /**
     * Close the connections
     */
    public function __destruct()
    {
        return; // We already closed the connection to database in the destruction function.
    }
}

// END DatabaseConnectionProvider.php class
/* End of file DatabaseConnectionProvider.php */

/* Location: .Obullo/ServiceProvider/DatabaseConnectionProvider.php */
