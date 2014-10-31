<?php

namespace Obullo\QueueLogger\JobHandler;

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
     * Config Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct($c, array $params = array());

    /**
     * Hanlder writer
     *
     * @param array $data data
     * 
     * @return boolean
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
/* Location: .Obullo/QueueLogger/JobHandler/JobHandlerInterface.php */