<?php

namespace Obullo\ServiceProviders\Connections;

use RuntimeException;
use UnexpectedValueException;
use Obullo\Container\Container;

/**
 * Database Connection Provider
 * 
 * @category  ConnectionProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/provider
 */
Class DatabaseConnectionProvider extends AbstractConnectionProvider
{
    protected $c;         // Container
    protected $config;    // Configuration items

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

        $this->setKey('db.connection.');
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
     * Creates databse connections
     * 
     * @param array $params connection parameters
     * 
     * @return object
     */
    protected function createConnection($params)
    {
        $driver = ucfirst(strstr($params['dsn'], ':', true));
        $Class = '\\Obullo\Database\Pdo\Handler\\'.$driver;
        return new $Class($this->c, $params);
    }
    
    /**
     * Retrieve shared database connection instance from connection pool
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
                    'Connection key %s not exists in your database.php config file.',
                    $params['connection']
                )
            );
        }
        return $this->c[$this->getKey($params['connection'])];  // return to shared connection
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
        $cid = $this->getKey($this->getConnectionId($params));

        if ( ! $this->c->exists($cid)) { // create shared connection if not exists
            $this->c[$cid] = function () use ($params) { //  create shared connections
                return $this->createConnection($params);
            };
        }
        return $this->c[$cid];
    }

    /**
     * Close the connections
     */
    public function __destruct()
    {
        return; // We already close the connections in pdo provider.
    }

}

// END DatabaseConnectionProvider.php class
/* End of file DatabaseConnectionProvider.php */

/* Location: .Obullo/ServiceProviders/DatabaseConnectionProvider.php */