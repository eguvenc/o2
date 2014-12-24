<?php

namespace Obullo\Database\Pdo\Handler;

/**
 * Database Handler Interface
 * 
 * @category  Database
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
interface HandlerInterface
{
    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params connection parameters
     */
    public function __construct($c, $params = array());

    /**
     * Connect to pdo
     * 
     * @return void
     */
    public function connect();

    /**
     * Protect identifiers using your escape character.
     * 
     * @param string $identifier identifier
     * 
     * @return string
     */
    public function protect($identifier);

    /**
     * Escape the SQL Identifiers
     *
     * This function escapes column and table names
     * 
     * @param string $item item
     * 
     * @return string
     */
    public function _escapeIdentifiers($item);

    /**
     * Escape string
     * 
     * @param string  $str  string
     * @param boolean $like whether or not the string will be used in a LIKE condition
     * @param string  $side direction
     * 
     * @return string
     */
    public function escapeStr($str, $like = false, $side = 'both');

    /**
     * Platform specific pdo quote function.
     * 
     * @param string $str  string
     * @param mixed  $type type
     * 
     * @return string
     */
    public function quote($str, $type = null);

    /**
     * Builds insert / update values
     * 
     * @param array $data values array
     * 
     * @return array
     */
    public function buildValues(array $data);

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
    public function _fromTables($tables);

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
    public function _insert($table, $keys, $values);

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
    public function _replace($table, $keys, $values);

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
    public function _update($table, $values, $where, $orderby = array(), $limit = false, $extraSql = '');

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
    public function _delete($table, $where = array(), $like = array(), $limit = false, $extraSql = '');

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
    public function _limit($sql, $limit, $offset);

}

// END HandlerInterface class

/* End of file HandlerInterface.php */
/* Location: .Obullo/Database/Pdo/Handler/HandlerInterface.php */