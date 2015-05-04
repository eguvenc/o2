<?php

namespace Obullo\Database\Adapter\Pdo;

use PDO;
use Closure;
use Exception;
use Controller;
use Obullo\Container\Container;
use Obullo\Service\ServiceProviderInterface;
    
/**
 * Adapter Class
 * 
 * @category  Database
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
abstract class Adapter
{
    public $stmt = null;      // PDOStatement Object
    public $lastSql = '';     // Stores last executed sql query
    public $queryId = 0;      // Give auto increment query ids to all queries.
    public $values = array(); // Stores last executed PDO values by execCount
    public $beginQueryTimer;  // Timer
    
    /**
     * Constructor
     * 
     * @param object $c        \Obullo\Container\Container
     * @param object $provider \Obullo\Service\ServiceProviderInterface
     * @param array  $params   service providers parameters
     */
    public function __construct(Container $c, ServiceProviderInterface $provider, array $params)
    {
        $this->c = $c;
        $this->params = $params;
        $this->provider = $provider;
    }

    /**
     * Connect to pdo, open db connections only when we need to them
     * 
     * @return void
     */
    public function connect()
    {
        if ($this->connection) {    // Lazy loading, If connection is ok not need to again connect.
            return $this;
        }
        $this->createConnection();
    }

    /**
     * Keep / reestablish the db connection if no queries have been
     * sent for a length of time exceeding the server's idle timeout
     *
     * @return void
     */
    public function reconnect()
    {
        $this->connect();
    }

    /**
     * Get pdo instance
     * 
     * @return object of pdo
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Test if a connection is active
     *
     * @return boolean
     */
    public function isConnected()
    {
        return ((bool) ($this->connection instanceof PDO));
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
        $this->connect();       // Open db connections only when we need to them
        $this->trackQuery($sql);
        $this->beginTimer();
        $this->stmt = $this->connection->prepare($sql, $options);
        return $this;
    }

    /**
     * Prepared or Direct Pdo Query
     *
     * @param string $sql query
     *
     * @return object pdo
     */
    public function query($sql)
    {
        $this->connect();
        $this->trackQuery($sql);
        $this->beginTimer();
        $this->stmt = $this->connection->query($sql);
        $this->log();
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
     * Pdo quote
     * 
     * @param string  $str  escape string
     * @param integer $type pdo type
     * 
     * @return string
     */
    public function quote($str, $type = PDO::PARAM_STR)
    {
        $this->connect();
        return $this->connection->quote($str, $type);
    }

    /**
     * Begin sql query timer
     * 
     * @return void
     */
    protected function beginTimer()
    {
        $this->beginQueryTimer = microtime(true);
    }

    /**
     * Track executed queries
     * 
     * @param string $sql sql
     *
     * @return void
     */
    protected function trackQuery($sql)
    {
        $this->lastSql = $sql;
        ++$this->queryId;
    }

    /**
     * Track pdo executed values
     * 
     * @param mixed $key key
     * @param mixed $val value
     * 
     * @return void
     */
    protected function trackValues($key, $val = '')
    {
        if (is_array($key)) {
            $this->values[$this->queryId()] = $key;
            return;
        }
        $this->values[$this->queryId()][$key] = $val;
    }

    /**
     * Begin transactions or run auto transaction with a closure.
     *
     * @param object $closure or null
     * 
     * @return boolean | object $e exception
     */
    public function transaction($closure = null)
    {
        $this->connect();
        $this->connection->beginTransaction();
        if (is_callable($closure)) {
            try
            {
                $closure();
                $this->commit();
            }
            catch(Exception $e)
            {
                $this->rollBack();
                return $e;
            }
        }
        return true;
    }

    /**
     * Commit the transaction
     * 
     * @return object
     */
    public function commit()
    {
        $this->connect();
        return $this->connection->commit();
    }

    /**
     * Check active transaction status
     * 
     * @return bool
     */
    public function inTransaction()
    {
        $this->connect();
        return $this->connection->inTransaction();
    }

    /**
     * Rollback transaction
     * 
     * @return object
     */
    public function rollBack()
    {
        $this->connect();      
        return $this->connection->rollBack();
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
        $this->connect();
        $this->trackQuery($sql);
        $this->beginTimer();
        $this->log();
        return $this->connection->exec($sql);
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
     * Alias of lastInsertId()
     *
     * @return object PDO::Statement
     */
    public function insertId()
    {
        return $this->lastInsertId();
    }

    /**
     * Alias of lastQueryId()
     *
     * @return object PDO::Statement
     */
    public function queryId()
    {
        return $this->queryId;
    }

    /**
     * Get last executed pdo query
     * 
     * @return string
     */
    public function lastQuery()
    {
        if (sizeof($this->values) > 0) {
            $newValues = array();
            $values = $this->values[$this->queryId()];
            foreach ($values as $key => $value) {
                if (is_int($value)) {
                    $newValues[$key] = $value;
                } else {
                    $newValues[$key] = $this->quote($value);
                }
            }
            $sql = preg_replace('/(?:[?])/', '%s', $this->lastSql);
            return vsprintf($sql, $newValues);
        }
        return $this->lastSql;
    }

    /**
     * Equal to pdo last insert id
     * 
     * @return integer
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Assign all controller objects into db class
     * to available closure $this->object support in transaction() method.
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

    /**
     * Log sql
     * 
     * @return void
     */
    protected function log()
    {
        if ($this->c['config']['logger']['extra']['queries']) {
            $this->c['logger']->debug(
                '$_SQL '.$this->queryId.' ( Query ):', 
                array('time' => number_format(microtime(true) - $this->beginQueryTimer, 4), 'output' => $this->formatSql($this->lastQuery())), 
                ($this->queryId * -1 )
            );
        }
    }

    /**
     * Return to last sql query string
     *
     * @param string $sql sql
     * 
     * @return void
     */
    public function formatSql($sql)
    {
        $sql = preg_replace('/\n\r\t/', ' ', $sql);
        return trim($sql, "\n");
    }

    /**
     * Close the database connetion.
     */
    public function __destruct()
    {
        $this->connection = null;
    }

}

// END Adapter Class
/* End of file Adapter.php
/* Location: .Obullo/Database/Adapter/Pdo/Adapter.php */