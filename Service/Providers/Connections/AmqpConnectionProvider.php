<?php

namespace Obullo\Service\Providers\Connections;

use AmqpConnection;
use RuntimeException;
use UnexpectedValueException;
use Obullo\Container\ContainerInterface;

/**
 * AMQP Connection Provider
 * 
 * @category  ConnectionProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/provider
 */
class AmqpConnectionProvider extends AbstractConnectionProvider
{
    protected $c;          // Container
    protected $config;     // AMQP configuration items
    protected $AMQPClass;  // AMQP extension client name

    /**
     * Constructor
     * 
     * Automatically check if the AMQP extension has been installed / enabled.
     * 
     * @param string $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('queue/amqp');  // Load database configuration file

        $this->setKey('amqp.connection.');

        if (! extension_loaded('AMQP')) {
            throw new RuntimeException(
                'The AMQP extension has not been installed or enabled.'
            );
        }
        $this->AMQPClass = 'AMQPConnection';
    }

    /**
     * Register all connections as shared ( It should run one time )
     * 
     * @return void
     */
    public function register()
    {
        foreach ($this->config['connections'] as $key => $val) {
            $this->c[$this->getKey($key)] = function () use ($val) {
                return $this->createConnection($val);
            };
        }
    }

    /**
     * Creates AMQP connections
     * 
     * @param array $params connection parameters
     * 
     * @return void
     */
    protected function createConnection($params)
    {
        if (empty($params['host']) || empty($params['port']) || empty($params['password'])) {
            throw new RuntimeException(
                'Check your queue configuration, "host" or "port" or "password" key seems empty.'
            );
        }
        $connection = new $this->AMQPClass;
        $connection->setHost($params['host']); 
        $connection->setPort($params['port']); 
        $connection->setLogin($params['username']);
        $connection->setPassword($params['password']); 
        $connection->setVHost($params['vhost']); 
        $connection->connect();
        return $connection;
    }

    /**
     * Retrieve shared AMQP connection instance from connection pool
     *
     * @param array $params provider parameters
     * 
     * @return object AMQP
     */
    public function getConnection($params = array())
    {
        if (! isset($params['connection'])) {
            $params['connection'] = array_keys($this->config['connections'])[0]; //  Set default connection
        }
        if (! isset($this->config['connections'][$params['connection']])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Connection key %s does not exist in your queue.php config file.',
                    $params['connection']
                )
            );
        }
        return $this->c[$this->getKey($params['connection'])];  // return to shared connection
    }

    /**
     * Create a new AMQP connection
     * 
     * If you don't want to add it config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object AMQP client
     */
    public function factory($params = array())
    {
        $cid = $this->getKey($this->getConnectionId($params));

        // Create shared connection if not exists
        if (! $this->c->exists($cid)) {
            $this->c[$cid] = function () use ($params) {
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
            if ($this->c->used($key)) {
                 $this->c[$key]->disconnect();
            }
        }
    }
}

// END AmqpConnectionProvider.php class
/* End of file AmqpConnectionProvider.php */

/* Location: .Obullo/ServiceProviders/Connections/AmqpConnectionProvider.php */