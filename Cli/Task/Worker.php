<?php

namespace Obullo\Cli\Task;

use Obullo\Http\Controller;
use Obullo\Queue\Worker as QueueWorker;

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
class Worker extends Controller
{
    /**
     * Run worker
     * 
     * @return void
     */
    public function run()
    {
        $worker = new QueueWorker(
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