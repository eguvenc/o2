<?php

namespace Obullo\Cli\Controller;

/**
 * Clear Controller
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/Cli
 */
Class ClearController implements CliInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Config array
     * 
     * @var array
     */
    protected $config;

    /**
     * Cli command parser
     * 
     * @var object
     */
    protected $parser;

    /**
     * Constructor
     *
     * @param object $c         container
     * @param object $arguments parameters
     */
    public function __construct($c, array $arguments = array())
    {
        $this->c = $c;
        $this->config = $this->c['config'];

        $this->parser = $c->load('cli/parser');
        $this->parser->parse($arguments);
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
    You are running $php task queue command. For help type php task clear --help.'."\n\033[0m\n";
    }

    /**
     * Execute command
     * 
     * @return void
     */
    public function run()
    {
        if ($this->parser->argument('help')) {
            return $this->help();
        }
        /**
         * Clear File handler data
         */
        $files = array(
            trim($this->config['log']['file']['path']['http'], '/'),
            trim($this->config['log']['file']['path']['ajax'], '/'),
            trim($this->config['log']['file']['path']['cli'], '/'),
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
        $queue = $this->c->load('service/queue');
        $queue->deleteQueue($this->config['log']['queue']['route']);
        
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

        echo "\33[0;36m".''."\33[1;36m".'Help:'."\33[0m\33[0;36m\n\033[0m\n";

        echo "\n\33[1;36mUsage:\33[0m\33[0;36m\n\nphp task clear\n\33[0m";
        echo "\n\33[1;36mDescription:\33[0m\33[0;36m\n\nClear all logs from .app/data/logs/ folder also deletes log data on the queue service.\n\n\33[0m\n";
    }


}

// END ClearController class

/* End of file ClearController.php */
/* Location: .Obullo/Cli/Controller/ClearController.php */