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
     * @return array|null
     */
    public function in(array $record)
    {
        if (empty($record)) {
            return array();
        }
        $priority = $this->logger->getPriority($record['level']);

        if (in_array($priority, $this->params)) {
            return $record;
        }
        return;
    }

    /**
     * Filter "not" in array
     * 
     * @param array $record unformatted record data
     * 
     * @return array|null
     */
    public function notIn(array $record)
    {
        if (empty($record)) {
            return array();
        }
        $priority = $this->logger->getPriority($record['level']);

        if (! in_array($priority, $this->params)) {
            return $record;
        }
        return;
    }
}