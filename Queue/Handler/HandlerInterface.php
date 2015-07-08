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
     * @param string $job     name
     * @param string $route   queue name ( route key )
     * @param mixed  $data    payload
     * @param array  $options delivery options
     *
     * @return boolean
     */
    public function push($job, $route, $data, $options = array());

    /**
     * Push a new job onto delayed queue.
     *
     * @param int    $delay   date
     * @param string $job     name
     * @param string $route   queue name ( Routing Key )
     * @param mixed  $data    payload
     * @param array  $options delivery options
     * 
     * @return boolean
     */
    public function later($delay, $job, $route, $data, $options = array());

    /**
     * Pop the next job off of the queue.
     *
     * @param string $route queue name ( Routing Key )
     *
     * @return mixed job handler object or null
     */
    public function pop($route = null);
    
}

// END HandlerInterface class

/* End of file HandlerInterface.php */
/* Location: .Obullo/Queue/Handler/HandlerInterface.php */