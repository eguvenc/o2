<?php

namespace Obullo\Queue;

use Obullo\Container\Container;

/**
 * Job Interface
 * 
 * @category  Queue
 * @package   JobInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 */
interface JobInterface
{
    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c);

    /**
     * Fire the job
     * 
     * @param object $job  class \\Obullo\Queue\Job class
     * @param array  $data payload
     * 
     * @return void
     */
    public function fire($job, $data);
}

// END JobInterface class

/* End of file JobInterface.php */
/* Location: .Obullo/Queue/JobInterface.php */