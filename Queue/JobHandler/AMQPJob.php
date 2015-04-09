<?php 

namespace Obullo\Queue\JobHandler;

use AMQPQueue;
use AMQPEnvelope;
use Obullo\Queue\Job;
use Obullo\Container\Container;

/**
 * AMQPJob Handler
 * 
 * @category  Queue
 * @package   AMQPJob
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 */
class AMQPJob extends Job
{
    /**
     * Constructor
     * 
     * @param object $c        Container
     * @param object $queue    AMQPQueue object
     * @param object $envelope AMQPEnvelope object
     */
    public function __construct(Container $c, AMQPQueue $queue, AMQPEnvelope $envelope)
    {  
        $this->c = $c; 
        $this->queue = $queue;
        $this->envelope = $envelope;
        $this->logger = $this->c['logger'];
        $this->logger->debug('Queue AMQPJob Class Initialized');
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        $this->resolveAndFire(json_decode($this->envelope->getBody(), true));
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->envelope->getBody();
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();
        $this->queue->ack($this->envelope->getDeliveryTag());
    }

    /**
     * Get queue name
     *
     * @return string
     */
    public function getName()
    {
        return $this->queue->getName();
    }

    /**
     * Release the job back into the queue.
     *
     * @param int $delay interval
     *
     * @return void
     */
    public function release($delay = 0)
    {
        $this->delete(); // Delete the job
        $body = $this->envelope->getBody();
        $body = json_decode($body, true);
        $body['data']['attempts'] = $this->getAttempts() + 1; // Write attempts to body

        $this->c['queue']->push($body['job'], $this->getName(), $body['data'], $delay);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function getAttempts()
    {
        $body = json_decode($this->envelope->getBody(), true);
        return isset($body['data']['attempts']) ? $body['data']['attempts'] : 0;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->envelope->getDeliveryTag();
    }
}

// END AMQPJob Class
/* End of file AMQPJob.php

/* Location: .Obullo/Queue/AMQPJob.php */