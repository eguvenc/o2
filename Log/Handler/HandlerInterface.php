<?php

namespace Obullo\Log\Handler;

use Obullo\Log\PriorityQueue;

/**
 * Logger Handler Interface
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
interface HandlerInterface
{
    /**
    * Format log records and build lines
    *
    * @param string $dateFormat        log record
    * @param array  $unformattedRecord log record
    * 
    * @return array formatted record
    */
    public function format($dateFormat, $unformattedRecord);

    /**
     * Write processor output
     *
     * @param object $pQ priorityQueue object
     * 
     * @return boolean
     */
    public function write(PriorityQueue $pQ);

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close();
}

// END HandlerInterface class

/* End of file HandlerInterface.php */
/* Location: .Obullo/Log/Handler/HandlerInterface.php */