<?php

namespace Obullo\Task;

use Controller;
use Obullo\Task\Helper\Console;

/**
 * Log Controller
 *
 * Follow log data
 * 
 * @category  Console
 * @package   Tasks
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
class LogController extends Controller
{
    /**
     * Loader
     *
     * @return void
     */
    public function load()
    {
        $this->c['cli/parser as parser'];
        $this->c['logger'];
    }

    /**
     * Execute command
     * 
     * @return void
     */
    public function index()
    {
        $this->logo();
        $this->parser->parse(func_get_args());
        $dir = $this->parser->argument('dir', 'http');
        $table = $this->parser->argument('dir', 'logs');

        if ($this->parser->argument('help')) {
            return $this->help();
        }
        $Class = '\\Obullo\Log\Console\Reader\\'.ucfirst($this->logger->getPrimaryWriter());
        $class = new $Class;
        $class->follow($this->c, $dir, $table);
    }

    /**
     * Print Logo
     * 
     * @return string colorful logo
     */
    public function logo() 
    {
        echo Console::logo("Welcome to Log Manager (c) 2015");
        echo Console::description("You are displaying logs. For more help type \$php task log --help.");
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
        if ($this->c->exists('queue')) {
            $this->c['queue']->deleteQueue($this->c['config']['logger']['queue']['route']); // Clear queue data
        }
        $this->c['logger']->debug('php task log clear');

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

echo Console::help("Help:\n\n", true);
echo Console::help(
"Available Commands

    clear    : Clear log data ( also removes the queue logs ).
    help     : Help

Available Arguments

    --dir    : Sets log direction for reader. Directions : cli, ajax, http ( default )
    --table  : Collection name if mongo driver used otherwise database table name.\n\n");

echo Console::help("Usage:\n\n",true);
echo Console::help(
"php task log --dir=value

    php task log 
    php task log --dir=cli
    php task log --dir=ajax
    php task log --dir=http --table=logs\n\n");


echo Console::help("Description:\n\n", true);
echo Console::help("Read log data from '". RESOURCES ."data". DS ."logs' folder.\n\n");

        $this->c['logger']->debug('php task log help');

    }

}

// END LogController class

/* End of file LogController.php */
/* Location: .Obullo/Task/LogController.php */