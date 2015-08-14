<?php

namespace Obullo\Queue\Handler;

use AMQPQueue;
use AMQPChannel;
use AMQPEnvelope;
use AMQPExchange;
use AMQPException;
use AMQPConnection;
use RuntimeException;
use Obullo\Queue\QueueInterface;
use Obullo\Config\ConfigInterface;
use Obullo\Queue\JobHandler\AMQPJob;
use Obullo\Service\ServiceProviderInterface;

/**
 * AMQP Handler
 * 
 * @category  Queue
 * @package   Queue
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 * @see       http://www.php.net/manual/pl/book.amqp.php
 * @see       http://www.brandonsavage.net/publishing-messages-to-rabbitmq-with-php/
 */
class AMQP implements QueueInterface
{
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
     * Default queue name if its not provided
     * 
     * @var string
     */
    protected $defaultQueueName;

    /**
     * Constructor
     *
     * @param object $config   \Obullo\Config\ConfigInterface
     * @param object $provider \Obullo\Service\Provider\ServiceProviderInterface 
     * @param array  $params   provider parameters
     */
    public function __construct(ConfigInterface $config, ServiceProviderInterface $provider, array $params)
    {
        $this->config = $config->load('queue')['amqp'];
        $this->AMQPconnection = $provider->get($params);
        
        $this->channel = new AMQPChannel($this->AMQPconnection);
        $this->defaultQueueName = 'default';
    }

    /**
     * Create AMQPExchange if not exists otherswise get instance of it
     * 
     * @param object $name exchange name
     * @param object $type available types AMQP_EX_TYPE_DIRECT, AMQP_EX_TYPE_FANOUT, AMQP_EX_TYPE_HEADER or AMQP_EX_TYPE_TOPIC,
     * @param object $flag available flags AMQP_DURABLE, AMQP_PASSIVE
     *
     * @return object
     */
    public function exchange($name, $type = null, $flag = null)
    {
        $type = (empty($type)) ? constant($this->config['exchange']['type']) : $type;
        $flag = (empty($flag)) ? constant($this->config['exchange']['flag']) : $flag;

        $this->exchange = new AMQPExchange($this->channel);
        $this->exchange->setName($name);
        $this->exchange->setFlags($flag);
        $this->exchange->setType($type);
        $this->exchange->declareExchange();
        return $this;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param string $job     name
     * @param string $route   queue name ( route key )
     * @param mixed  $data    payload
     * @param array  $options delivery options
     *
     * @link(Set Delivery Mode, http://stackoverflow.com/questions/6882995/setting-delivery-mode-for-amqp-rabbitmq)
     * 
     * @throws AMQPException
     * @return boolean
     */
    public function push($job, $route, $data, $options = array())
    {
        if (empty($this->exchange)) {
            $this->exchange($job);
        }
        $queue = $this->declareQueue($route); // Get queue
        return $this->publishJob($queue, $job, $data, $options);
    }

    /**
     * Push a new job onto delayed queue.
     *
     * @param int    $delay   date
     * @param string $job     name
     * @param string $route   queue name ( Routing Key )
     * @param mixed  $data    payload
     * @param array  $options delivery options
     *
     * @link(Set Delivery Mode, http://stackoverflow.com/questions/6882995/setting-delivery-mode-for-amqp-rabbitmq)
     * 
     * @throws AMQPException
     * @return boolean
     */
    public function later($delay, $job, $route, $data, $options = array())
    {
        if (empty($this->channel)) {
            throw new AMQPException('Before push you need to set a channel.');
        }
        $queue = $this->declareDelayedQueue($route, $delay); // Get queue
        return $this->publishJob($queue, $job, $data, $options);
    }

    /**
     * Publish queue job
     * 
     * @param object $queue   AMQPQueue
     * @param string $job     queue name
     * @param array  $data    payload
     * @param array  $options delivery options
     * 
     * @return bool
     */
    protected function publishJob($queue, $job, $data, $options = array())
    {
        $options = empty($options) ? array(
            'delivery_mode' => 2,           // 2 = "Persistent", 1 = "Non-persistent"
            'content_type' => 'text/json'
        ) : $options;
        $payload = json_encode(array('job' => $job, 'data' => $data));
        $result = $this->exchange->publish(
            $payload, 
            $queue->getName(),
            AMQP_MANDATORY, 
            $options
        );
        $this->exchange = null;  //  Reset channel
        if (! $result) {
            throw new AMQPException('Could not push job to a queue');
        }
        return $result;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string $route queue name ( routing key )
     *
     * @return mixed job handler object or null
     */
    public function pop($route = null)
    {
        $queue    = $this->declareQueue($route); // Declare queue if not exists
        $envelope = $queue->get();  // Get envelope
    
        if ($envelope instanceof AMQPEnvelope) { // * Send Message to JOB QUEUE
            return new AMQPJob($queue, $envelope);  // Send incoming message to job class.
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
     * @return object
     */
    public function purgeQueue($name)
    {
        $channel = new AMQPChannel($this->AMQPconnection);
        $queue = new AMQPQueue($channel);
        $queue->setName($name);
        $queue->purge();
        return $this;
    }

    /**
     * Delete a queue and its contents.
     *
     * @param string $name queue name
     * 
     * @return object
     */
    public function deleteQueue($name)
    {
        $channel = new AMQPChannel($this->AMQPconnection);
        $queue = new AMQPQueue($channel);
        $queue->setName($name);
        $queue->delete();
        return $this;
    }

}