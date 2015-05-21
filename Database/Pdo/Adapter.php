<?php

namespace Obullo\Database\Pdo;

use PDO;
use Closure;
use Exception;
use Controller;
use Obullo\Container\Container;
use Obullo\Database\AdapterInterface;
use Obullo\Database\SQLLoggerInterface;
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
class Adapter implements AdapterInterface
{
    /**
     * Available drivers
     * 
     * @var array
     */
    public $drivers = [
        'pdo_mysql',
        'pdo_pgsql',
    ];

    /**
     * Pdo connection object
     * 
     * @var object
     */
    public $conn;

    /**
     * Connection Params
     * 
     * @var array
     */
    public $params;    // Pdo provider parameters

    /**
     * Statement
     * 
     * @var null
     */
    public $stmt = null; // PDOStatement Object

    /**
     * Benchmark variables
     * 
     * @var string
     */
    public $sql;                    // Stores last executed sql query
    public $start;                  // Timer
    public $parameters = array();   // Stores last executed PDO values

    /**
     * SQLLogger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Constructor
     * 
     * @param array $params connection params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->logger = $this->getSQLLogger();
    }

    /**
     * Get sql logger object
     * 
     * @return void
     */
    protected function getSQLLogger()
    {
        if (isset($this->params['logger']) && $this->params['logger'] instanceof SQLLoggerInterface) {
            return $this->params['logger'];
        }
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
     * Connect to pdo, open db connections only when we need to them
     * 
     * @return void
     */
    public function connect()
    {   
        if ($this->conn) {    // Lazy loading, If connection is ok not need to again connect.
            return false;
        }
        $this->createConnection();
        return true;
    }

    /**
     * Get pdo instance
     * 
     * @return object of pdo
     */
    public function connection()
    {
        return $this->conn;
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
        $this->sql = $sql;
        $this->stmt = $this->conn->prepare($sql, $options);
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
        
        $this->connect();
        $this->startQuery($sql);
        $this->stmt = $this->conn->query($sql);
        $this->stopQuery();
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
        $this->trackParams($param, $val);
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
        $this->trackParams($param, $val);
        return $this;
    }

    /**
     * Execute prepared query
     *
     * @param array $params bound : default must be null.
     * 
     * @return object of Stmt
     */
    public function execute($params = null)
    {
        $this->trackParams($params);     // Store last executed bind values for last_query method.
        $this->startQuery($this->sql);
        $this->stmt->execute($params);
        $this->stopQuery();
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
        $this->startQuery($sql);
        $return = $this->conn->exec($sql);
        $this->stopQuery();
        return $return;
    }

    /**
     * Begin transaction
     * 
     * @return void
     */
    public function beginTransaction()
    {
        $this->connect();
        return $this->conn->beginTransaction();
    }

    /**
     * Begin transactions or run auto transaction with a closure.
     *
     * @param object $func Closure
     * 
     * @return void
     */
    public function transactional(Closure $func)
    {
        $this->beginTransaction();
        try
        {
            $return = $func();
            $this->commit();
            return $return ?: true;  // Only fail if we have exceptions
        }
        catch(Exception $e)
        {
            $this->rollBack();
            throw $e;           // throw a PDOException developer will catch it 
        }
    }

    /**
     * Commit the transaction
     * 
     * @return object
     */
    public function commit()
    {
        $this->connect();
        return $this->conn->commit();
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
     * Rollback transaction
     * 
     * @return object
     */
    public function rollBack()
    {
        $this->connect();      
        return $this->conn->rollBack();
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
        return $this->conn->lastInsertId($name);
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
        $this->connect();
        return $this->conn->quote($str, PDO::PARAM_STR);
    }

    /**
     * Track pdo executed values
     * 
     * @param mixed $key key
     * @param mixed $val value
     * 
     * @return void
     */
    protected function trackParams($key, $val = '')
    {
        if (empty($key)) {
            return;
        }
        if (is_array($key)) {
            $this->parameters = $key;
            return;
        }
        $this->parameters[$key] = $val;
    }

    /**
     * Start query timer & add sql log
     * 
     * @param string $sql sql
     * 
     * @return void
     */
    protected function startQuery($sql)
    {
        $this->sql = $sql;
        $params = $this->getParameters(null);
        if ($this->logger) {
            $this->logger->startQuery($sql, $params);
        }
    }

    /**
     * Stop query timer & write sql log
     * 
     * @return void
     */
    protected function stopQuery()
    {
        if ($this->logger) {
            $this->logger->stopQuery();
        }
        $this->parameters = array();
    }

    /**
     * Get prepared parameters
     *
     * @param mixed $failure result value if empty
     * 
     * @return array|null
     */
    public function getParameters($failure = array())
    {
        return empty($this->parameters) ? $failure : $this->parameters;
    }

    /**
     * Quotes a string so that it can be safely used as a table or column name,
     * even if it is a reserved word of the platform. This also detects identifier
     * chains separated by dot and quotes them independently.
     *
     * NOTE: Just because you CAN use quoted identifiers doesn't mean
     * you SHOULD use them. In general, they end up causing way more
     * problems than they solve.
     *
     * @param string $str The identifier name to be quoted.
     *
     * @return string The quoted identifier string.
     */
    public function quoteIdentifier($str)
    {
        if (strpos($str, ".") !== false) {
            $parts = array_map(array($this, "quoteSingleIdentifier"), explode(".", $str));

            return implode(".", $parts);
        }

        return $this->quoteSingleIdentifier($str);
    }

    /**
     * Quotes a single identifier (no dot chain separation).
     *
     * @param string $str The identifier name to be quoted.
     *
     * @return string The quoted identifier string.
     */
    public function quoteSingleIdentifier($str)
    {
        $c = $this->getIdentifierQuoteCharacter();

        return $c . str_replace($c, $c.$c, $str) . $c;
    }

    /**
     * Get identifier char
     * 
     * @return string
     */
    public function getIdentifierQuoteCharacter()
    {
        return $this->escapeIdentifier;
    }

    /**
     * Assign controller objects into db class
     * to available closure $this->db support in transactional() method.
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
     * Close the database connetion.
     */
    public function __destruct()
    {
        $this->conn = null;
    }

}

// END Adapter Class

/* End of file Adapter.php
/* Location: .Obullo/Database/Pdo/Adapter.php */