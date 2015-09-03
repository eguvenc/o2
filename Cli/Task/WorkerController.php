<?php

namespace Obullo\Cli\Task;

use Obullo\Queue\Worker;

/**
 * Worker Controller ( Private controller )
 *
 * Worker consumes queue data and do jobs using queue job class
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
class WorkerController extends \Controller
{
    /**
     * Execute command
     * 
     * @return void
     */
    public function index()
    {
        $worker = new Worker(
            $this->c['app'],
            $this->c['config'],
            $this->c['queue'],
            $this->c['uri'],
            $this->c['logger']
        );
        $worker->init();
        $worker->pop();
    }
}