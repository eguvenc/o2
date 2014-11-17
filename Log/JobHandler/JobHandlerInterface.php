<?php

namespace Obullo\Log\JobHandler;

/**
 * JobHandler Interface
 * 
 * @category  Log
 * @package   JobHandler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
interface JobHandlerInterface
{
    /**
    * Format log records and build lines
    *
    * @param string $timestamp         unix time
    * @param array  $unformattedRecord log data
    * 
    * @return array formatted record
    */
    public function format($timestamp, $unformattedRecord);

    /**
     * Hanlder writer
     *
     * @param array $data data
     * 
     * @return void
     */
    public function write(array $data);

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close();
}

// END JobHandlerInterface class

/* End of file JobHandlerInterface.php */
/* Location: .Obullo/Log/JobHandler/JobHandlerInterface.php */