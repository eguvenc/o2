<?php

namespace Obullo\Queue;

use Obullo\Process\Process;

/**
 * Queue Listener Class
 * 
 * @category  Queue
 * @package   Queue
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/queue
 */
Class Listener
{
    /**
     * Constructor
     *
     * @param object $c         container
     * @param array  $arguments data
     */
    public function __construct($c, $arguments)
    {
        $this->queue = $c->load('service/queue');
        $this->parser = $c->load('cli/parser');
        $this->parser->parse($arguments);

        switch ($this->parser->segment(0)) {
        case 'list':
            $this->listQueue();
            break;
        case 'listen':
            $this->logo();
            $this->listenQueue();
            break;
        default:
            $this->logo();
            $this->help();
            break;
        }
    }

    /**
     * Display console logo
     * 
     * @return string
     */
    public function logo()
    {
        echo "\33[1;36m".'
            ______  _            _  _
           |  __  || |__  _   _ | || | ____
           | |  | ||  _ || | | || || ||  _ |
           | |__| || |_||| |_| || || || |_||
           |______||____||_____||_||_||____|

            Welcome to Task Manager (c) 2014
    You are running $php task queue command which is located in app / tasks folder.'."\n\033[0m\n";
    }

    /**
     * Print console help
     *
     * @return string
     */
    public function help()
    {
        echo "\33[1;36mUsage:\33[0m\33[0;36m
    php task [command] [arguments]\n\33[0m\n";

    echo "\33[1;36mAvailable commands:\33[0m\33[0;36m

    \33[1;36mlist       : List queued jobs.\33[0m\33[0;36m
    \33[1;36mlisten     : Wait and send jobs to job handler.\33[0m\33[0;36m
    \33[1;36mdown       : Pause the queue in maintenance mode.\33[0m\33[0;36m
    \33[1;36mup         : Release the paused queue.\33[0m\33[0;36m

    \33[1;36mExamples :\33[0m\33[0;36m
    \$php task queue list --channel=Logs --route=ServerName.Logger
    \$php task queue listen --channel=Logs --route=ServerName.Logger --memory=128 --delay=0 --timeout=3 --debug=0\33[0m\n";
    }

    /**
     * List ( debug ) queue data
     *
     * php task queue list --route=Server1.Logger clear=1
     * 
     * @return string
     */
    public function listQueue()
    {
        $this->logo();
        $break = "------------------------------------------------------------------------------------------";

        $channel = $this->parser->argument('channel');
        $route = $this->parser->argument('route', null);  // Sets queue route key ( queue name )
        $clear = $this->parser->argument('clear');

        $this->emptyControl($channel, $route);

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
     * php task queue listen --channel=Logger --route=Server1.Logger --memory=128 --delay=0 --timeout=3 --sleep=0 --maxTries=0 --debug=0 --env=prod
     * 
     * @return void
     */
    public function listenQueue()
    {
        $channel = $this->parser->argument('channel', null); // Sets queue exchange
        $route = $this->parser->argument('route', null);     // Sets queue route key ( queue name )
        $memory = $this->parser->argument('memory', 128);    // Sets maximum allowed memory for current job.
        $delay = $this->parser->argument('delay', 0);        // Sets job delay interval
        $timeout = $this->parser->argument('timeout', 0);    // Sets time limit execution of the current job.
        $sleep = $this->parser->argument('sleep', 0);        // If we have not job on the queue sleep the script for a given number of seconds.
        $maxTries = $this->parser->argument('maxTries', 0);  // If job attempt failed we push and increase attempt number.
        $debug = $this->parser->argument('debug', 0);        // Enable / Disabled console debug.
        $env = $this->parser->argument('env', 'local');      // Sets environment for current worker.
        $project = $this->parser->argument('project', 'default');  // Sets project name for current worker. 
        $var = $this->parser->argument('var', null);         // Sets your custom variable
        
        $this->emptyControl($channel, $route);

        $cmd = "php task worker --channel=$channel --route=$route --memory=$memory --delay==$delay --timeout=$timeout --sleep=$sleep --maxTries=$maxTries --debug=$debug --env=$env --project=$project --var=$var";

        $process = new Process($cmd, ROOT, null, null, $timeout);
        while (true) {
            $process->run();
            if ($debug == 1) {
                echo $process->getOutput();
            }
        }
    }

    /**
     * Check --channel and --route is empty
     * 
     * @param string $channel exchange
     * @param string $route   queue name
     * 
     * @return void
     */
    protected function emptyControl($channel, $route)
    {
        if (empty($channel)) {
            echo "\33[1;36mQueue \"--channel\" can't be empty.\33[0m\n";
            exit;
        }
        if (empty($route)) {
            echo "\33[1;36mQueue \"--route\" can't be empty.\33[0m\n";
            exit;
        }
    }

}

// END Listener class

/* End of file Listener.php */
/* Location: .Obullo/Queue/Listener.php */