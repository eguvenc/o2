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
     * Escape string
     * 
     * @param string $str string
     * 
     * @return string
     */
    public function _escape($str);

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