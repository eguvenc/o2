<?php

namespace Obullo\Cli\Controller;

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
        $this->c->load('cli/parser as parser');
        $this->c->load('service/logger');
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

        ______  _            _  _
       |  __  || |__  _   _ | || | ____
       | |  | ||  _ || | | || || ||  _ |
       | |__| || |_||| |_| || || || |_||
       |______||____||_____||_||_||____|

        Welcome to Log Manager v2.0 (c) 2015
You are displaying logs. For more help type $php task help.'."\n\033[0m";

    }

    /**
     * Log help
     * 
     * @return string
     */
    public function help()
    {
        echo "\33[0;36m".'
'."\33[1;36m".'Help:'."\33[0m\33[0;36m".'

Available Arguments

    --dir    : Sets log direction for reader. Directions : cli, ajax, http ( default )
    --table  : Collection name if mongo driver used otherwise database table name.'."\n\033[0m";

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
/* Location: .Obullo/Cli/Controller/LogController.php */