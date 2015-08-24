<?php

namespace Obullo\Queue\Handler;

use RuntimeException;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Message\AMQPMessage;

use Obullo\Queue\QueueInterface;
use Obullo\Config\ConfigInterface;
use Obullo\Queue\JobHandler\AMQPJob;
use Obullo\Service\ServiceProviderInterface;

/**
 * AMQP Handler for Composer package "videlalvaro/php-amqplib"
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
class AMQPLib implements QueueInterface
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
        
        $this->channel = $this->AMQPconnection->channel();
        $this->defaultQueueName = 'default';
    }

    /**
     * Parse amqp options and returns to amqp supported types
     * 
     * @param string $type available types (direct) AMQP_EX_TYPE_DIRECT, (fanout) AMQP_EX_TYPE_FANOUT
     * @param string $flag available flags AMQP_DURABLE, AMQP_PASSIVE
     * 
     * @return array
     */
    protected function getOptions($type = null, $flag = null)
    {
        $type = (empty($type)) ? $this->config['exchange']['type'] : $type;
        $flag = (empty($flag)) ? $this->config['exchange']['flag'] : $flag;

        if ($typeStr = strstr($type, 'TYPE_')) {
            $type = strtolower(substr($typeStr, 5));
        }
        $passive = true;
        $durable = false;
        if ($flag == 'AMQP_DURABLE') {
            $passive = false;
            $durable = true;
        }
        return [
            'type' => $type,
            'passive' => $passive,
            'durable' => $durable
        ];
    }

    /**
     * Push a new job onto the queue.
     *
     * @param string $job     name (exchange)
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
        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $exchange = $job;
        $queue  = (empty($route)) ? $this->defaultQueueName : $route;
        $params = $this->getOptions();

        $this->channel->queue_declare($queue, false, true, false, false);
        $this->channel->exchange_declare($queue, $params['type'], $params['passive'], $params['durable'], false);
        $this->channel->queue_bind($queue, $exchange);

        return $this->publishJob($exchange, $data, $options);
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
     * @link(SetDeliveryMode, http://stackoverflow.com/questions/6882995/setting-delivery-mode-for-amqp-rabbitmq)
     * @link(PhpAmqpDelay,    http://stackoverflow.com/questions/21942063/how-to-delay-php-amqplib?rq=1)
     * 
     * @throws AMQPException
     * @return boolean
     */
    public function later($delay, $job, $route, $data, $options = array())
    {
        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $exchange = $job;
        $destination = (empty($route)) ? $this->defaultQueueName : $route;
        $queue = $destination . '_delayed_' . $delay;

        $arguments = new AMQPTable(
            [
               "x-dead-letter-exchange" => $exchange,
               'x-dead-letter-routing-key' => $destination,
               "x-expires" => $delay * (1000 + 1000)
            ]
        );

        // "x-message-ttl" => $delay * 1000

        $this->channel->queue_declare($queue, false, true, false, false, false, $arguments);
        $this->channel->exchange_declare($exchange, 'direct');
        $this->channel->queue_bind($queue, $exchange);

        return $this->publishJob($exchange, $data, $options);
    }


    // public function later($delay, $job, $route, $data, $options = array())
    // {
    //     $destination = (empty($route)) ? $this->defaultQueueName : $route;
    //     $queue = $destination . '_delayed_' . $delay;
    //     $exchange = $job . '_delayed_' . $delay;

    //     // $queue = new AMQPQueue($this->channel);
    //     // $queue->setName($queue);
    //     // $queue->setFlags(AMQP_DURABLE);
    //     // $queue->setArguments(
    //     //     array(
    //     //     'x-dead-letter-exchange'    => $this->exchange->getName(),
    //     //     'x-dead-letter-routing-key' => $destination,
    //     //     'x-message-ttl'             => $delay * 1000,
    //     //     )
    //     // );
    //     // $queue->declareQueue();
    //     // $queue->bind($this->exchange->getName(), $queue);
    //     // $queue->declareQueue();

    //     // $this->channel->queue_declare($queue, false, true, false, false);
    //     // $this->exchange($job);
    //     // $this->channel->queue_bind($queue, $job);

    //     $this->channel->queue_declare('queue.default');
    //     $this->channel->exchange_declare('exchange.default', 'direct');
    //     $this->channel->queue_bind('queue.default', 'exchange.default');

    //     $this->channel->queue_declare(
    //         $queue,
    //         false,
    //         true,
    //         false,
    //         false,
    //         true,
    //         array(
    //             'x-message-ttl' => array('I', $delay * 1000),   // delay in seconds to milliseconds
    //             "x-expires" => array("I", $delay * 1000 + 1000),
    //             'x-dead-letter-exchange' => array('S', 'exchange.default') // after message expiration in delay queue, move message to the default queue
    //         )
    //     );
    //     $this->channel->exchange_declare($exchange, 'direct');
    //     $this->channel->queue_bind($queue, $exchange);

    //     return $this->publishJob($exchange, $data, $options);
    // }

    /**
     * Publish queue job
     * 
     * @param string $job     exchange
     * @param array  $data    payload
     * @param array  $options delivery options
     * 
     * @return bool
     */
    protected function publishJob($job, $data, $options = array())
    {
        $options = empty($options) ? array(
            'delivery_mode' => 2,           // 2 = "Persistent", 1 = "Non-persistent"
            'content_type' => 'text/json'
        ) : $options;

        $payload = json_encode(array('job' => $job, 'data' => $data));
        $message = new AMQPMessage($payload, $options);
        
        return $this->channel->basic_publish($message, $job);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string $job   exchange name
     * @param string $route queue name ( routing key )
     *
     * @return mixed job handler object or null
     */
    public function pop($job, $route = null)
    {
        $exchange = $job;
        $queue  = (empty($route)) ? $this->defaultQueueName : $route;
        $params = $this->getOptions();

        $this->channel->queue_declare($queue, false, true, false, false);
        $this->channel->exchange_declare($exchange, $params['type'], $params['passive'], $params['durable'], false);
        $this->channel->queue_bind($queue, $exchange);

        $envelope = $this->channel->basic_get($queue);
        

        // $this->channel->basic_ack($envelope->delivery_info['delivery_tag']);

        var_dump($envelope);

        // $envelope = $queue->get();  // Get envelope
    
        // if ($envelope instanceof AMQPEnvelope) { // * Send Message to JOB QUEUE
        //     return new AMQPJob($queue, $envelope);  // Send incoming message to job class.
        // }
        // return null;
    }

    /**
     * Declare queue
     * 
     * @param string $name string
     *
     * @return object AMQPQueue
     */
    // public function declareQueue($name = null)
    // {
    //     $name = (empty($name)) ? $this->defaultQueueName : $name;

    //     $this->queue = new AMQPQueue($this->channel);
    //     $this->queue->setName($name);
    //     $this->queue->setFlags(AMQP_DURABLE);
    //     $this->queue->declareQueue();
    //     $this->queue->bind($this->exchange->getName(), $name);
    //     $this->queue->declareQueue();

    //     return $this->queue;
    // }

    /**
     * Declare delated queue
     *
     * @link http://stackoverflow.com/questions/21942063/how-to-delay-php-amqplib?rq=1
     * 
     * @param string  $destination delayed queue name
     * @param integer $delay       interval
     *
     * @return object AMQPQueue delayed object
     */
    // public function declareDelayedQueue($destination, $delay)
    // {
    //     // $destination = (empty($destination)) ? $this->defaultQueueName : $destination;
    //     // $name = $destination . '_delayed_' . $delay;

    //     // $queue = new AMQPQueue($this->channel);
    //     // $queue->setName($name);
    //     // $queue->setFlags(AMQP_DURABLE);
    //     // $queue->setArguments(
    //     //     array(
    //     //     'x-dead-letter-exchange'    => $this->exchange->getName(),
    //     //     'x-dead-letter-routing-key' => $destination,
    //     //     'x-message-ttl'             => $delay * 1000,
    //     //     )
    //     // );
    //     // $queue->declareQueue();
    //     // $queue->bind($this->exchange->getName(), $name);
    //     // $queue->declareQueue();
    //     // return $queue;
    // }

    /**
     * Clear the contents of a queue
     *
     * @param string $name queue name
     * 
     * @return object
     */
    // public function purgeQueue($name)
    // {
    //     $channel = new AMQPChannel($this->AMQPconnection);
    //     $queue = new AMQPQueue($channel);
    //     $queue->setName($name);
    //     $queue->purge();
    //     return $this;
    // }

    // /**
    //  * Delete a queue and its contents.
    //  *
    //  * @param string $name queue name
    //  * 
    //  * @return object
    //  */
    // public function deleteQueue($name)
    // {
    //     $channel = new AMQPChannel($this->AMQPconnection);
    //     $queue = new AMQPQueue($channel);
    //     $queue->setName($name);
    //     $queue->delete();
    //     return $this;
    // }

}