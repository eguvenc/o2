<?php

namespace Obullo\Log;

use SplPriorityQueue;

/**
 * PriorityQueue Class
 * 
 * @category  Log
 * @package   PriorityQueue
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class PriorityQueue extends SplPriorityQueue 
{
    /**
     * Queue order
     * 
     * @var integer
     */
    protected $queueOrder = -999;

    /**
     * Priority fix
     * 
     * @param integer $priority1 priority level
     * @param integer $priority2 priority2 level
     *
     * @return integer
     */
    public function compare($priority1, $priority2) 
    { 
        if ($priority1 == null) {
            return null;
        }
        if ($priority1 === $priority2) {
            return 0;
        }
        return $priority1 < $priority2 ? -1 : 1; 
    }

    /**
     * Add to queue
     * 
     * @param string $data     data
     * @param mixed  $priority priority
     * 
     * @return void
     */
    public function insert($data, $priority)
    {
        if (is_null($priority)) {                  // SplPriorityQueue Fix
            $priority = $this->queueOrder--;       // https://mwop.net/blog/253-Taming-SplPriorityQueue.html
        }
        parent::insert($data, $priority);
    }

}

// END PriorityQueue class
/* End of file PriorityQueue.php */

/* Location: .Obullo/Log/PriorityQueue.php */