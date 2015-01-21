<?php

namespace Obullo\Provider;

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
Class MongoConnector
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Configuration items
     * 
     * @var void
     */
    protected $config;

    /**
     * Mongo extension client name
     * 
     * @var string
     */
    protected $mongoClass;

    /**
     * Presence of a static member variable
     * 
     * @var null
     */
    protected static $instance = null;

    /**
     * Returns the singleton instance of this class.
     *
     * @param object $c Container
     * 
     * @return singleton instance.
     */
    public static function getInstance($c)
    {
        if (null === self::$instance) {
            self::$instance = new static($c);
        }
        return self::$instance;
    }

    /**
     * Constructor
     * 
     * Automatically check if the Mongo PECL extension has been installed / enabled.
     * 
     * @param string $c      container
     * @param string $params container parameters
     */
    protected function __construct(Container $c, $params = array())
    {
        $this->c = $c;
        $this->params = $params;
        $this->config = $c['config']->load('mongo');  // Load nosql configuration file
        $this->mongoClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? '\Mongo' : '\MongoClient';

        if ( ! class_exists($this->mongoClass, false)) {
            throw new RuntimeException(
                sprintf(
                    'The %s extension has not been installed or enabled.', 
                    $this->mongoClass
                )
            );
        }
    }

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
     * Register all connections as shared services 
     *
     * Warning : It should be run one time
     * 
     * @return void
     */
    public function register()
    {
        foreach ($this->config['servers'] as $key => $val) {
            $server = 'mongodb://'.$val['username'].':'.$val['password'].'@'.$val['host'].':'.$val['port'];
            $this->c['mongo.connection.'.$key] = function () use ($server, $val) {  //  create shared connections
                return new $this->mongoClass($server, $val['options']);
            };
        }
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
        if ( ! isset($params['server'])) {
            $params['server'] = $this->c['config']['mongo']['default']['connection'];  //  Set default connection
        }
        if ( ! isset($this->config['servers'][$params['server']])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Server key %s not exists in your mongo.php config file.',
                    $params['server']
                )
            );
        }
        return $this->c['mongo.connection.'.$params['server']];  // return to shared connection
    }

    /**
     * Create a new mongo connection if you don't want 
     * to add config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object mongo client
     */
    public function factory($params = array())
    {   
        if ( ! isset($params['server'])) {
            throw new UnexpectedValueException("Mongo connector requires server parameter.");
        }
        $options = isset($params['options']) ? $params['options'] : array('connect' => true);

        return new $this->mongoClass($params['server'], $options);  // Create new connection
    }

    /**
     * Close the connections
     */
    public function __destruct()
    {
        foreach ($this->config['servers'] as $key => $val) {  //  Close shared connections
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

// END MongoConnector.php class
/* End of file MongoConnector.php */

/* Location: .Obullo/Provider/MongoConnector.php */