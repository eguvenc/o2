<?php

namespace Obullo\ServiceProviders;

use AMQPConnection,
    RuntimeException,
    UnexpectedValueException,
    Obullo\Container\Container,
    Obullo\Database\Connection,
    Obullo\Helpers\SingletonTrait;

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
Class AMQPConnectionProvider
{
    protected $c;          // Container
    protected $config;     // AMQP configuration items
    protected $AMQPClass;  // AMQP extension client name

    use SingletonTrait, ConnectionTrait;

    /**
     * Constructor
     * 
     * Automatically check if the AMQP extension has been installed / enabled.
     * 
     * @param string $c container
     */
    protected function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('queue');  // Load database configuration file

        if ( ! extension_loaded('AMQP')) {
            throw new RuntimeException(
                'The AMQP extension has not been installed or enabled.'
            );
        }
        $this->AMQPClass = '\AMQPConnection';
    }

    /**
     * Register all connections as shared services ( It should be run one time )
     * 
     * @return void
     */
    public function register()
    {
        $self = $this;
        foreach ($this->config['AMQP']['connections'] as $key => $val) {
            $this->c['amqp.connection.'.$key] = function () use ($self, $val) {  // create shared connections
                return $self->createConnection($val);
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
        if ( ! isset($params['connection'])) {
            $params['connection'] = $this->config['default']['connection'];  //  Set default connection
        }
        if ( ! isset($this->config['AMQP']['connections'][$params['connection']])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Connection key %s not exists in your queue.php config file.',
                    $params['connection']
                )
            );
        }
        return $this->c['amqp.connection.'.$params['connection']];  // return to shared connection
    }

    /**
     * Create a new AMQP connection if you don't want to add config file and you want to create new one connection.
     * 
     * @param array $params connection parameters
     * 
     * @return object AMQP client
     */
    public function factory($params = array())
    {
        $cid = 'amqp.connection.'. static::getConnectionId($params);

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

// END AMQPConnectionProvider.php class
/* End of file AMQPConnectionProvider.php */

/* Location: .Obullo/ServiceProviders/AMQPConnectionProvider.php */