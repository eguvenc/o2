<?php

namespace Obullo\Queue\Failed;

/**
 * Failed Job Class
 * 
 * @category  Queue
 * @package   Failed
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 */
abstract class FailedJob
{
    /**
     * Get database connection
     * 
     * @return object
     */
    public function getConnection()
    {
        return $this->db;
    }

    /**
     * Returns to tablename in service parameters
     * 
     * @return string
     */
    public function getTablename()
    {
        return $this->tablename;
    }

    /**
     * Save failed event
     * 
     * @param array $event job event
     * 
     * @return object connection
     */
    abstract function save(array $event);

}