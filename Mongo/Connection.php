<?php

namespace Obullo\Mongo;

use MongoClient;

/**
 * Mongo Connection Manager
 * 
 * @category  Mongo
 * @package   Connection
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mongo
 */
Class Connection
{
    /**
     * Dsn connection string
     * 
     * @var string
     */
    public $dsn = '';

    /**
     * Db object
     * 
     * @var object
     */
    public $connection = null;

    /**
     * Constructor
     * 
     * Automatically check if the Mongo PECL extension has been installed / enabled.
     * 
     * @param string $c      container
     * @param string $params parameters
     * @param string $dsn    connection string
     */
    public function __construct($c, $params, $dsn = '')
    {
        $c['config']->load('nosql');  // Load nosql configuration file

        $config = $c['config']['nosql']['mongo'][$params['db']];
        $dsn = (empty($dsn)) ? 'mongodb://'.$config['username'].':'.$config['password'].'@'.$config['host'].':'.$config['port'].'/'.$params['db'] : $dsn;

        if ( ! class_exists('MongoClient', false)) {
            throw new RuntimeException('The MongoClient extension has not been installed or enabled.');
        }
        $this->dsn = $dsn;
    }

    /**
     * Connect to mongo
     * 
     * @return object MongoClient
     */
    public function connect()
    {
        $this->connection = new MongoClient($this->dsn);
        if ( ! $this->connection->connect()) {
            throw new RuntimeException('Mongo connection error.');
        }
        return $this->connection;
    }

    /**
     * Close the connection
     */
    public function __destruct()
    {
        if (is_object($this->connection)) {
            $connections = $this->connection->getConnections(); // Close all the connections.
            foreach ($connections as $con) {              
                $this->connection->close($con['hash']);
            }
        }
    }
}

// END Connection.php class
/* End of file Connection.php */

/* Location: .Obullo/Mongo/Connection.php */