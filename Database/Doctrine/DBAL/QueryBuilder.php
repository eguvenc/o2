<?php

namespace Obullo\Database\Doctrine\DBAL;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;

/**
 * QueryBuilder Layer for Doctrine
 * 
 * @category  Database
 * @package   QueryBuilder
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
class QueryBuilder extends DoctrineQueryBuilder
{
    /**
     * Initializes a new QueryBuilder.
     *
     * @param \Doctrine\DBAL\Connection $connection The DBAL Connection.
     */
    public function __construct(Adapter $connection)
    {
        parent::__construct($connection);
    }

    /**
     * Get sql query & execute
     *
     * @param string                                      $table name
     * @param array                                       $types The types the previous parameters are in.
     * @param \Doctrine\DBAL\Cache\QueryCacheProfile|null $qcp   The query cache profile, optional.
     * 
     * @return object connection
     */
    public function get($table = null, $types = array(), QueryCacheProfile $qcp = null)
    {
        if ($table != null) {
            $this->from($table);
        }
        return $this->connection->executeQuery($this->getSQL(), $this->getParameters(), $types, $qcp);
    }

    /**
     * Execute query builder insert, update, replace, delete ( write )
     * operations.
     * 
     * @return object connection
     */
    public function exec()
    {
        return $this->connection;
    }

    /**
     * Call adapter methods
     * 
     * @param string $method    name
     * @param array  $arguments method arguments
     * 
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->connection, $method), $arguments);
    }

}