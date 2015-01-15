<?php

namespace Obullo\Database\Pdo;

use PDO,
    Exception,
    Closure,
    Controller;
    
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
Abstract Class Adapter
{
    public $sql;
    public $pdo = array();      // Pdo config
    public $stmt = null;        // PDOStatement Object
    public $connection = null;  // Pdo connection object.
    public $startQueryTimer;    // Timer
    public $escapeChar = '`';   // The character used for escaping
    public $config;             // Config class
    public $logger;             // Logger class
    public $host = '';
    public $username = '';
    public $password = '';
    public $database = '';
    public $driver = '';      // optional
    public $charset = 'utf8'; // optional
    public $port = '';        // optional
    public $dsn = '';         // optional
    public $options = array(); // optional
    public $autoinit = array(); // optional
    public $prefix = '';
    public $prepare = false;    // Prepare used or not
    public $lastSql = null;     // stores last queried sql
    public $lastValues = array();  // stores last executed PDO values by execCount
    public $queryCount = 0;        // count all queries.
    public $execCount = 0;        // count exec methods.
    public $prepQueries = array();
    public $useBindValues = false;    // bind value usage switch
    public $useBindParams = false;    // bind param usage switch
    public $lastBindValues = array();  // Last bindValues and bindParams
    public $lastBindParams = array();  // We store binds values to array()
    public $protectIdentifiers = true;
    public $reservedIdentifiers = array('*'); // Identifiers that should NOT be escaped

    /**
     * Constructor
     *
     * @param array $c      container
     * @param array $params db array
     */
    public function __construct($c, $params = array())
    {
        $this->options = array();
        foreach (array('host','username','password','database','prefix','port','charset','autoinit','dsn','pdo') as $key) {
            $this->{$key} = (isset($params[$key]) AND ! empty($params[$key])) ? $params[$key] : $this->{$key}; 
        }
        $this->config = $c->load('config');
        $this->logger = $c->load('service/logger');
    }

    /**
     * Connect to PDO
     *
     * @param string $dsn     data source name
     * @param string $user    username
     * @param mixed  $pass    password
     * @param array  $options driver options
     * 
     * @return void
     */
    public function connection($dsn, $user = null, $pass = null, $options = null)
    {
        $this->connection = new PDO($dsn, $user, $pass, $options);
    }

    /**
     * Parse array and escape
     * 
     * @param array $data values
     * 
     * @return array escaped data
     */
    protected function escapeArray($data)
    {
        $newData = array();
        foreach ($data as $key => $value) {
            $newData[$key] = $this->_escape($value);
        }
        return $newData;
    }

    /**
     * Set pdo prepare function
     *
     * @param string $sql     prepared query
     * @param array  $fields  array fields
     * @param array  $options prepare options
     *
     * @return object adapter
     */
    public function prepare($sql, $fields = array(), $options = array())
    {
        $this->connect();
        $this->startQueryTimer = microtime(true);
        $this->lastSql = $this->sprintf($sql, $fields);

        $this->stmt = $this->connection->prepare($this->lastSql, $options);
        $this->prepQueries[] = $this->lastSql;  // Save the  query for debugging
        $this->prepare = true;
        ++$this->queryCount;
        return $this;
    }

    /**
     * Prepared or Direct Pdo Query
     * 
     * @param string $sql     query
     * @param array  $sprintf array fields
     * @param array  $values  bind values
     * 
     * @return object pdo
     */
    public function query($sql, $sprintf = array(), $values = array())
    {
        $this->connect();
        $this->lastSql = $this->sprintf($sql, $sprintf);
        $this->startQueryTimer = microtime(true);

        if (count($values) > 0) {
            $this->prepare($this->lastSql);
            $this->execute($values);
            return $this;
        } else {
            $this->stmt = $this->connection->query($this->lastSql);
        }
        ++$this->queryCount;
        $this->sqlLog($this->lastSql);

        return ($this);
    }

    public function write($sql, $sprintf, $columns = array(), $values = array())
    {
        $this->connect();
        $this->lastSql = $this->_prepSQL($sql, $sprintf, $values);

        if (count($values) > 0) {
            $this->prepare($this->lastSql);
            $this->execute($values);
            return $this;
        } else {
            $this->stmt = $this->connection->query($this->lastSql);
        }
        ++$this->queryCount;
        $this->sqlLog($this->lastSql);

        return ($this);
    }

    /**
     * Checks array is multidimensional
     * 
     * @param array $array array
     * 
     * @return boolean
     */
    public function isMultiArray($array)
    {
        foreach ($array as $value) {
            if (is_array($value)) return true;
        }
        return false;
    }

    /**
     * Protect array values
     * 
     * @param string $sql   sql strings
     * @param array  $array protect values
     * 
     * @return array rendered array
     */
    protected function sprintf($sql, $array)
    {
        if (count($array) == 0) {   // If we have no sprintf data
            return $sql;
        }
        $newArray = array();
        foreach ($array as $key => $value) {
            $newArray[$key] = $value;
        }
        return vsprintf($sql, $newArray);
    }

    /**
     * PDO Last Insert Id
     *
     * @return object PDO::Statement
     */
    public function insertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Get pdo instance
     * 
     * @return object of pdo
     */
    public function getConnection()
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
     * Reconnect
     *
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
     * Begin transactions or run auto 
     * transaction with a closure.
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
                $this->bind($closure);
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
     * Run Closure
     *
     * @param mixed $val closure or string
     * 
     * @return mixed
     */
    protected function bind($val)
    {
        $closure = Closure::bind($val, $this, get_class());
        return $closure();
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
     * Set attribute
     * 
     * @param string $key name
     * @param string $val value
     *
     * @return void
     */
    public function setAttribute($key, $val)
    {
        $this->connect();
        $this->connection->setAttribute($key, $val);
    }

    /**
     * Get pdo attribute
     * 
     * @param string $key key
     * 
     * @return mixed 
     */
    public function getAttribute($key)
    {
        return $this->connection->getAttribute($key);
    }

    /**
     * Return error info in PDO::PDO::ERRMODE_SILENT mode
     * 
     * @return type 
     */
    public function errorInfo()
    {
        return $this->connection->errorInfo();
    }

    /**
     * Get available drivers on your host
     *
     * @return object PDO::Statement
     */
    public function getDrivers()
    {
        return $this->connection->getAvailableDrivers();
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
        $this->useBindParams = true;
        $this->lastBindParams[$param] = $val;
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
        $this->useBindValues = true;
        $this->lastBindValues[$param] = $val;
        return $this;
    }

    /**
     * Smart Escape String via PDO Escapes data based on type
     * Sets boolean and null types
     *
     * @param string $str escape value
     * 
     * @return mixed
     */
    public function escape($str)
    {
        if (is_string($str)) {
            return $this->_escape($str);
        }
        return $str;
    }

    /**
     * Escape LIKE String
     *
     * Calls the individual driver for platform
     * specific escaping for LIKE conditions
     *
     * @param string $str  input value
     * @param string $side direction
     * 
     * @return mixed
     */
    public function escapeLike($str, $side = 'both')
    {
        return $this->_escape($str, true, $side);
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

        if (isset($this->prepQueries[0])) {
            $this->sqlLog(end($this->prepQueries));
        }
        $this->prepare = false;  // reset prepare variable and prevent collision with next query ..
        ++$this->execCount;      // count execute of prepared statements ..

        $this->lastValues = array();   // reset last bind values ..

        if (is_array($array)) {         // store last executed bind values for last_query method.
            $this->lastValues[$this->execCount] = $array;
        } elseif ($this->useBindValues) {
            $this->lastValues[$this->execCount] = $this->lastBindValues;
        } elseif ($this->useBindParams) {
            $this->lastValues[$this->execCount] = $this->lastBindParams;
        }
        $this->useBindValues  = false;         // reset query bind usage data ..
        $this->useBindParams  = false;
        $this->lastBindValues = array();
        $this->lastBindParams = array();

        return $this->stmt;
    }

    /**
     * Exec used just for CREATE, DELETE, INSERT and UPDATE operations it returns to
     * number of [affected rows] after the write operations.
     *
     * @param string $sql    query sql
     * @param array  $fields array fields
     * 
     * @return boolean
     */
    public function exec($sql, $fields = array())
    {
        $this->connect();
        $this->lastSql = $this->sprintf($sql, $fields);

        $this->startQueryTimer = microtime(true);
        $affectedRows = $this->connection->exec($this->lastSql);
        ++$this->queryCount;

        $this->sqlLog($this->lastSql);

        return $affectedRows;
    }

    /**
     * Return to last sql query string
     *
     * @param string $sqlStr string sql
     * 
     * @return void
     */
    protected static function getSqlString($sqlStr)
    {
        $sql = preg_replace('/\n/', ' ', $sqlStr);
        return trim($sql, "\n");
    }

    /**
     * Returns number of rows.
     *
     * @return integer
     */
    public function count()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Get row as object & if fail return false
     *
     * @param boolean $failArray return array if fail
     * 
     * @return array | object otherwise false
     */
    public function row($failArray = false)
    {
        $result = $this->stmt->fetch(PDO::FETCH_OBJ);
        if ($result === false AND $failArray == true) {
            return array();
        }
        return $result;
    }

    /**
     * Get row as array & if fail return 
     * 
     * @param boolean $failArray return array if fail
     * 
     * @return array | object otherwise false
     */
    public function rowArray($failArray = false)
    {
        $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false AND $failArray == true) {
            return array();
        }
        return $result;
    }

    /**
     * Get results as array & if fail return ARRAY
     *
     * @param boolean $failArray return array if fail
     * 
     * @return array | object otherwise false
     */
    public function result($failArray = false)
    {
        $result = $this->stmt->fetchAll(PDO::FETCH_OBJ);
        if ($result === false AND $failArray == true) {
            return array();
        }
        return $result;
    }

    /**
     * Get results as array & if fail return ARRAY
     * 
     * @param boolean $failArray return array if fail
     * 
     * @return array | object otherwise false
     */
    public function resultArray($failArray = false)
    {
        $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($result === false AND $failArray == true) {
            return array();
        }
        return $result;
    }

    /**
     * Get the pdo statement object and use native pdo functions.
     *
     *  Example: 
     *  $stmt = $this->db->getStatement();
     *  $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
     * 
     * @return object
     */
    public function getStatement()
    {
        return $this->stmt;
    }
    
    /**
     * Get last executed pdo query
     * 
     * @return string
     */
    public function lastQuery()
    {
        if (sizeof($this->lastValues) > 0) {
            $values = $this->lastValues[$this->execCount];
            $sql = preg_replace('/(?:[?])/', '%s', $this->lastSql);
            $newValues = array();
            foreach ($values as $key => $value) {
                if (is_int($value)) {
                    $newValues[$key] = $value;
                } else {
                    $newValues[$key] = $this->quote($value);
                }
            }
            $this->lastValues = array();  // Reset values
            return vsprintf($sql, $newValues);
        }
        return $this->lastSql;
    }

    /**
     * Insert data to table
     * 
     * @param string $table    tablename
     * @param array  $data     insert data
     * @param array  $extraSql extra sql 
     * 
     * @return integer affected rows
     */
    public function insert($table, $data = array(), $extraSql = null)
    {
        $this->connect();
        $data = $this->arrayEscape($data);
        $sql = $this->_insert($table, array_keys($data), array_values($data), $extraSql);
        return $this->exec($sql);
    }

    /**
     * Replace the in the table
     * 
     * @param string $table tablename
     * @param array  $data  insert data
     * 
     * @return integer affected rows
     */
    public function replace($table, $data = array())
    {
        $this->connect();
        $data = $this->arrayEscape($data);
        $sql = $this->_replace($table, array_keys($data), array_values($data));
        return $this->exec($sql);
    }

    /**
     * Update table
     * 
     * @param string $table    tablename
     * @param array  $data     array
     * @param array  $where    array
     * @param string $extraSql add extra sql end of your query
     * @param int    $limit    sql limit
     *  
     * @return integer affected rows
     */
    public function update($table, $data = array(), $where = array(), $extraSql = '', $limit = false)
    {     
        $this->connect();
        $data = $this->arrayEscape($data);
        $where = $this->arrayEscape($where);
        $conditions = $this->buildConditions($where);
        $sql = $this->_update($table, $data, $conditions, array(), $limit, $extraSql);
        return $this->exec($sql);
    }

    /**
     * Delete data from table
     * 
     * @param string $table    tablename
     * @param array  $where    array
     * @param string $extraSql add extra sql end of your query
     * @param int    $limit    sql limit
     *  
     * @return integer affected rows
     */
    public function delete($table, $extraSql = '', $limit = false)
    {
        $this->connect();
        $where = $this->arrayEscape($where);
        
        // $conditions = $this->buildConditions($where);

        $sql = $this->_delete($table, $conditions, array(), $limit, $extraSql);
        return $this->exec($sql);
    }

    // /**
    //  * Build where conditions
    //  * 
    //  * @param array $where conditions
    //  * 
    //  * @return void
    //  */
    // protected function buildConditions($where) 
    // {
    //     if (empty($where)) return;
    //     $newWhere = array();
    //     foreach ($where as $key => $value) {
    //         $newWhere[] = $key.' = '.$value;
    //     }
    //     $conditions = array();
    //     $conditions[] = $newWhere[0];
    //     if (count($newWhere) > 1) {
    //         unset($newWhere[0]);
    //         foreach ($newWhere as $key => $value) {
    //             $conditions[] = "\nAND ".$value;
    //         }
    //     }
    //     return $conditions;
    // }

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
        return Controller::$instance->{$key};
    }

    /**
     * Log sql
     * 
     * @param string $sql sql query
     * 
     * @return void
     */
    protected function sqlLog($sql)
    {
        $time  = microtime(true) - $this->startQueryTimer;

        if ($this->config['log']['extra']['queries']) {
            $this->logger->debug(
                '$_SQL '.$this->queryCount.' ( Query ):', 
                array('time' => number_format($time, 4), 'output' => self::getSqlString($sql)), 
                ($this->queryCount * -1 )
            );
        }
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
/* Location: .Obullo/Database/Pdo/Adapter.php */