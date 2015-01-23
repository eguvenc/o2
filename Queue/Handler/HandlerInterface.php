<?php

namespace Obullo\Queue\Handler;

/**
 * Queue Handler Interface
 * 
 * @category  Queue
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 */
interface HandlerInterface
{
    /**
     * Create exchange object if not exists otherwise returns to instance of it
     * 
     * @param string $name exchange
     * @param mixed  $type type 
     * @param mixed  $flag flag
     * 
     * @return object exchange
     */
    public function channel($name, $type = null, $flag = null);

    /**
     * Push a new job onto the queue.
     *
     * @param string $queueName queue name ( Routing Key )
     * @param string $job       name
     * @param mixed  $data      payload
     * @param mixed  $delay     dateTime or int push job onto the queue after a delay. 
     *
     * @return boolean
     */
    public function push($queueName, $job, $data, $delay = 0);

    /**
     * Pop the next job off of the queue.
     *
     * @param string $queueName queue name ( Routing Key )
     *
     * @return mixed job handler object or null
     */
    public function pop($queueName = null);
    
}

// END HandlerInterface class

/* End of file HandlerInterface.php */
/* Location: .Obullo/Queue/Handler/HandlerInterface.php */