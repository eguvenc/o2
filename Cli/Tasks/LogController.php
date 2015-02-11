<?php

namespace Obullo\Cli\Tasks;

use Controller,
    Obullo\Cli\LogFollower;

/**
 * Log Controller
 *
 * Follow log data
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
Class LogController extends Controller
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
        $Class = '\\Obullo\Cli\Log\Reader\\'.ucfirst($this->logger->getWriterName());
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
        echo "\33[1;36m".'   
         _____ _____ _____ __    __    _____ 
        |     | __  |  |  |  |  |  |  |     |
        |  |  | __ -|  |  |  |__|  |__|  |  |
        |_____|_____|_____|_____|_____|_____|
                                     
        Welcome to Log Manager v2.0 (c) 2015
You are displaying logs. For more help type $php task help.'."\n\033[0m";

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
        /**
         * Clear File handler data
         */
        $files = array(
            trim($this->c['config']['logger']['file']['path']['http'], '/'),
            trim($this->c['config']['logger']['file']['path']['ajax'], '/'),
            trim($this->c['config']['logger']['file']['path']['cli'], '/'),
        );
        foreach ($files as $file) {
            $file = str_replace('/', DS, $file);
            if (strpos($file, 'data') === 0) { 
                $file = str_replace('data', rtrim(DATA, DS), $file);
            } 
            $exp = explode(DS, $file);
            $filename = array_pop($exp);
            $path= implode(DS, $exp). DS;
            if (is_file($path.$filename)) {
                unlink($path.$filename);
            }
        }
        /**
         * Clear Queue handler data
         */
        $queue = $this->c['queue'];
        $queue->deleteQueue($this->c['config']['logger']['queue']['route']);
        
        echo "\33[1;36mApplication logs deleted.\33[0m\n";
    }

    /**
     * Log help
     * 
     * @return string
     */
    public function help()
    {
        $this->logo();

        echo "\33[0;36m".'
'."\33[1;36m".'Help:'."\33[0m\33[0;36m".'

Available Commands

    clear    : Clear log data ( also removes the queue logs ).
    help     : Help

Available Arguments

    --dir    : Sets log direction for reader. Directions : cli, ajax, http ( default )
    --table  : Collection name if mongo driver used otherwise database table name.'."\n\n\033[0m";

echo "\33[1;36mUsage:\33[0m\33[0;36m

php task log --dir=value

    php task log 
    php task log --dir=cli
    php task log --dir=ajax
    php task log --dir=http --table=logs\n\33[0m\n";


echo "\33[1;36mDescription:\33[0m\33[0;36m\n\nRead log data from app/data/logs folder.\n\33[0m\n";

    }

}

// END LogController class

/* End of file LogController.php */
/* Location: .Obullo/Cli/Tasks/LogController.php */