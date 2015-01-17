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
     * @param string $str string
     * 
     * @return string
     */
    public function _escape($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->_escape($val);
            }
            return $str;
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
                $values[$key] = $value;  // We already do escape in Adapter class not need to escape
            }
        }
        return $values;
    }

    public function prepFields($fields)
    {

    }

    /**
     * Preparation of sql for write statements
     * 
     * @param array $args query arguments
     * 
     * @return string
     */
    public function _prepSqlQuery($args)
    {
        $sql = $args[0];
        $sprintf = isset($args[1]) ? $args[1] : null;
        $exp = explode(' ', $sql);
        $operator = $exp[0];
        $values = array();

            print_r($sprintf); exit;
        if ($sprintf != null) {
            $sprintf = $this->resolveModifiers($sprintf);  // parse : $insert, $update, $in, $multi, $columns, $values
        }

        switch ($operator) {

        case ($operator == 'SELECT'):
            if (isset($args[2]) AND is_array($args[2])) {   // bind values
                $values = $args[2];
            }
            $sql = $this->sprintf($sql, $sprintf);
            break;

        // case ($operator == 'INSERT' || $operator == 'REPLACE '):
        //     if (isset($args[3]) AND is_array($args[3])) {   // bind values
        //         $values = $args[3];
        //     }
        //     if (isset($args[1]) AND in_array('$multiple', $args[1])) {  //  Multiple operations
        //         $data = $this->buildValues($args[2]);
        //         $escapedValues = $this->escapeArray($data);
        //         $sqlString = " (" . implode(', ', array_keys($data)) . ") VALUES (" . implode(', ', array_values($escapedValues)) . ") ";
        //         // exit('multiple');
        //         // multiple insert
        //         // $prepColumns = $this->prepFields($args[2]);
        //         // ....
        //         // ....
        //     } else {
        //         $data = $this->buildValues($args[2]);
        //         $escapedValues = $this->escapeArray($data);
        //         $sqlString = " (" . implode(', ', array_keys($data)) . ") VALUES (" . implode(', ', array_values($escapedValues)) . ") ";
        //     }
        //     $sql = $this->sprintf($sql, $sprintf);

        //     if (isset($args[2]) AND is_array($args[2])) {   // developer may don't want to send third parameter using "false".
        //         $sql = str_replace('$'.strtolower($operator), $sqlString, $sql);
        //         $sql = str_replace('$multiple', $sqlString, $sql);
        //     }
        //     break;

        // case ($operator == 'UPDATE'):
        //     $values = isset($args[3]) ? $args[3] : $values;  // bind values
        //     $sql = $this->sprintf($sql, $sprintf);

        //     if (isset($args[2]) AND is_array($args[2])) {  // developer may don't want to send third parameter using "false".
        //         $sqlString = '';
        //         foreach ($args[2] as $key => $value) {
        //             $sqlString .= $key."=".$this->escape($value).',';
        //         }
        //         $sql = str_replace('$update', rtrim($sqlString, ','), $sql);
        //     }
        //     break;

        default:
            break;
        }
        return array('sql' => trim($sql), 'values' => $values);
    }

    /**
     * Resovle framework modifiers 
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
                case '$in':
                    $array[$key] = rtrim(implode(',', $this->escapeArray($value['$in'])), ',');
                    break;
                case '$col':
                    $array[$key] = rtrim(implode(',', $value['$col']), ',');
                    break;
                case '$val':
                    $array[$key] = rtrim(implode(',', $this->escapeArray($value['$val'])), ',');
                    break;
                case '$and':
                    $AND = '';
                    $i = 0;
                    foreach ($value['$and'] as $key => $value) {
                        $AND .= $key.' = '.$this->escape($value).' AND ';
                        ++$i;
                    }
                    $val = ($i > 1) ? substr($AND, 0, -4) : $AND;
                    $array[$key] = trim($val);
                    break;
                case '$or':
                    $OR = '';
                    $i = 0;
                    foreach ($value['$or'] as $key => $value) {
                        $OR .= $key.' = '.$this->escape($value).' OR ';
                        ++$i;
                    }
                    $val = substr($OR, 0, -3);
                    $array[$key] = trim($val);
                    break;
                }
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }

}

// END Mysql Class
/* End of file Mysql.php

/* Location: .Obullo/Database/Pdo/Handler/Mysql.php */