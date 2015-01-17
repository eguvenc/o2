<?php

namespace Obullo\Cli\Tasks;

use Controller;

/**
 * App Controller
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
Class AppController extends Controller
{
    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->c->load('cli/parser as parser');
    }

    /**
     * Execute command
     * 
     * @return boolean
     */
    public function index()
    {
        $this->logo();
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
            ______  _            _  _
           |  __  || |__  _   _ | || | ____
           | |  | ||  _ || | | || || ||  _ |
           | |__| || |_||| |_| || || || |_||
           |______||____||_____||_||_||____|

            Welcome to Task Manager (c) 2015
    You are running $php task app command. For help type php task app --help.'."\n\033[0m\n";
    }

    /**
     * Enter the maintenance mode
     * 
     * @return void
     */
    public function down()
    {
        $this->parser->parse(func_get_args());
        $name = $this->parser->argument('name', null);
        $this->isEmpty($name);

        $this->config->env['application'][$name]['maintenance'] = 'down';
        $this->config->write();

        $hostname = empty($this->config->env['application'][$name]['label']) ? $name : $this->config->env['application'][$name]['label'];

        echo "\33[1;31mApp \33[1;37m\33[41m$hostname\33[0m\33[1;31m down for maintenance.\33[0m\n";
    }

    /**
     * Leave from maintenance mode
     *
     * @return void
     */
    public function up()
    {
        $this->parser->parse(func_get_args());
        $name = $this->parser->argument('name', null);
        $this->isEmpty($name);

        $this->config->env['application'][$name]['maintenance'] = 'up';
        $this->config->write();

        $hostname = empty($this->config->env['application'][$name]['label']) ? $name : $this->config->env['application'][$name]['label'];

        echo "\33[1;32mApp \33[1;37m\33[42m$hostname\33[0m\33[1;32m up.\33[0m\n";
    }

    /**
     * Check --name is empty
     * 
     * @param string $name route name
     * 
     * @return void
     */
    protected function isEmpty($name)
    {
        if (empty($name)) {
            echo "\33[1;36mApp \"--name\" can't be empty.\33[0m\n";
            exit;
        }
        if ( ! isset($this->config->env['application'][$name])) {
            echo "\33[1;31m\33[1;37m\33[41m".ucfirst($name)."\33[0m\33[1;31m must be defined in your config.env file\33[0m\n";
            die;
        }
    }

    /**
     * Cli help
     * 
     * @return void
     */
    public function help()
    {
        $this->logo();

        echo "\33[0;36m".'
'."\33[1;36m".'Help:'."\33[0m\33[0;36m".'

Available Commands

    down     : Sets app down to enter maintenance mode.
    up       : Sets app up to leaving from maintenance mode.

Available Arguments

    --name   : Sets web app name.'."\n\033[0m\n";

echo "\33[1;36mUsage:\33[0m\33[0;36m

php task app down --name=site\n\n";

echo "\33[1;36mDescription:\33[0m\33[0;36m

Manages application features which are defined in your config.env file.
\n\33[0m\n";

    }
}

// END AppController class

/* End of file AppController.php */
/* Location: .Obullo/Cli/Tasks/AppController.php */