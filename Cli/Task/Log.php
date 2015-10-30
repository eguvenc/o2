<?php

namespace Obullo\Cli\Task;

use Obullo\Cli\Console;
use Obullo\Cli\Controller;

class Log extends Controller
{
    /**
     * Execute command
     * 
     * @return void
     */
    public function index()
    {
        $this->logo();
        $this->uri = $this->request->getUri();

        $dir = $this->uri->argument('dir', 'http');
        $writer = $this->logger->getWriter();

        $table = $db = null;
        if ($writer == 'mongo') {
            $table = $this->uri->argument('table');
            $db    = $this->uri->argument('db');
            if (empty($table) || empty($db)) {
                 echo Console::fail('MongoDB database or table not given.');
                 return;
            }
        }
        if ($this->uri->argument('help')) {
            return $this->help();
        }
        $reader = ucfirst($this->logger->getWriter());

        if ($reader == 'Null') {
            echo Console::text("Logging feature disabled from your config.", 'yellow');
            echo Console::newline(2);
            return;
        }
        $Class = '\\Obullo\Cli\LogReader\\'.$reader;
        $class = new $Class;
        $class->follow($this->c, $dir, $db, $table);
    }

    /**
     * Print Logo
     * 
     * @return string colorful logo
     */
    public function logo() 
    {
        echo Console::logo("Welcome to Log Manager (c) 2015");
        echo Console::description("You are displaying log data. For more help type \$php task log help.");
    }

    /**
     * Clear all log data from log folder
     *
     * Also removes queue data
     * 
     * @return void
     */
    public function clear()
    {
        $files = \Obullo\Cli\LogReader\File::getPathArray();

        foreach ($files as $file) {
            $file = ROOT. $file;
            $exp = explode('/', $file);
            $filename = array_pop($exp);
            $path = implode('/', $exp). '/';

            if (is_file($path.$filename)) {
                unlink($path.$filename);
            }
        }
        echo Console::success('Application logs deleted.');
    }

    /**
     * Log help
     * 
     * @return string
     */
    public function help()
    {
        $this->logo();

echo Console::help("Help:", true);
echo Console::newline(2);
echo Console::help(
"Available Commands

    clear    : Clear log data ( also removes the queue logs ).
    help     : Help

Available Arguments

    --dir    : Sets log direction for reader. Directions : cli, ajax, http ( default )
    --db     : Database name if mongo driver used.
    --table  : Collection name if mongo driver used.");
echo Console::newline(2);
echo Console::help("Usage:",true);
echo Console::newline(2);
echo Console::help(
"php task log --dir=value

    php task log 
    php task log --dir=cli
    php task log --dir=ajax
    php task log --dir=http --table=logs");
echo Console::newline(2);

echo Console::help("Description:", true);
echo Console::newline(2);
echo Console::help("Read log data from '". RESOURCES ."data/logs' folder.");
echo Console::newline(2);
    }

}