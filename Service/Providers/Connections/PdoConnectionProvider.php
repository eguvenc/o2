<?php

namespace Obullo\Service\Providers\Connections;

use Pdo;
use RuntimeException;
use UnexpectedValueException;
use Obullo\Container\Container;
use Obullo\Database\Connection;

/**
 * Pdo Connection Provider
 * 
 * @category  Connections
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class PdoConnectionProvider extends AbstractConnectionProvider
{
    protected $c;         // Container
    protected $config;    // Database configuration items
    protected $pdoClass;  // Pdo extension client name

    /**
     * Constructor
     * 
     * Automatically check if the PDO extension has been installed / enabled.
     * 
     * @param string $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('database');  // Load database configuration file

        $this->setKey('pdo.connection.');  // Set container key

        if ( ! extension_loaded('PDO')) {
            throw new RuntimeException(
                'The PDO extension has not been installed or enabled.'
            );
        }
        $this->pdoClass = 'PDO';
    }

    /**
     * Register all connections as shared services ( It should be run one time )
     * 
     * @return void
     */
    public function register()
    {
        foreach ($this->config['connections'] as $key => $val) {
            $this->c[$this->getKey($key)] = function () use ($val) {  // create shared connections
                return $this->createConnection($val);
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
            $params['connection'] = array_keys($this->config['connections'])[0];  //  Set default connection
        }
        if ( ! isset($this->config['connections'][$params['connection']])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Connection key %s does not exist in your database.php config file.',
                    $params['connection']
                )
            );
        }
        return $this->c[$this->getKey($params['connection'])];  // return to shared connection
    }

    /**
     * Create a new PDO connection
     * 
     * if you don't want to add it to config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object PDO client
     */
    public function factory($params = array())
    {
        $cid = $this->getKey($this->getConnectionId($params));

        if ( ! $this->c->has($cid)) { //  create shared connection if not exists
            $this->c[$cid] = function () use ($params) {  //  create shared connections
                return $this->createConnection($params);
            };
        }
        return $this->c[$cid];
    }

    /**
     * Close all "active" connections
     */
    public function __destruct()
    {
        foreach (array_keys($this->config['connections']) as $key) {        // Close the connections
            $key = $this->getKey($key);
            if ($this->c->loaded($key)) {  // Connection is active ? 
                 unset($this->c[$key]);
            }
        }
    }
}

// END PdoConnectionProvider.php class
/* End of file PdoConnectionProvider.php */

/* Location: .Obullo/Service/Providers/Connections/PdoConnectionProvider.php */