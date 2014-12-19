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
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/mongo
 */
Class MongoConnection
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
     * Automatically check if the Mongo PECL extension has been installed/enabled.
     * 
     * @param string $dsn connection string
     */
    public function __construct($dsn)
    {
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
     * 
     * @return void
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

// END MongoConnection.php class
/* End of file MongoConnection.php */

/* Location: .Obullo/Mongo/MongoConnection.php */