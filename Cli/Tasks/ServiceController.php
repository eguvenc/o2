<?php

namespace Obullo\Cli\Tasks;

use Controller;

/**
 * Service Controller
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
Class ServiceController extends Controller
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
    You are running $php task service command. For help type php task service --help.'."\n\033[0m\n";
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

        $this->config->env['service'][$name]['maintenance'] = 'down';
        $this->config->write();

        $label = empty($this->config->env['service'][$name]['label']) ? $name : $this->config->env['service'][$name]['label'];

        echo "\33[1;31mService \33[1;37m\33[41m$label\33[0m\33[1;31m down for maintenance.\33[0m\n";
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

        $this->config->env['service'][$name]['maintenance'] = 'up';
        $this->config->write();

        $label = empty($this->config->env['service'][$name]['label']) ? $name : $this->config->env['service'][$name]['label'];

        echo "\33[1;32mService \33[1;37m\33[42m$label\33[0m\33[1;32m up.\33[0m\n";
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
            echo "\33[1;36mService \"--name\" can't be empty.\33[0m\n";
            exit;
        }
        if ( ! isset($this->config->env['service'][$name])) {
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

    down       : Sets service down to enter maintenance mode.
    up         : Sets service up to leaving from maintenance mode.
    pause      : Sets service pause ( New requests stop but background jobs continue. ).

Available Arguments

    --name     : Sets service name.'."\n\033[0m\n";

echo "\33[1;36mUsage:\33[0m\33[0;36m

php task service down --name=queue\n\n";

echo "\33[1;36mDescription:\33[0m\33[0;36m

Manages service features which are defined in your config.env file.
\n\33[0m\n";

    }
}

// END ServiceController class

/* End of file ServiceController.php */
/* Location: .Obullo/Cli/Tasks/ServiceController.php */