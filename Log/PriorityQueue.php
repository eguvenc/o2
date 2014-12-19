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
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/log
 */
Class PriorityQueue extends SplPriorityQueue 
{
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
        if ($priority1 === $priority2) {
            return 0;
        }
        return $priority1 < $priority2 ? -1 : 1; 
    }

}

// END PriorityQueue class
/* End of file PriorityQueue.php */

/* Location: .Obullo/Log/PriorityQueue.php */