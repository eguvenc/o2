<?php

namespace Obullo\ServiceProvider;

use RuntimeException,
    UnexpectedValueException,
    Obullo\Container\Container;

/**
 * Mongo Connection Provider
 * 
 * @category  Mongo
 * @package   Connector
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/provider
 */
Class MongoConnectionProvider
{
    protected $c;            // Container
    protected $config;       // Configuration items
    protected $mongoClass;   // Mongo extension client name
    protected $factories = array(); // New connection objects
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
     * Constructor ( Works one time )
     * 
     * Automatically check if the Mongo PECL extension has been installed / enabled.
     * 
     * @param string $c container
     */
    protected function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('mongo');  // Load nosql configuration file
        $this->mongoClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? '\Mongo' : '\MongoClient';

        if ( ! class_exists($this->mongoClass, false)) {
            throw new RuntimeException(
                sprintf(
                    'The %s extension has not been installed or enabled.', 
                    trim($this->mongoClass, '\\')
                )
            );
        }
    }

    /**
     * Register all connections as shared services ( Works one time )
     * 
     * @return void
     */
    public function register()
    {
        $self = $this;
        foreach ($this->config['connections'] as $key => $val) {
            $server = 'mongodb://'.$val['username'].':'.$val['password'].'@'.$val['hostname'].':'.$val['port'];
            $this->c['mongo.connection.'.$key] = function () use ($self, $server, $val) {  //  create shared connections
                return $self->createConnection($server, $val);
            };
        }
    }

    /**
     * Creates mongo connections
     * 
     * @param string $server dsn
     * @param array  $params connection parameters
     * 
     * @return void
     */
    protected function createConnection($server, $params)
    {
        $options = isset($params['options']) ? $params['options'] : array('connect' => true);
        return new $this->mongoClass($server, $options);
    }

    /**
     * Retrieve shared mongo connection instance from connection pool
     *
     * @param array $params provider parameters
     * 
     * @return object MongoClient
     */
    public function getConnection($params = array())
    {
        if (isset($params['server'])) {  // create new none config connection
            return $this->factory($params);
        }
        if ( ! isset($params['connection'])) {
            $params['connection'] = $this->c['config']['mongo']['default']['connection'];  //  Set default connection
        }
        if ( ! isset($this->config['connections'][$params['connection']])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Server key %s not exists in your mongo.php config file.',
                    $params['connection']
                )
            );
        }
        return $this->c['mongo.connection.'.$params['connection']];  // return to shared connection
    }

    /**
     * Create a new mongo connection if you don't want to add config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object mongo client
     */
    public function factory($params = array())
    {   
        if ( ! isset($params['server'])) {
            throw new UnexpectedValueException("Mongo connection provider requires server parameter.");
        }
        $cid = 'mongo.connection.'.self::getConnectionId($params);

        if ( ! $this->c->exists($cid)) { //  create shared connection if not exists
            $self = $this;
            $this->c[$cid] = function () use ($self, $params) {  //  create shared connections
                return $self->createConnection($params['server'], $params);
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
        foreach ($this->config['connections'] as $key => $val) {  //  Close shared connections
            $val = null;
            $connection = $this->c['mongo.connection.'.$key];
            if (is_object($connection)) {
                foreach ($connection->getConnections() as $con) {
                    $connection->close($con['hash']);
                }
            }
        }
    }

}

// END MongoConnectionProvider.php class
/* End of file MongoConnectionProvider.php */

/* Location: .Obullo/ServiceProvider/MongoConnectionProvider.php */