<?php

namespace Obullo\Task;

use Controller;
use Obullo\Cli\Console;

/**
 * Log Controller
 *
 * Follow log data
 * 
 * @category  Console
 * @package   Tasks
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
class LogController extends Controller
{
    /**
     * Execute command
     * 
     * @return void
     */
    public function index()
    {
        $this->logo();
        $dir = $this->cli->argument('dir', 'http');

        $writer = $this->logger->getWriter();
        $table = $db = null;
        if ($writer == 'mongo') {
            $table = $this->cli->argument('table');
            $db    = $this->cli->argument('db');
            if (empty($table) || empty($db)) {
                 echo Console::fail('MongoDB database or table not given.');
                 return;
            }
        }
        if ($this->cli->argument('help')) {
            return $this->help();
        }
        $reader = ucfirst($this->logger->getWriter());

        if ($reader == 'Null') {
            echo Console::fail("Logging feature disabled from your config.");
            echo Console::newline(1);
            return;
        }
        $Class = '\\Obullo\Log\Cli\\'.$reader;
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
        echo Console::description("You are displaying logs. For more help type \$php task log help.");
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
        $files = array(
            trim($this->c['config']['logger']['file']['path']['http'], '/'),
            trim($this->c['config']['logger']['file']['path']['ajax'], '/'),
            trim($this->c['config']['logger']['file']['path']['cli'], '/'),
        );
        foreach ($files as $file) {
            $file = ROOT. str_replace('/', DS, $file);
            $exp = explode(DS, $file);
            $filename = array_pop($exp);
            $path = implode(DS, $exp). DS;

            if (is_file($path.$filename)) {
                unlink($path.$filename);
            }
        }
        if ($this->c->has('queue')) {
            $this->c['queue']->deleteQueue($this->c['config']['logger']['queue']['route']); // Clear queue data
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
echo Console::help("Read log data from '". RESOURCES ."data". DS ."logs' folder.");
echo Console::newline(2);
    }

}