<?php

namespace Obullo\Mongo;

use MongoClient,
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
     * Db object
     * 
     * @var object
     */
    public $connection = null;

    /**
     * Containser
     * 
     * @var object
     */
    protected $c;

    /**
     * Config params
     * 
     * @var array
     */
    protected $params;

    /**
     * Loader commands
     * 
     * @var array
     */
    protected $commands;

    /**
     * Data source name
     * 
     * @var string
     */
    protected $dsn;

    /**
     * Constructor
     * 
     * Automatically check if the Mongo PECL extension has been installed / enabled.
     * 
     * @param string $c        container
     * @param string $params   parameters
     * @param array  $commands loader command parameters ( new, return, as, class .. )
     */
    public function __construct($c, $params, $commands = array())
    {
        $c['config']->load('mongo');  // Load nosql configuration file
        $this->params = $params;
        $this->commands = $commands;

        $db = (empty($this->params['db'])) ? $this->c['config']['mongo']['default']['database'] : $this->params['db'];

        $mongo = $this->c['config']['mongo']['key'][$db];
        $connStr = 'mongodb://'.$mongo['username'].':'.$mongo['password'].'@'.$mongo['host'].':'.$mongo['port'].'/'.$db;
        $this->dsn = (empty($this->params['dsn'])) ? $connStr : $this->params['dsn'];

        if ( ! class_exists('MongoClient', false)) {
            throw new RuntimeException('The MongoClient extension has not been installed or enabled.');
        }
    }

    /**
     * Connect to mongo
     * 
     * @return object MongoClient
     */
    public function connect()
    {
        /**
        * Provider default instance fix.
        * New keyword support.
        * 
        * If default database already available we need return to old instance.
        * But if new keyword used in loader $this->c->load('new service/provider/db'); this time we cannot
        * return to old instance.
        */ 
        if ($this->c->exists('mongo')  //  Is service available ?
            AND $this->commands['class'] == 'service/provider/mongo'  // Is this provider request ?
            AND empty($this->commands['new']) 
            AND $this->params['db'] == $this->c['config']['mongo']['default']['database']
        ) {
            return $this->c->load('return service/mongo'); // return to current mongo instance
        }
        $this->connection = new MongoClient($this->dsn);
        if ( ! $this->connection->connect()) {
            throw new RuntimeException('Mongo connection error.');
        }
        $this->connection->provider = $this;  // We need store connection instance to mongo client object
                                              // to run provider getName() method in service mongo.
        return $this->connection;
    }

    /**
     * Returns to database name provider
     * 
     * @return string
     */
    public function getName()
    {
        return $this->db;
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