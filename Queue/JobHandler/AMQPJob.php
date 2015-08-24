<?php 

namespace Obullo\Queue\JobHandler;

use AMQPQueue;
use AMQPEnvelope;
use Obullo\Queue\Job;

/**
 * AMQPJob Handler
 * 
 * @category  Queue
 * @package   AMQPJob
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 */
class AMQPJob extends Job
{
    /**
     * Constructor
     * 
     * @param object $queue    AMQPQueue object
     * @param object $envelope AMQPEnvelope object
     */
    public function __construct(AMQPQueue $queue, AMQPEnvelope $envelope)
    {  
        global $c;
        $this->c = $c;
        $this->queue = $queue;
        $this->envelope = $envelope;
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

        if ($delay > 0) {
            $this->c['queue']->later($delay, $body['job'], $this->getName(), $body['data']);
        } else {
            $this->c['queue']->push($body['job'], $this->getName(), $body['data']);
        }
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
    public function getId()
    {
        return $this->envelope->getDeliveryTag();
    }
}