<?php

namespace Obullo\Database\Pdo\Handler;

use PDO,
    Obullo\Database\Pdo\Adapter;

/**
 * Pdo Mysql Driver
 * 
 * @category  Database
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
Class Mysql extends Adapter implements HandlerInterface
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
     * 
     * @var string
     */
    public $likeEscapeChr = '';

    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params connection parameters
     */
    public function __construct($c, $params = array())
    {
        $c['config']->load('database');
        $default = (isset($params['db'])) ? $params['db'] : $c['config']['database']['default']['database']; 

        parent::__construct($c, $c['config']['database']['key'][$default]);
    }

    /**
     * Connect to pdo
     * 
     * @return void
     */
    public function connect()
    {
        if ($this->connection) { // Lazy loading, If connection is ok .. not need to again connect..
            return $this;
        }
        $port = empty($this->port) ? '' : ';port=' . $this->port;
        $dsn  = empty($this->dsn) ? 'mysql:host=' . $this->host . $port . ';dbname=' . $this->database : $this->dsn;

        $this->initialize();
        $this->connection($dsn, $this->username, $this->password, $this->options);

        // We set exception attribute for always showing the pdo exceptions errors.
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // PDO::ERRMODE_SILENT
    }

    /**
     * Initialize to attributes
     * 
     * @return void
     */
    protected function initialize()
    {
        if ($this->autoinit['bufferedQuery'] AND defined('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) { // Automatically use buffered queries.
            $this->options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
        }
        if ($this->autoinit['charset']) {
            $this->options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES $this->charset";
        }   
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
     * Escape string
     * 
     * @param string  $str  string
     * @param boolean $like whether or not the string will be used in a LIKE condition
     * @param string  $side direction
     * 
     * @return string
     */
    public function _escape($str, $like = false, $side = 'both')
    {
        $this->connect();
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->_escape($val);
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
        if ($this->prepare === false AND trim($str) != '?') { // Make sure is it bind value, if not ...
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
     * Preparation of sql statements
     * 
     * @param string $sql     query
     * @param array  $sprintf values
     * @param array  $values  bind values
     *
     * @return array
     */
    public function _prepSqlQuery($sql, $sprintf = null, $values = array())
    {
        $exp = explode(' ', $sql);
        $operator = $exp[0];
        if ($operator != 'SELECT' AND $sprintf != null) {
            $sprintf = $this->resolveModifiers($sprintf);
        }
        return array('sql' => trim($this->sprintf($sql, $sprintf)), 'values' => $values);
    }

    /**
     * Resolve insert, update, delete, replace modifiers 
     * 
     * @param string $sprintf format
     * 
     * @return array
     */
    public function resolveModifiers($sprintf)
    {
        $array = array();
        foreach ($sprintf as $key => $value) {
            if (is_array($value)) {
                $mod = key($value);
                switch ($mod) {
                case ($mod == '@insert' || $mod == '@replace'):
                    $escapedArray = $this->escape($value['@insert']);
                    $array[$key] = " (" . implode(', ', array_keys($escapedArray)) . ") VALUES (" . implode(', ', array_values($escapedArray)) . ") ";
                    break;
                case ($mod == '@update'):
                    $escapedArray = $this->escape($value['@update']);
                    $update = '';
                    foreach ($escapedArray as $key => $value) {
                        $update .= $key.'='.$value.',';
                    }
                    $array[$key] = substr($update, 0, -1);
                    break;
                case ($mod == '@in'):
                    $array[$key] = rtrim(implode(',', $this->escape($value['@in'])), ',');
                    break;
                case ($mod == '@or'):
                    $or = '';
                    foreach ($value['@or'] as $key => $value) {
                        $or.= $key.'='.$this->escape($value).' OR ';
                    }
                    $array[$key] = trim(substr($or, 0, -3));
                    break;
                case ($mod == '@and'):
                    $and = '';
                    foreach ($value['@and'] as $key => $value) {
                        $and.= $key.'='.$this->escape($value).' AND ';
                    }
                    $array[$key] = trim(substr($and, 0, -4));
                    break;
                }

            } else {
                $array[$key] = $value;
            }
        }
        return $array;
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
     * @return string
     */
    public function _insert($table, $keys, $values)
    {
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
     * @return string
     */
    public function _replace($table, $keys, $values)
    {
        return "REPLACE INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

    /**
     * Update statement
     *
     * Generates a platform-specific update string from the supplied data
     *
     * @param string $table   the table name
     * @param array  $values  the update data
     * @param array  $where   the where clause
     * @param array  $orderby the orderby clause
     * @param int    $limit   the limit clause
     * 
     * @return string
     */
    public function _update($table, $values, $where, $orderby = array(), $limit = false)
    {
        foreach ($values as $key => $val) {
            $valstr[] = $key . ' = ' . $val;
        }
        $limit = ( ! $limit) ? '' : ' LIMIT '.$limit;
        $orderby = (count($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';
        $sql = "UPDATE ".$table." SET ".implode(', ', $valstr);
        $sql .= ($where != '' AND count($where) >=1) ? " WHERE ".implode(" ", $where) : '';
        $sql .= $orderby.$limit;
        return $sql;
    }

    /**
     * Delete statement
     *
     * Generates a platform-specific delete string from the supplied data
     *
     * @param string $table the table name
     * @param array  $where the where clause
     * @param string $like  the like clause
     * @param string $limit the limit clause
     * 
     * @return string
     */
    public function _delete($table, $where = array(), $like = array(), $limit = false)
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
        $sql = "DELETE FROM " . $table . $conditions . $limit;
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

/* Location: .Obullo/Database/Pdo/Handler/Mysql.php */