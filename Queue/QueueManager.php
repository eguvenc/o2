<?php

namespace Obullo\Queue;

use Obullo\Container\ContainerInterface;

/**
 * QueueManager Class
 * 
 * @category  Manager
 * @package   QueueManager
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 */
class QueueManager
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Service Parameters
     * 
     * @var array
     */
    protected $params;

    /**
     * Create classes
     * 
     * @param object $c container
     * 
     * @return object
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Set parameters
     * 
     * @param array $params parameters
     *
     * @return void
     */
    public function setParameters(array $params)
    {
        $this->params = $params;
    }

    /**
     * Returns to selected queue handler object
     * 
     * @return void
     */
    public function getClass()
    {
        return new $this->params['class'](
            $this->c['config'],
            $this->c['app']->provider($this->params['provider']['name']),
            $this->params['provider']['params']
        );
    }

}