<?php

namespace Obullo\ServiceProvider;

use RuntimeException,
    UnexpectedValueException,
    Obullo\Container\Container,
    Obullo\Database\Connection,
    Obullo\Utils\SingletonTrait;

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
    protected $c;         // Container
    protected $config;    // Database configuration items
    protected $pdoClass;  // Pdo extension client name

    use SingletonTrait, ConnectionTrait;

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

        if ( ! extension_loaded('PDO')) {
            throw new RuntimeException(
                'The PDO extension has not been installed or enabled.'
            );
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
            $this->c['pdo.connection.'.$key] = function () use ($self, $val) {  // create shared connections
                return $self->createConnection($val);
            };
        }
    }

    /**
     * Creates PDO connections
     * 
     * @param array $params connection parameters
     * 
     * @return void
     */
    protected function createConnection($params)
    {
        if ( ! isset($params['dsn']) OR empty($params['dsn'])) {
            throw new RuntimeException(
                'In your database configuration "dsn" connection key empty or not found.'
            );
        }
        return new $this->pdoClass($params['dsn'], $params['username'], $params['password'], $params['options']);
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
        $cid = 'pdo.connection.'. static::getConnectionId($params);

        if ( ! $this->c->exists($cid)) { //  create shared connection if not exists
            $self = $this;
            $this->c[$cid] = function () use ($self, $params) {  //  create shared connections
                return $self->createConnection($params);
            };
        }
        return $this->c[$cid];
    }

    /**
     * Close the connections
     */
    public function __destruct()
    {
        return; // We already closed the connection to database in the destruction function.
    }
}

// END PdoConnectionProvider.php class
/* End of file PdoConnectionProvider.php */

/* Location: .Obullo/ServiceProvider/PdoConnectionProvider.php */