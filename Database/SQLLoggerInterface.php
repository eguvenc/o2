<?php

namespace Obullo\Database;

/**
 * Interface SqlLogger
 * 
 * @category  Database
 * @package   SQLLogger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
interface SQLLoggerInterface
{
    /**
     * Logs a SQL statement somewhere.
     *
     * @param string     $sql    The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null);

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery();
}


// END SQLLoggerInterface Class
/* End of file SQLLoggerInterface.php

/* Location: .Obullo/Database/SQLLoggerInterface.php */