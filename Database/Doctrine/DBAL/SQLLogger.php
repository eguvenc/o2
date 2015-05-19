<?php

namespace Obullo\Database\Doctrine\DBAL;

use PDO;
use Obullo\Log\Logger;
use Doctrine\DBAL\Logging\SQLLogger as SQLLoggerInterface;

/**
 * Doctrine SQLLogger for Obullo
 * 
 * @category  Database
 * @package   SQLLogger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
class SQLLogger implements SQLLoggerInterface
{
    /**
     * Sql
     * 
     * @var string
     */
    protected $sql;

    /**
     * Query timer start value
     * 
     * @var int
     */
    protected $start;

    /**
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Count number of queries
     * 
     * @var integer
     */
    protected $queryNumber = 0;

    /**
     * Create pdo statement object
     * 
     * @param \Obullo\Log\Logger $logger object
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Begin sql query timer
     * 
     * @return void
     */
    protected function beginTimer()
    {
        $this->start = microtime(true);
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string     $sql    The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->beginTimer();
        $this->sql = $sql;
        ++$this->queryNumber;
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        $this->logger->debug(
            '$_SQL '.$this->queryNumber.' ( Query ):', 
            [
                'time' => number_format(microtime(true) - $this->start, 4),
                'output' => static::formatSql($this->sql)
            ],
            ($this->queryNumber * -1)  // priority
        );
    }

    /**
     * Return to last sql query string
     *
     * @param string $sql sql
     * 
     * @return void
     */
    public static function formatSql($sql)
    {
        $sql = preg_replace('/\n\r\t/', ' ', $sql);
        return trim($sql, "\n");
    }

}

// END SQLLogger Class
/* End of file SQLLogger.php

/* Location: .Obullo/Database/Doctrine/SQLLogger.php */