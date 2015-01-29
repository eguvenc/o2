<?php

namespace Obullo\Cli\Tasks;

use Controller,
    Obullo\Process\Process;

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
Class QueueController extends Controller
{
    /**
     * Constructor
     *
     * @return void
     */
    public function load()
    {
        $this->c->load('queue');
        $this->c->load('cli/parser as parser');
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
        echo "\33[1;36m".'
         _____ _____ _____ __    __    _____ 
        |     | __  |  |  |  |  |  |  |     |
        |  |  | __ -|  |  |  |__|  |__|  |  |
        |_____|_____|_____|_____|_____|_____|

        Welcome to Task Manager (c) 2015
    You are running $php task queue command. For help type php task queue --help.'."\n\033[0m\n";
    }

    /**
     * Print console help
     *
     * @return string
     */
    public function help()
    {
        $this->logo();
        echo "\33[0;36m".'
'."\33[1;36m".'Help:'."\33[0m\33[0;36m".'

Available Commands

    show        : Display all queued jobs.
    listen      : Wait and send jobs to job handler.
    help        : Display help.

Arguments

    --channel   : Sets queue exchange ( Channel ).
    --route     : Sets queue name.

Optional

    --debug     : Enables queue output and any possible worker exceptions. ( Designed for local environment  )
    --delay     : Sets delay time for uncompleted jobs.
    --memory    : Sets maximum allowed memory for current job.
    --timeout   : Sets time limit execution of the current job.
    --sleep     : If we have not job on the queue sleep the script for a given number of seconds.
    --tries     : Sets the maximum number of times a job should be attempted.
    --env       : Sets your environment variable to job class.
    --project   : Sets your project name to works with multiple projects.
    --var       : Sets your custom variable if you need.

'."\n\033[0m";

echo "\33[1;36mUsage for local:\33[0m\33[0;36m

php task queue listen --channel=Log --route=my-computer-hostname.Logger --memory=128 --delay=0 --timeout=3 --debug=1\n\n";

echo "\33[1;36mUsage for production:\33[0m\33[0;36m

php task queue listen --channel=Log --route=my-computer-hostname.Logger --memory=128 --delay=0 --timeout=3 --debug=0\n\33[0m\n";

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
        $this->parser->parse(func_get_args());

        $break = "------------------------------------------------------------------------------------------";

        $channel = $this->parser->argument('channel');
        $route = $this->parser->argument('route', null);  // Sets queue route key ( queue name )
        $clear = $this->parser->argument('clear');

        if (empty($channel)) {
            echo "\33[1;36mQueue \"--channel\" can't be empty.\33[0m\n";
            exit;
        }
        if (empty($route)) {
            echo "\33[1;36mQueue \"--route\" can't be empty.\33[0m\n";
            exit;
        }
        echo "\33[0;36mFollowing queue data ...\33[0m\n\n";
        echo "\33[1;36mChannel : ".$channel."\33[0m\n";
        echo "\33[1;36mRoute   : ".$route."\33[0m\n";

        $this->queue->channel($channel);  // Sets queue exchange
        
        echo "\033[1;36m".$break."\33[0m\n";
        echo "\033[1;36m".' Job ID | Job Name             | Data '."\33[0m\n";
        echo "\033[1;36m".$break."\33[0m\n";

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
                $lines = "\033[1;36m ".$job->getJobId().str_repeat(' ', $jobIdRepeat).' | ';
                $lines.= $raw['job'].str_repeat(' ', $jobNameRepeat).' | ';
                $lines.= "\033[0;36m ".json_encode($raw['data'], true)."\33[0m\n";
                $lines.= "\33[0m\n";
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
        $this->parser->parse(func_get_args());

        $debug = $this->parser->argument('debug', 0);        // Enable / Disabled console debug.
        $channel = $this->parser->argument('channel', null); // Sets queue exchange
        $route = $this->parser->argument('route', null);     // Sets queue route key ( queue name )
        $memory = $this->parser->argument('memory', 128);    // Sets maximum allowed memory for current job.
        $delay = $this->parser->argument('delay', 0);        // Sets job delay interval
        $timeout = $this->parser->argument('timeout', 0);    // Sets time limit execution of the current job.
        $sleep = $this->parser->argument('sleep', 3);        // If we have not job on the queue sleep the script for a given number of seconds.
        $tries = $this->parser->argument('tries', 0);        // If job attempt failed we push back on to queue and increase attempt number.
        $env = $this->parser->argument('env', 'local');      // Sets environment for current worker.
        $project = $this->parser->argument('project', 'default');  // Sets project name for current worker ( This is useful working with multiple projects ). 
        $var = $this->parser->argument('var', null);         // Sets your custom variable
        
        if (empty($channel)) {
            echo "\33[1;36mQueue \"--channel\" can't be empty.\33[0m\n";
            exit;
        }
        if (empty($route)) {
            echo "\33[1;36mQueue \"--route\" can't be empty.\33[0m\n";
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
    }

}

// END QueueController class

/* End of file QueueController.php */
/* Location: .Obullo/Cli/Tasks/QueueController.php */