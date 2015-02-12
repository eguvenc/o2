<?php

namespace Obullo\Log\Queue;

use Obullo\Log\PriorityQueue;

/**
 * QueueHandlerPriority Class - Manages queue handler priorities 
 * 
 * @category  Log
 * @package   Log
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Class QueueHandlerPriority
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->priorityQueue = new PriorityQueue;
    }

    /**
     * Insert data to priority queue
     * 
     * @param array $data job data
     * 
     * @return void
     */
    public function insert(array $data)
    {
        unset($data['logger']);
        array_walk(
            $data, 
            function (&$value) {
                $this->priorityQueue->insert($value, $value['priority']);
            }
        );
    }

    /**
     * Get queue data
     * 
     * @return array
     */
    public function getQueue()
    {
        $this->priorityQueue->setExtractFlags(PriorityQueue::EXTR_DATA); // Queue mode of extraction
        $records = array();
        if ($this->priorityQueue->count() > 0) {
            $this->priorityQueue->top();  // Go to Top
            $i = 0;
            while ($this->priorityQueue->valid()) {         // Prepare Lines
                $records[$i] = $this->priorityQueue->current();
                $this->priorityQueue->next();
                ++$i;
            }
        }
        return $records;
    }
}

/*
Array
(
    [0] => Array
        (
            [request] => http
            [handler] => file
            [priority] => 5
            [time] => 1416220274
            [record] => Array
                (
                    [0] => Array
                        (
                            [channel] => system
                            [level] => debug
                            [message] => View Class Initialized
                            [context] => Array
                                (
                                )
                    [1] => Array
                        (
                            [channel] => system
                            ...
                        )



        )
)
*/

// END QueueHandlerPriority class
/* End of file QueueHandlerPriority.php */

/* Location: .Obullo/Log/Queue/QueueHandlerPriority.php */