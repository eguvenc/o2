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
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/mongo
 */
Class MongoConnection
{
    /**
     * Dsn connection string
     * 
     * @var string
     */
    public $dsn;

    /**
     * Db object
     * 
     * @var object
     */
    public $db = null;

    /**
     * Constructor
     * 
     * @param string $dsn connection string
     */
    public function __construct($dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * Connect to mongo
     * 
     * @return object MongoClient
     */
    public function connect()
    {
        return $this->db = new MongoClient($this->dsn);
    }

    /**
     * Close the connection
     * 
     * @return void
     */
    public function __destruct()
    {
        if (is_object($this->db)) {
            $this->db->close();
        }
    }
}

// END MongoConnection.php class
/* End of file MongoConnection.php */

/* Location: .Obullo/Mongo/MongoConnection.php */