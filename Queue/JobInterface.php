<?php

namespace Obullo\Queue;

/**
 * Job Interface
 * 
 * @category  Queue
 * @package   JobInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/queue
 */
interface JobInterface
{
    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c);

    /**
     * Fire the job
     * 
     * @param object $job  class
     * @param array  $data payload
     * 
     * @return void
     */
    public function fire(Job $job, $data);
}

// END JobInterface class

/* End of file JobInterface.php */
/* Location: .Obullo/Queue/JobInterface.php */