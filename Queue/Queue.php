<?php 

namespace Obullo\Queue;

use Obullo\Container\Container;

/**
 * Abstract Queue Class
 * 
 * @category  Queue
 * @package   Queue
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 * @see       http://www.php.net/manual/pl/book.amqp.php
 */
Abstract class Queue
{
    /**
     * Constructor
     *
     * @param object $c container
     */
    abstract function __construct(Container $c);

    /**
     * Create exchange if not exists
     * 
     * @param object $name exhange name
     * @param object $type available types AMQP_EX_TYPE_DIRECT, AMQP_EX_TYPE_FANOUT, AMQP_EX_TYPE_HEADER or AMQP_EX_TYPE_TOPIC,
     * @param object $flag available flags AMQP_DURABLE, AMQP_PASSIVE
     *
     * @return object AMQPExchange
     */
    abstract public function channel($name, $type = null, $flag = null);

    /**
     * Push a new job onto the queue.
     *
     * @param string $job       name
     * @param string $queueName queue name ( Routing Key )
     * @param mixed  $data      payload
     * @param mixed  $delay     dateTime or int push job onto the queue after a delay.
     * @param array  $options   delivery options
     *
     * @link(Set Delivery Mode, http://stackoverflow.com/questions/6882995/setting-delivery-mode-for-amqp-rabbitmq)
     * @throws AMQPException
     * @return bool
     */
    abstract public function push($job, $queueName, $data, $delay = 0, $options = array());

    /**
     * Pop the next job off of the queue.
     *
     * @param string $queueName queue name ( Routing Key )
     *
     * @return mixed job handler object or null
     */
    abstract public function pop($queueName = null);

    /**
     * Clear the contents of a queue
     * 
     * @param string $name queue name
     * 
     * @return void
     */
    abstract public function purgeQueue($name);

    /**
     * Delete a queue and its contents.
     *
     * @param string $name queue name
     * 
     * @return void
     */
    abstract public function deleteQueue($name);

}

// END Queue Class
/* End of file Queue.php

/* Location: .Obullo/Queue/Queue.php */