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
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/database
 */
Class Adapter
{
    public $sql;
    public $pdo = array();      // Pdo config
    public $pdoObject = null;   // Pdo object.
    public $Stmt = null;        // PDOStatement Object
    public $connection = null;  // Pdo connection object.
    public $startQueryTimer;    // Timer
    public $escapeChar = '`';   // The character used for escaping
    public $config;     // Config class
    public $logger;     // Logger class
    public $host = '';
    public $username = '';
    public $password = '';
    public $database = '';
    public $driver = '';      // optional
    public $charset = 'utf8'; // optional
    public $port = '';        // optional
    public $dsn = '';         // optional
    public $options = array(); // optional
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
    public function pdoConnect($dsn, $user = null, $pass = null, $options = null)
    {
        $this->connection = new PDO($dsn, $user, $pass, $options);
        return $this;
    }

    /**
     * Insert data to table
     * 
     * @param string $table tablename
     * @param array  $data  insert data
     * 
     * @return integer affected rows
     */
    public function insert($table, $data = array())
    {
        $data = $this->doArrayEscape($data);
        $sql = $this->_insert($table, array_keys($data), array_values($data));
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
        $data = $this->doArrayEscape($data);
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
        $data = $this->doArrayEscape($data);
        $where = $this->doArrayEscape($where);
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
    public function delete($table, $where = array(), $extraSql = '', $limit = false)
    {
        $where = $this->doArrayEscape($where);
        $conditions = $this->buildConditions($where);
        $sql = $this->_delete($table, $conditions, array(), $limit, $extraSql);
        return $this->exec($sql);
    }

    /**
     * Build where conditions
     * 
     * @param array $where conditions
     * 
     * @return void
     */
    protected function buildConditions($where) 
    {
        if (empty($where)) return;
        $newWhere = array();
        foreach ($where as $key => $value) {
            $newWhere[] = $key.' = '.$value;
        }
        $conditions = array();
        $conditions[] = $newWhere[0];
        if (count($newWhere) > 1) {
            unset($newWhere[0]);
            foreach ($newWhere as $key => $value) {
                $conditions[] = "\nAND ".$value;
            }
        }
        return $conditions;
    }

    /**
     * Parse array and escape
     * 
     * @param array $data values
     * 
     * @return array escaped data
     */
    protected function doArrayEscape($data)
    {
        $newData = array();
        foreach ($data as $key => $value) {
            $newData[$key] = $this->escape($value);
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
        $this->startQueryTimer = microtime(true);
        $this->lastSql = $this->sprintf($sql, $fields);

        $this->Stmt = $this->connection->prepare($this->lastSql, $options);
        $this->prepQueries[] = $this->lastSql;  // Save the  query for debugging
        $this->prepare = true;
        ++$this->queryCount;
        return $this;
    }

    /**
     * Prepared or Direct Pdo Query
     * 
     * @param string $sql    query
     * @param array  $fields array fields
     * 
     * @return object pdo
     */
    public function query($sql, $fields = array())
    {
        $this->lastSql = $this->sprintf($sql, $fields);

        $start = microtime(true);
        $this->Stmt = $this->connection->query($this->lastSql);
        $time = microtime(true) - $start;
        ++$this->queryCount;
        if ($this->config['log']['extra']['queries']) {
            $this->logger->debug(
                '$_SQL '.$this->queryCount.' ( Query ):', 
                array(
                    'time' => number_format($time, 4), 
                    'output' => trim(preg_replace('/\n/', ' ', $this->lastSql), "\n")
                ), ($this->queryCount * -1 )
            );
        }
        return ($this);
    }

    /**
     * Protect array values
     * 
     * @param string $sql   sql strings
     * @param array  $array protect values
     * 
     * @return array rendered array
     */
    public function sprintf($sql, $array)
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
     * @return  object PDO::Statement
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
     * @access    public
     * @return    void
     */
    public function reconnect()
    {
        $this->connect();
    }

    /**
     * Get Database Version number.
     *
     * @access    public
     * @return    string
     */
    public function getVersion()
    {
        $this->connect();
        try {
            $version = $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (PDOException $e) {  // don't show excepiton
            return null; // If the driver doesn't support getting attributes
        }
        $matches = null;
        if (preg_match('/((?:[0-9]{1,2}\.){1,3}[0-9]{1,2})/', $version, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
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
        $this->assignObjects();
        $this->__wakeup();
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
    public function bind($val)
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
        $this->__wakeup();
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
        $this->__wakeup();        
        return $this->connection->rollBack();
    }

    /**
     * Sleep
     * 
     * @return array
     */
    public function __sleep()
    {
        return array(
            'host',
            'username',
            'password',
            'database',
            'driver',
            'charset',
            'port',
            'dsn',
            'options');
    }

    /**
     * Wake up
     * 
     * @return void
     */
    public function __wakeup()
    {
        $this->connect();
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
     * @return  object PDO::Statement
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
        $this->Stmt->bindParam($param, $val, $type, $length, $options);
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
        $this->Stmt->bindValue($param, $val, $type);
        $this->useBindValues = true;
        $this->lastBindValues[$param] = $val;
        return $this;
    }

    /**
     * "Smart" Escape String via PDO
     *
     * Escapes data based on type
     * Sets boolean and null types
     *
     * @param string $str escape value
     * 
     * @return mixed
     */
    public function escape($str)
    {
        if (is_string($str)) {
            return $this->escapeStr($str);
        }
        if (is_integer($str)) {
            return (int)$str;
        }
        if (is_double($str)) {
            return (double)$str;
        }
        if (is_float($str)) {
            return (float)$str;
        }
        if (is_bool($str)) {
            return (bool)$str;
        }
        if (is_null($str)) {
            return null;
        }
        return null;
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
        return $this->escapeStr($str, true, $side);
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
        $this->Stmt->execute($array);
        $time = microtime(true) - $this->startQueryTimer;

        if ($this->config['log']['extra']['queries'] AND isset($this->prepQueries[0])) {
            $this->logger->debug(
                '$_SQL '.$this->queryCount.' ( Execute ):', 
                array('time' => number_format($time, 4), 
                'output' => trim(preg_replace('/\n/', ' ', end($this->prepQueries)), "\n")), 
                ($this->queryCount * -1 )
            );
        }
        $this->prepare = false;   // reset prepare variable and prevent collision with next query ..
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
        return $this->Stmt;
    }

    /**
     * exec used just for CREATE, DELETE, INSERT and
     * UPDATE operations it returns to
     * number of [affected rows] after the write
     * operations.
     *
     * @param string $sql    query sql
     * @param array  $fields array fields
     * 
     * @return boolean
     */
    public function exec($sql, $fields = array())
    {
        $this->lastSql = $this->sprintf($sql, $fields);

        $start = microtime(true);
        $affected_rows = $this->connection->exec($this->lastSql);
        $time  = microtime(true) - $start;
        ++$this->queryCount;

        if ($this->config['log']['extra']['queries']) {
            $this->logger->debug(
                '$_SQL '.$this->queryCount.' ( Exec ):', 
                array('time' => number_format($time, 4), 
                'output' => trim(preg_replace('/\n/', ' ', $this->lastSql), "\n")), 
                ($this->queryCount * -1 )
            );
        }
        return $affected_rows;
    }

    /**
     * Returns number of rows.
     *
     * @return integer
     */
    public function count()
    {
        return $this->Stmt->rowCount();
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
        $result = $this->Stmt->fetch(PDO::FETCH_OBJ);
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
        $result = $this->Stmt->fetch(PDO::FETCH_ASSOC);
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
        $result = $this->Stmt->fetchAll(PDO::FETCH_OBJ);
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
        $result = $this->Stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($result === false AND $failArray == true) {
            return array();
        }
        return $result;
    }

    /**
     * Get the pdo statement object and use native pdo functions.
     *
     *  $stmt = $this->db->getStatement();
     *  $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
     * 
     * @return object
     */
    public function getStatement()
    {
        return $this->Stmt;
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
     * Assign all controller objects
     * into db class
     * to callback closure $this->object support.
     * 
     * @return void
     */
    public function assignObjects()
    {
        foreach (get_object_vars(Controller::$instance) as $k => $v) {  // Get object variables
            if (is_object($v)) { // Do not assign again reserved variables
                $this->{$k} = Controller::$instance->{$k};
            }
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