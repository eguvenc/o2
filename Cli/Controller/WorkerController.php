<?php

namespace Obullo\Cli\Controller;

use Obullo\Queue\Worker;

/**
 * Worker Controller
 *
 * Worker consumes queue data and do jobs using queue job class
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
Class WorkerController implements CliInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Cli arguments
     * 
     * @var array
     */
    protected $arguments;

    /**
     * Constructor
     * 
     * @param object $c         container
     * @param array  $arguments array
     * 
     * @return void
     */
    public function __construct($c, array $arguments = array())
    {
        $this->c = $c;
        $this->arguments = $arguments;
    }

    /**
     * Execute command
     * 
     * @return void
     */
    public function run()
    {
        $worker = new Worker($this->c, $this->arguments);
        $worker->init();
        $worker->pop();
    }
}

// END WorkerController class

/* End of file WorkerController.php */
/* Location: .Obullo/Cli/Controller/WorkerController.php */
