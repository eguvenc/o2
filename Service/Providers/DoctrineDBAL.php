<?php

namespace Obullo\Service\Providers;

use RuntimeException;
use UnexpectedValueException;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Obullo\Container\ContainerInterface;
use Obullo\Service\ServiceProviderInterface;

use Obullo\Database\Doctrine\DBAL\SQLLogger;

/**
 * Doctrine DBAL Connection Provider
 * 
 * @category  Connections
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class DoctrineDBAL extends AbstractProvider implements ServiceProviderInterface
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
     * Doctrin adapter class
     * 
     * @var string
     */
    protected $adapterClass;

    /**
     * Constructor
     * 
     * @param string $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('database');  // Load database configuration file
        $this->adapterClass = '\Obullo\Doctrine\DBAL\Adapter';

        $this->setKey('doctrine.connection.');
        $this->register();
    }

    /**
     * Register all connections as shared services ( It should be run one time )
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
        $dsnString = 'driver='.strstr($params['dsn'], ':', true).';'.ltrim(strstr($params['dsn'], ':'), ':');
        parse_str(str_replace(';', '&', $dsnString), $formattedParams);
        $params = array_merge($formattedParams, $params);

        $config = isset($params['config']) ? $params['config'] : new Configuration;
        $eventManager = isset($params['eventManager']) ? $params['eventManager'] : null;

        if ($this->c['config']['logger']['app']['query']['log']) {
            $config->setSQLLogger(new SQLLogger($this->c['logger']));
        }
        $params['wrapperClass'] = '\Obullo\Database\Doctrine\DBAL\Adapter';

        return DriverManager::getConnection($params, $config, $eventManager);
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