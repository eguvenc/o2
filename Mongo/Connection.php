<?php

namespace Obullo\Mongo;

use MongoClient,
    Controller,
    RuntimeException;
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
     * Containser
     * 
     * @var object
     */
    protected $c;

    /**
     * Data source name
     * 
     * @var string
     */
    protected $dsn;

    /**
     * Container parameters
     * 
     * @var array
     */
    protected $commands;

    /**
     * Connection object
     * 
     * @var object
     */
    protected $connection = null;

    /**
     * Constructor
     * 
     * Automatically check if the Mongo PECL extension has been installed / enabled.
     * 
     * @param string $c        container
     * @param string $commands container cmd parameters
     */
    public function __construct($c, $commands = array())
    {
        $c['config']->load('mongo');  // Load nosql configuration file
        
        $this->c = $c;
        $this->commands = $commands;

        if ( ! class_exists('MongoClient', false)) {
            throw new RuntimeException('The MongoClient extension has not been installed or enabled.');
        }
    }

    /**
     * Connect to mongo
     * 
     * @return object MongoClient
     */
    protected function connect()
    {
        if ($this->connection) {  //  Lazy load
            return;
        }
        $this->connection = new MongoClient($this->dsn);
        if ( ! $this->connection->connect()) {
            throw new RuntimeException('Mongo connection error.');
        }                                   
    }

    /**
     * Select database if connection availavle don't create new connection 
     * 
     * @param string $db name
     * 
     * @return object
     */
    public function db($db = null)
    {
        $db = (empty($db)) ? $this->c['config']['mongo']['default']['database'] : $db;
        $mongo = $this->c['config']['mongo']['key'][$db];
        
        $connStr = 'mongodb://'.$mongo['username'].':'.$mongo['password'].'@'.$mongo['host'].':'.$mongo['port'].'/'.$db;
        $this->dsn = (empty($this->params['dsn'])) ? $connStr : $this->params['dsn'];

        $this->connect();  //  Lazy load connection
        $database = $this->connection->selectDb($db);

        $this->reAssign($database);  // Assign to controller

        return $database;
    }

    /**
     * Reassign selected database instance to controller
     *
     * @param object $database MongoDB object
     * 
     * @return void
     */
    protected function reAssign($database)
    {
        if (Controller::$instance != null) {
            $as = empty($this->commands['as']) ? 'mongo' : $this->commands['as'];
            return Controller::$instance->{$as} = $database;
        }
    }

    /**
     * Close the connection
     */
    public function __destruct()
    {
        if (is_object($this->connection)) {
            $connections = $this->connection->getConnections(); // Close all the connections.
            foreach ($connections as $con) {
                print_r($con);
                $this->connection->close($con['hash']);
            }
        }
    }
}

// END Connection.php class
/* End of file Connection.php */

/* Location: .Obullo/Mongo/Connection.php */