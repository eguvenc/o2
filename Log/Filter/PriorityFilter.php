<?php

namespace Obullo\Log\Filter;

use Obullo\Log\Logger;

/**
 * PriorityFilter Class
 * 
 * @category  Log
 * @package   PriorityFilter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class PriorityFilter
{
    /**
     * Logger class
     * 
     * @var object
     */
    protected $logger;

    /**
     * Parameters
     * 
     * @var array
     */
    protected $params;

    /**
     * Constructor
     * 
     * @param array  $params parameters
     * @param object $logger Logger
     */
    public function __construct(array $params, Logger $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
    }

    /**
     * Filter in array
     * 
     * @param array $record unformatted record data
     * 
     * @return array
     */
    public function in(array $record)
    {
        if (empty($record)) {
            return array();
        }
        $priority = $this->getPriority($record['level']);

        if (in_array($priority, $this->params)) {
            return $record;
        }
        return array();  // To remove record return to empty array.
    }

    /**
     * Filter "not" in array
     * 
     * @param array $record unformatted record data
     * 
     * @return array
     */
    public function notIn(array $record)
    {
        if (empty($record)) {
            return array();
        }
        $priority = $this->getPriority($record['level']);

        if (! in_array($priority, $this->params)) {
            return $record;
        }
        return array();  // To remove record return to empty array.
    }

    /**
     * Get priority
     *
     * @param string $level current level
     * 
     * @return void
     */
    protected function getPriority($level)
    {
        $priorities = $this->logger->getPriorities();
        return $priorities[$level];
    }
    
}