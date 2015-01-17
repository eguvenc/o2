<?php

namespace Obullo\Cli\Tasks;

use Controller,
    Obullo\Queue\Worker;

/**
 * Worker Controller ( Private controller )
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
Class WorkerController extends Controller
{
    /**
     * Execute command
     * 
     * @return void
     */
    public function index()
    {
        $worker = new Worker($this->c, func_get_args());
        $worker->init();
        $worker->pop();
    }
}

// END WorkerController class

/* End of file WorkerController.php */
/* Location: .Obullo/Cli/Tasks/WorkerController.php */
