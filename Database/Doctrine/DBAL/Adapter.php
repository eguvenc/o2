<?php

namespace Obullo\Database\Doctrine\DBAL;

use PDO;
use Controller;
use Obullo\Container\Container;
use Obullo\Database\AdapterInterface;
use Obullo\Database\CommonAdapterTrait;
use Obullo\Service\ServiceProviderInterface;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Configuration;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Cache\QueryCacheProfile;

/**
 * Doctrine DBAL Adapter Class
 * 
 * @category  Database
 * @package   DBALAdapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
class Adapter extends Connection
{
    /**
     * Available drivers
     * 
     * @var array
     */
    public $drivers = [
        'db2',         // Doctrine\DBAL\Driver\IBMDB2\DB2Driver
        'drizzle_pdo_mysql', // Doctrine\DBAL\Driver\DrizzlePDOMySql\Driver
        'ibm_db2',     // Doctrine\DBAL\Driver\IBMDB2\DB2Driver
        'mssql',       // Doctrine\DBAL\Driver\PDOSqlsrv\Driver
        'mysql',       // Doctrine\DBAL\Driver\PDOMySql\Driver
        'mysqli',      // Doctrine\DBAL\Driver\Mysqli\Driver
        'mysql2',      // Doctrine\DBAL\Driver\PDOMySql\Driver // Amazon RDS, for some weird reason
        'oci8',        // Doctrine\DBAL\Driver\OCI8\Driver
        'pdo_mysql',   // Doctrine\DBAL\Driver\PDOMySql\Driver
        'pdo_sqlite',  // Doctrine\DBAL\Driver\PDOSqlite\Driver
        'pdo_pgsql',   // Doctrine\DBAL\Driver\PDOPgSql\Driver
        'pdo_oci',     // Doctrine\DBAL\Driver\PDOOracle\Driver
        'pdo_sqlsrv',  // Doctrine\DBAL\Driver\PDOSqlsrv\Driver
        'postgres',    // Doctrine\DBAL\Driver\PDOPgSql\Driver
        'postgresql',  // Doctrine\DBAL\Driver\PDOPgSql\Driver
        'pgsql',       // Doctrine\DBAL\Driver\PDOPgSql\Driver
        'sqlite',      // Doctrine\DBAL\Driver\PDOSqlite\Driver
        'sqlite3',     // Doctrine\DBAL\Driver\PDOSqlite\Driver
        'sqlanywhere', // Doctrine\DBAL\Driver\SQLAnywhere\Driver
        'sqlsrv',      // Doctrine\DBAL\Driver\SQLSrv\Driver 
    ];

    /**
     * Statement
     * 
     * @var null
     */
    public $stmt = null; // PDOStatement Object

    /**
     * Initializes a new instance of the Connection class.
     *
     * @param array                              $params       The connection parameters.
     * @param \Doctrine\DBAL\Driver              $driver       The driver to use.
     * @param \Doctrine\DBAL\Configuration|null  $config       The configuration, optional.
     * @param \Doctrine\Common\EventManager|null $eventManager The event manager, optional.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(array $params, Driver $driver, Configuration $config = null, EventManager $eventManager = null)
    {
        $params['user'] = $params['username'];  // Doctrine changes.

        if (isset($params['options'])) {
            $params['driverOptions'] = $params['options'];
        }
        parent::__construct($params, $driver, $config, $eventManager);
    }

    /**
     * Generate db results
     * 
     * @param string $method    name
     * @param array  $arguments method arguments
     * 
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array(new Result($this->stmt), $method), $arguments);
    }

    /**
     * Returns the list of supported drivers.
     *
     * @return array
     */
    public function drivers()
    {
        return $this->drivers;
    }

    /**
     * Establishes the connection with the database.
     *
     * @param string $name possible master slave connection support
     * 
     * @return boolean TRUE if the connection was successfully established, FALSE if
     *                 the connection is already open.
     */
    public function connect($name = null)
    {
        $name = null;
        if ($this->_conn) {    // Lazy loading, If connection is ok not need to again connect.
            return false;
        }
        parent::connect();
        return true;
    }

    /**
     * Get pdo instance
     * 
     * @return object of pdo
     */
    public function connection()
    {
        return $this->_conn;
    }

    /**
     * Check active transaction status
     * 
     * @return bool
     */
    public function inTransaction()
    {
        $this->connect();
        return $this->conn->inTransaction();
    }

    /**
     * Set pdo prepare function
     *
     * @param string $sql     prepared query
     * @param array  $options prepare options
     *
     * @return object adapter
     */
    public function prepare($sql, $options = array())
    {
        $this->trackQuery($sql);
        $this->beginTimer();

        $this->stmt = parent::prepare($sql, $options);
        return $this;
    }

    /**
     * Prepared or Direct Pdo Query
     *
     * @return object pdo
     */
    public function query()
    {
        $args = func_get_args();
        $sql = $args[0];

        $this->trackQuery($sql);
        $this->beginTimer();

        $this->stmt = parent::query($sql);
        $this->log();
        return $this;
    }

    /**
     * Equal to PDO_Statement::bindParam()
     *
     * @param string  $param   parameter name
     * @param mixed   $val     parameter value
     * @param mixed   $type    pdo fetch constant
     * @param integer $length  parameter length
     * @param array   $options parameter option
     *
     * @return object
     */
    public function bindParam($param, $val, $type, $length = null, $options = null)
    {
        $this->stmt->bindParam($param, $val, $type, $length, $options);
        $this->trackValues($param, $val);
        return $this;
    }

    /**
     * Equal to PDO_Statement::bindValue()
     *
     * @param integer $param parameter number 
     * @param mixed   $val   parameter value
     * @param string  $type  pdo fecth constant
     *
     * @return object
     */
    public function bindValue($param, $val, $type)
    {
        $this->stmt->bindValue($param, $val, $type);
        $this->trackValues($param, $val);
        return $this;
    }

    /**
     * Execute prepared query
     *
     * @param array $array bound : default must be null.
     * 
     * @return object of Stmt
     */
    public function execute($array = null)
    {
        $this->stmt->execute($array);
        $this->trackValues($array);     // Store last executed bind values for last_query method.
        $this->log();
        return $this;
    }

    /**
     * [executeQuery description]
     * @param  [type]                 $query  [description]
     * @param  array                  $params [description]
     * @param  array                  $types  [description]
     * @param  QueryCacheProfile|null $qcp    [description]
     * @return [type]                         [description]
     */
    public function executeQuery($query, array $params = array(), $types = array(), QueryCacheProfile $qcp = null)
    {
        $this->stmt = parent::executeQuery($query, $params, $types, $qcp);
        return $this;
    }

    /**
     * Get the pdo statement object and use native pdo functions.
     * 
     * Example: $this->db->stmt()->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
     * 
     * @return object
     */
    public function stmt()
    {
        return $this->stmt;
    }

    /**
     * Exec just CREATE, DELETE, INSERT and UPDATE operations.
     * 
     * Returns to number of affected rows.
     *
     * @param string $sql query sql
     * 
     * @return boolean
     */
    public function exec($sql)
    {
        $this->trackQuery($sql);
        $this->beginTimer();
        $this->log();

        return parent::exec($sql);
    }

    /**
     * Alias of lastInsertId()
     *
     * @param string $name name null
     * 
     * @return object PDO::Statement
     */
    public function insertId($name = null)
    {
        return $this->lastInsertId($name);
    }

    /**
     * Pdo quote function.
     * 
     * @param mixed $str string
     * 
     * @return string
     */
    public function escape($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                if (is_string($val)) {
                    $str[$key] = $this->escape($val);
                }
            }
            return $str;
        }
        return $this->quote($str);
    }

    /**
     * Assign all controller objects into db class
     * to available closure $this->object support in beginTransaction() method.
     *
     * @param string $key Controller variable
     * 
     * @return void
     */
    public function __get($key)
    {
        if (isset(Controller::$instance->{$key})) {
            return Controller::$instance->{$key};
        }
    }


}

// END Adapter Class

/* End of file Adapter.php
/* Location: .Obullo/Database/Adapter/Doctrine/DBAL/Adapter.php */