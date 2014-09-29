<?php 

namespace Obullo\Queue;

use DateTime;

/**
 * Job Class
 * 
 * @category  Queue
 * @package   Queue
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/queue
 */
Abstract class Job
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Console Debug on / off
     * 
     * @var boolean
     */
    public $debug = false;

    /**
     * Queue instance
     * 
     * @var object
     */
    protected $queue;

    /**
     * AMQP Envolope instance
     * 
     * @var object
     */
    protected $envelope;

    /**
     * The job handler instance.
     *
     * @var mixed
     */
    protected $instance;

    /**
     * The name of the queue the job belongs to.
     *
     * @var string
     */
    protected $queueName;

    /**
     * Indicates if the job has been deleted.
     *
     * @var bool
     */
    protected $deleted = false;

    /**
     * Worker Environment
     * 
     * @var string
     */
    public $env;

    /**
     * Fire the job.
     *
     * @return void
     */
    abstract public function fire();

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        $this->deleted = true;
    }

    /**
     * Determine if the job has been deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Release the job back into the queue.
     *
     * @param int $delay interval
     * 
     * @return void
     */
    abstract public function release($delay = 0);

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    abstract public function getAttempts();

    /**
     * Get the job id
     * 
     * @return int
     */
    abstract public function getJobId();

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    abstract public function getRawBody();

    /**
     * Resolve and fire the job handler method.
     *
     * @param array $payload data
     * 
     * @return void
     */
    protected function resolveAndFire(array $payload)
    {
        $class = ucfirst($payload['job']);
        $this->instance = new $class($this->c, $this->getEnv());
        $this->instance->fire($this, $payload['data']);
    }

    /**
     * Calculate the number of seconds with the given delay.
     *
     * @param mixed $delay datetime or int
     * 
     * @return int
     */
    protected function getSeconds($delay)
    {
        if ($delay instanceof DateTime) {
            return max(0, $delay->getTimestamp() - time());
        } else {
            return intval($delay);
        }
    }

    /**
     * Get the name of the queue the job belongs to.
     *
     * @return string
     */
    public function getName()
    {
        return $this->queueName;
    }

    /**
     * Set environment of current worker
     *
     * @param string $env environment
     * 
     * @return string
     */
    public function setEnv($env = 'local')
    {
        $this->env = $env;
    }

    /**
     * Get environment of current worker
     * 
     * @return string
     */
    public function getEnv()
    {
        return $this->env;
    }

}

// END Job class

/* End of file Job.php */
/* Location: .Obullo/Queue/Job.php */