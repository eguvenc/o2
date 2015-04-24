<?php

namespace Obullo\Task;

use Controller;
use Obullo\Cli\Console;
use Obullo\Process\Process;

/**
 * Queue Controller
 *
 * Listen queue data and workers
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
class QueueController extends Controller
{
    /**
     * Constructor
     *
     * @return void
     */
    public function load()
    {
        $this->c['queue'];
    }

    /**
     * Run command
     * 
     * @return void
     */
    public function index()
    {
        $this->help();
    }

    /**
     * Print logo
     * 
     * @return string
     */
    public function logo()
    {
        echo Console::logo("Welcome to Queue Manager (c) 2015");
        echo Console::description("You are running \$php task queue command. For help type php task queue --help.");
    }

    /**
     * Print console help
     *
     * @return string
     */
    public function help()
    {
        $this->logo();

echo Console::help("Help:", true);
echo Console::newline(1);
echo Console::help("

Available Commands

    show        : Display all queued jobs.
    listen      : Wait and send jobs to job handler.
    help        : Display help.

Arguments

    --channel   : Sets queue channel.( Exchange )
    --route     : Sets queue name.   ( Route )

Optional

    --debug     : Enables queue output and any possible worker exceptions. ( Designed for local environment  )
    --delay     : Sets delay time for uncompleted jobs.
    --memory    : Sets maximum allowed memory for current job.
    --timeout   : Sets time limit execution of the current job.
    --sleep     : If we have not job on the queue sleep the script for a given number of seconds.
    --tries     : Sets the maximum number of times a job should be attempted.
    --env       : Sets your environment variable to job class.
    --project   : Sets your project name to works with multiple projects.
    --var       : Sets your custom variable if you need."
);
echo Console::newline(2);
echo Console::help("Usage for local:", true);
echo Console::newline(2);
echo Console::help("php task queue listen --channel=Log --route=my-computer-hostname.Logger --debug=1");
echo Console::newline(2);
echo Console::help("Usage for production:", true);
echo Console::newline(2);
echo Console::help("php task queue listen --channel=Log --route=my-computer-hostname.Logger --memory=128 --delay=0 --sleep=3 --timeout=3 --debug=0");
echo Console::newline(2);
    }

    /**
     * List ( debug ) queue data
     *
     * Example : php task queue show --route=Server1.Logger clear=1
     * 
     * @return string
     */
    public function show()
    {
        $this->logo();
        $break = "------------------------------------------------------------------------------------------";

        $channel = $this->cli->argument('channel');
        $route = $this->cli->argument('route', null);  // Sets queue route key ( queue name )
        $clear = $this->cli->argument('clear');

        if (empty($channel)) {
            echo Console::fail("Queue \"--channel\" can't be empty.");
            exit;
        }
        if (empty($route)) {
            echo Console::fail("Queue \"--route\" can't be empty.");
            exit;
        }
        echo Console::body("Following queue data ...\n\n");
        echo Console::body("Channel : ". $channel."\n");
        echo Console::body("Route   : ". $route."\n\n");

        $this->queue->channel($channel);  // Sets queue exchange
        
        echo Console::body($break. "\n");
        echo Console::body("Job ID  | Job Name            | Data \n");
        echo Console::body($break. "\n");

        $lines = '';
        while (true) {
            $job = $this->queue->pop($route);  // !!! Get the last message from queue but don't mark it as delivered
            if ( ! is_null($job)) {
                $raw = json_decode($job->getRawBody(), true);
                $jobIdRepeat = 6 - strlen($job->getJobId());  // 999999
                if (strlen($job->getJobId()) > 6) {
                    $jobIdRepeat = 6;
                }
                $jobNameRepeat = 20 - strlen($raw['job']);
                if (strlen($raw['job']) > 20) {
                    $jobNameRepeat = 20;
                }
                $lines = Console::body($job->getJobId().str_repeat(' ', $jobIdRepeat).'  | ');
                $lines.= Console::body($raw['job'].str_repeat(' ', $jobIdRepeat).' | ');
                $lines.= Console::text(json_encode($raw['data'], true)."\n", 'yellow');
                $lines.= "\n";
                echo $lines;
                if ($clear == 1) {  // Delete all jobs in the queue
                     $job->delete();
                }
            }
        }
    }

    /**
     * Listen Queue
     *
     * Example : 
     * php task queue listen --channel=Logger --route=Server1.Logger --memory=128 --delay=0 --timeout=3 --sleep=0 --tries=0 --debug=0 --env=production
     * 
     * @return void
     */
    public function listen()
    {
        $debug = $this->cli->argument('debug', 0);        // Enable / Disabled console debug.
        $channel = $this->cli->argument('channel', null); // Sets queue exchange
        $route = $this->cli->argument('route', null);     // Sets queue route key ( queue name )
        $memory = $this->cli->argument('memory', 128);    // Sets maximum allowed memory for current job.
        $delay = $this->cli->argument('delay', 0);        // Sets job delay interval
        $timeout = $this->cli->argument('timeout', 0);    // Sets time limit execution of the current job.
        $sleep = $this->cli->argument('sleep', 3);        // If we have not job on the queue sleep the script for a given number of seconds.
        $tries = $this->cli->argument('tries', 0);        // If job attempt failed we push back on to queue and increase attempt number.
        $env = $this->cli->argument('env', 'local');      // Sets environment for current worker.
        $project = $this->cli->argument('project', 'default');  // Sets project name for current worker ( This is useful working with multiple projects ). 
        $var = $this->cli->argument('var', null);         // Sets your custom variable
        
        if (empty($channel)) {
            echo Console::fail("Queue \"--channel\" can't be empty.");
            exit;
        }
        if (empty($route)) {
            echo Console::fail("Queue \"--route\" can't be empty.");
            exit;
        }
        $cmd = "php task worker --channel=$channel --route=$route --memory=$memory --delay=$delay --timeout=$timeout --sleep=$sleep --tries=$tries --debug=$debug --env=$env --project=$project --var=$var";

        $process = new Process($cmd, ROOT, null, null, $timeout);
        while (true) {
            $process->run();
            if ($debug == 1) {
                echo $process->getOutput();
            }
        }
        $this->c['logger']->debug($cmd);
    }

}

// END QueueController class

/* End of file QueueController.php */
/* Location: .Obullo/Task/QueueController.php */