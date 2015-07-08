<?php

namespace Obullo\Log\Filters;

use Obullo\Container\ContainerInterface;

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
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Parameters
     * 
     * @var array
     */
    public $params;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct(ContainerInterface $c, array $params)
    {
        $this->c = $c;
        $this->params = $params;
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
        $priorities = $this->c['logger']->getPriorities();
        
        $priority = $priorities[$record['level']];
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
        $priorities = $this->c['logger']->getPriorities();

        $priority = $priorities[$record['level']];
        if (! in_array($priority, $this->params)) {
            return $record;
        }
        return array();  // To remove record return to empty array.
    }
    
}