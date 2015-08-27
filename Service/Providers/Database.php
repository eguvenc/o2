<?php

namespace Obullo\Service\Providers;

use RuntimeException;
use UnexpectedValueException;
use Obullo\Database\SQLLogger;
use Obullo\Container\ContainerInterface;
use Obullo\Service\ServiceProviderInterface;

/**
 * Database Connection Provider
 * 
 * @category  Connections
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class Database extends AbstractProvider implements ServiceProviderInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Database config array
     * 
     * @var array
     */
    protected $config;

    /**
     * Database adapter class
     * 
     * @var string
     */
    protected $adapterClass;

    /**
     * Constructor
     * 
     * Automatically check if the PDO extension has been installed / enabled.
     * 
     * @param string $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('database');  // Load database configuration file
        $this->adapterClass = '\Obullo\Database\Pdo\Adapter';

        $this->setKey('database.connection.');
        $this->register();
    }

    /**
     * Register all connections as shared services ( run once )
     * 
     * @return void
     */
    public function register()
    {
        foreach (array_keys($this->config['connections']) as $key) {
            $this->c[$this->getKey($key)] = function () use ($key) {
                return $this->createConnection($this->config['connections'][$key]);
            };
        }
    }

    /**
     * Creates databse connections
     * 
     * @param array $params database connection params
     * 
     * @return object
     */
    protected function createConnection($params)
    {
        $params['dsn'] = str_replace('pdo_', '', $params['dsn']);
        $Class = '\\Obullo\Database\Pdo\Drivers\\'.ucfirst(strstr($params['dsn'], ':', true));

        if ($this->c['config']['logger']['app']['query']['log']) {
            $params['logger'] = new SQLLogger($this->c['logger']);
        }
        return new $Class($params);

    }

    /**
     * Retrieve shared database connection instance from connection pool
     *
     * @param array $params provider parameters
     * 
     * @return object PDO
     */
    public function get($params = array())
    {
        if (! isset($params['connection'])) {
            $params['connection'] = array_keys($this->config['connections'])[0];  //  Set default connection
        }
        if (! isset($this->config['connections'][$params['connection']])) {
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
     * Create a new database connection if you don't want to add config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object database
     */
    public function factory($params = array())
    {   
        $cid = $this->getKey($this->getConnectionId($params));

        if (! $this->c->has($cid)) { // create shared connection if not exists
            $this->c[$cid] = function () use ($params) {
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
        foreach (array_keys($this->config['connections']) as $key) {        // Close the connections
            $key = $this->getKey($key);
            if ($this->c->active($key)) {  // Connection is active ? 
                 unset($this->c[$key]);
            }
        }
    }

}