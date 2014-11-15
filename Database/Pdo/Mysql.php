<?php

namespace Obullo\Database\Pdo;

use PDO;

/**
 * Pdo Mysql Driver
 * 
 * @category  Database
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/database
 */
Class Mysql extends Adapter
{
    /**
     * The character used for escaping
     *
     * @var string
     */
    public $escapeChar = '`';

    /**
     * Clause and character used for LIKE escape sequences - not used in MySQL
     * 
     * @var string
     */
    public $likeEscapeStr = '';
    /**
     * Clause and character used for LIKE escape sequences - not used in MySQL
     * @var string
     */
    public $likeEscapeChr = '';

    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params connection parameters
     */
    public function __construct($c, $params)
    {
        parent::__construct($c, $params);
        $this->connect();
    }

    /**
     * Connect to pdo
     * 
     * @return void
     */
    public function connect()
    {
        if ($this->connection) { // If connection is ok .. not need to again connect..
            return;
        }
        $port = empty($this->port) ? '' : ';port=' . $this->port;
        $dsn  = empty($this->dsn) ? 'mysql:host=' . $this->host . $port . ';dbname=' . $this->database : $this->dsn;

        if ($this->autoinit['bufferedQuery'] AND defined('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) { // Automatically use buffered queries.
            $this->options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
        }
        if ($this->autoinit['charset']) {
            $this->options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES $this->charset";
        }   
        $this->pdoObject = $this->pdoConnect($dsn, $this->username, $this->password, $this->options);

        // We set exception attribute for always showing the pdo exceptions errors.
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // PDO::ERRMODE_SILENT
    }

    /**
     * Protect identifiers using your escape character.
     * 
     * Escape character able to set using $this->setEscapeChar()
     * method.
     * 
     * @param string $identifier identifier
     * 
     * @return string
     */
    public function protect($identifier)
    {
        return $this->escapeChar . $identifier . $this->escapeChar;
    }

    /**
     * Escape the SQL Identifiers
     *
     * This function escapes column and table names
     * 
     * @param string $item item
     * 
     * @return string
     */
    public function _escapeIdentifiers($item)
    {
        if ($this->escapeChar == '') {
            return $item;
        }
        foreach ($this->reservedIdentifiers as $id) {
            if (strpos($item, '.' . $id) !== false) {
                $str = $this->escapeChar . str_replace('.', $this->escapeChar . '.', $item);
                // remove duplicates if the user already included the escape
                return preg_replace('/[' . $this->escapeChar . ']+/', $this->escapeChar, $str);
            }
        }
        if (strpos($item, '.') !== false) {
            $str = $this->escapeChar . str_replace('.', $this->escapeChar . '.' . $this->escapeChar, $item) . $this->escapeChar;
        } else {
            $str = $this->escapeChar . $item . $this->escapeChar;
        }
        // remove duplicates if the user already included the escape
        return preg_replace('/[' . $this->escapeChar . ']+/', $this->escapeChar, $str);
    }
    
    /**
     * Escape string
     * 
     * @param string  $str  string
     * @param boolean $like whether or not the string will be used in a LIKE condition
     * @param string  $side direction
     * 
     * @return string
     */
    public function escapeStr($str, $like = false, $side = 'both')
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->escapeStr($val, $like);
            }
            return $str;
        }
        if ($like === true) {         // escape LIKE condition wildcards
            $str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);

            switch ($side) {
            case 'before':
                $str = "%{$str}";
                break;

            case 'after':
                $str = "{$str}%";
                break;

            default:
                $str = "%{$str}%";
            }
        }
        if ($this->prepare === false) {          // make sure is it bind value, if not ...
            $str = $this->quote($str, PDO::PARAM_STR);
        }
        return $str;
    }

    /**
     * Platform specific pdo quote function.
     * 
     * @param string $str  string
     * @param mixed  $type type
     * 
     * @return string
     */
    public function quote($str, $type = null)
    {
        return $this->connection->quote($str, $type);
    }

    /**
     * Builds insert / update values
     * 
     * @param array $data values array
     * 
     * @return array
     */
    public function buildValues(array $data)
    {
        $values = array();
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $values[$key] = 'NULL';
            } elseif (is_bool($value)) {
                $values[$key] = ($value) ? 'TRUE' : 'FALSE';
            } else {
                $values[$key] = $value;
            }
        }
        return $values;
    }

    /**
     * From Tables
     *
     * This function implicitly groups FROM tables so there is no confusion
     * about operator precedence in harmony with SQL standards
     * 
     * @param array $tables values
     * 
     * @return string
     */
    public function _fromTables($tables)
    {
        if ( ! is_array($tables)) {
            $tables = array($tables);
        }
        return '(' . implode(', ', $tables) . ')';
    }

    /**
     * Insert statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @param string $table  the table name
     * @param array  $keys   the insert keys
     * @param array  $values the insert values
     * 
     * @return   string
     */
    public function _insert($table, $keys, $values)
    {
        $values = $this->buildValues($values);
        return "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

    /**
     * Replace statement
     *
     * Generates a platform-specific replace string from the supplied data
     *
     * @param string $table  the table name
     * @param array  $keys   the insert keys
     * @param array  $values the insert values
     * 
     * @return  string
     */
    public function _replace($table, $keys, $values)
    {
        $values = $this->buildValues($values);
        return "REPLACE INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

    /**
     * Update statement
     *
     * Generates a platform-specific update string from the supplied data
     *
     * @param string $table    the table name
     * @param array  $values   the update data
     * @param array  $where    the where clause
     * @param array  $orderby  the orderby clause
     * @param int    $limit    the limit clause
     * @param int    $extraSql add extra sql end of your query
     * 
     * @return string
     */
    public function _update($table, $values, $where, $orderby = array(), $limit = false, $extraSql = '')
    {
        $values = $this->buildValues($values);
        foreach ($values as $key => $val) {
            $valstr[] = $key . " = " . $val;
        }
        $limit = ( ! $limit) ? '' : ' LIMIT ' . $limit;
        $orderby = (count($orderby) >= 1) ? ' ORDER BY ' . implode(", ", $orderby) : '';

        $sql = "UPDATE " . $table . " SET " . implode(', ', $valstr);
        $sql .= ($where != '' AND count($where) >= 1) ? " WHERE " . implode(" ", $where) : '';
        $sql .= $orderby . $limit .' '.trim($extraSql);
        return trim($sql);
    }

    /**
     * Delete statement
     *
     * Generates a platform-specific delete string from the supplied data
     *
     * @param string $table    the table name
     * @param array  $where    the where clause
     * @param string $like     the like clause
     * @param string $limit    the limit clause
     * @param string $extraSql add extra sql end of your query
     * 
     * @return string
     */
    public function _delete($table, $where = array(), $like = array(), $limit = false, $extraSql = '')
    {
        $conditions = '';
        if (count($where) > 0 OR count($like) > 0) {
            $conditions = "\nWHERE ";
            $conditions .= implode("\n", $where);
            if (count($where) > 0 AND count($like) > 0) {  // Put and for like
                $conditions .= " AND ";
            }
            $conditions .= implode("\n", $like);
        }
        $limit = ( ! $limit) ? '' : ' LIMIT ' . $limit;
        $sql = "DELETE FROM " . $table . $conditions . $limit .' '.trim($extraSql);
        return trim($sql);
    }

    /**
     * Limit string
     * Generates a platform-specific LIMIT clause
     * 
     * @param string  $sql    query
     * @param integer $limit  number limit
     * @param integer $offset number offset
     * 
     * @return string
     */
    public function _limit($sql, $limit, $offset)
    {
        if ($offset == 0) {
            $offset = '';
        } else {
            $offset .= ", ";
        }
        return $sql . "LIMIT " . $offset . $limit;
    }

}

// END Mysql Class
/* End of file Mysql.php

/* Location: .Obullo/Database/Pdo/Mysql.php */