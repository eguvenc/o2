<?php

namespace Obullo\Queue\Handler;

use AMQPQueue;
use AMQPChannel;
use AMQPEnvelope;
use AMQPExchange;
use AMQPException;
use AMQPConnection;
use RuntimeException;
use Obullo\Queue\Queue;
use Obullo\Queue\JobHandler\AMQPJob;
use Obullo\Container\ContainerInterface;

/**
 * AMQP Handler
 * 
 * @category  Queue
 * @package   Queue
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 * @see       http://www.php.net/manual/pl/book.amqp.php
 * @see       http://www.brandonsavage.net/publishing-messages-to-rabbitmq-with-php/
 */
class AMQP extends Queue implements HandlerInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * AMQP channel name
     * 
     * @var string
     */
    protected $channel = null;

    /**
     * AMQP connection instance
     * 
     * @var object
     */
    protected $AMQPconnection;

    /**
     * Store declared channels
     * 
     * @var array
     */
    protected $channels;

    /**
     * Default queue name if its not provided
     * 
     * @var string
     */
    protected $defaultQueueName;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('queue/amqp');
        $this->logger = $this->c['logger'];

        $this->AMQPconnection = $this->c['app']->provider('amqp')->get(['connection' => 'default']);

        $this->channel = new AMQPChannel($this->AMQPconnection);
        $this->defaultQueueName = 'default';
    }

    /**
     * Create AMQPExchange if not exists otherswise get instance of it
     * 
     * @param object $name exhange name
     * @param object $type available types AMQP_EX_TYPE_DIRECT, AMQP_EX_TYPE_FANOUT, AMQP_EX_TYPE_HEADER or AMQP_EX_TYPE_TOPIC,
     * @param object $flag available flags AMQP_DURABLE, AMQP_PASSIVE
     *
     * @return object AMQPExchange
     */
    public function channel($name, $type = null, $flag = null)
    {
        if (isset($this->channels[$name])) {
            $this->exchange = $this->channels[$name];
            return $this->channels[$name];  // Return to instance of it.
        }
        $type = (empty($type)) ? constant($this->config['exchange']['type']) : $type;
        $flag = (empty($flag)) ? constant($this->config['exchange']['flag']) : $flag;

        $this->exchange = new AMQPExchange($this->channel);
        $this->exchange->setName($name);
        $this->exchange->setFlags($flag);
        $this->exchange->setType($type);
        $this->exchange->declareExchange();
        return $this->channels[$name] = $this->exchange; // Register channel.
    }

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
    public function push($job, $queueName, $data, $delay = 0, $options = array())
    {
        if (empty($this->channel)) {
            throw new AMQPException('Before push you need to set a channel.');
        }
        $options = empty($options) ? array(
            'delivery_mode' => 2,  // 2 = "Persistent", 1 = "Non-persistent"
            'content_type' => 'text/json'
        ) : $options;        
        if ($delay > 0) {
            $queue = $this->declareDelayedQueue($queue, $delay); 
        } else {
            $queue = $this->declareQueue($queueName); // Get queue
        }
        $payload = json_encode(array('job' => $job, 'data' => $data));
        $job = $this->exchange->publish(
            $payload, 
            $queue->getName(),
            AMQP_MANDATORY, 
            $options
        );
        if ( ! $job) {
            throw new AMQPException('Could not push job to a queue');
        }
        return $job;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string $queueName queue name ( Routing Key )
     *
     * @return mixed job handler object or null
     */
    public function pop($queueName = null)
    {
        $queue = $this->declareQueue($queueName); // Declare queue if not exists
        $envelope = $queue->get();  // Get envelope
    
        if ($envelope instanceof AMQPEnvelope) { // * Send Message to JOB QUEUE
            return new AMQPJob($this->c, $queue, $envelope);  // Send incoming message to job class.
        }
        return null;
    }

    /**
     * Declare queue
     * 
     * @param string $name string
     *
     * @return object AMQPQueue
     */
    public function declareQueue($name = null)
    {
        $name = (empty($name)) ? $this->defaultQueueName : $name;

        $this->queue = new AMQPQueue($this->channel);
        $this->queue->setName($name);
        $this->queue->setFlags(AMQP_DURABLE);
        $this->queue->declareQueue();
        $this->queue->bind($this->exchange->getName(), $name);
        $this->queue->declareQueue();

        return $this->queue;
    }

    /**
     * Declare delated queue
     * 
     * @param string  $destination delayed queue name
     * @param integer $delay       interval
     *
     * @return object AMQPQueue delayed object
     */
    public function declareDelayedQueue($destination, $delay)
    {
        $destination = (empty($destination)) ? $this->defaultQueueName : $destination;
        $name = $destination . '_delayed_' . $delay;

        $queue = new AMQPQueue($this->channel);
        $queue->setName($name);
        $queue->setFlags(AMQP_DURABLE);
        $queue->setArguments(
            array(
            'x-dead-letter-exchange'    => $this->exchange->getName(),
            'x-dead-letter-routing-key' => $destination,
            'x-message-ttl'             => $delay * 1000,
            )
        );
        $queue->declareQueue();
        $queue->bind($this->exchange->getName(), $name);
        $queue->declareQueue();
        return $queue;
    }

    /**
     * Clear the contents of a queue
     *
     * @param string $name queue name
     * 
     * @return void
     */
    public function purgeQueue($name)
    {
        $channel = new AMQPChannel($this->AMQPconnection);
        $queue = new AMQPQueue($channel);
        $queue->setName($name);
        $queue->purge();
    }

    /**
     * Delete a queue and its contents.
     *
     * @param string $name queue name
     * 
     * @return void
     */
    public function deleteQueue($name)
    {
        $channel = new AMQPChannel($this->AMQPconnection);
        $queue = new AMQPQueue($channel);
        $queue->setName($name);
        $queue->delete();
    }

}

// END AMQP class

/* End of file AMQP.php */
/* Location: .Obullo/Queue/Handler/AMQP.php */