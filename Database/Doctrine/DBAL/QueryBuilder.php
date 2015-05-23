<?php

namespace Obullo\Database\Doctrine\DBAL;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;

/**
 * Handle for Doctrine QueryBuilder
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
     * Run execute methods, set table using from 
     * if tablename not null
     * 
     * @param string $table name
     * 
     * @return void
     */
    public function get($table = null)
    {
        if ($table != null) {
            $this->from($table);
        }
        $this->execute();
    }

    /**
     * Call connection methods
     *
     * This method allows to you reach database connection methods
     * 
     * Example :
     * 
     * $this->db->query("..");
     * 
     * @param string $method    name
     * @param array  $arguments method arguments
     * 
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->getConnection(), $method), $arguments);
    }
}

// END QueryBuilder Class
/* End of file QueryBuilder.php

/* Location: .Obullo/Database/Doctrine/QueryBuilder.php */