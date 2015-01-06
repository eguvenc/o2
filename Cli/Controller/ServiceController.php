<?php

namespace Obullo\Cli\Controller;

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
Class ServiceController implements CliInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Cli parser
     * 
     * @var object
     */
    protected $parser;

    /**
     * Config
     * 
     * @var object
     */
    protected $config;

    /**
     * Constructor
     *
     * @param object $c         container
     * @param array  $arguments $arguments
     */
    public function __construct($c, array $arguments = array())
    {
        $this->c = $c;
        $this->config = $c['config'];

        $this->parser = $c->load('cli/parser');
        $this->parser->parse($arguments);
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

            Welcome to Task Manager (c) 2014
    You are running $php task service command. For help type php task service --help.'."\n\033[0m\n";
    }

    /**
     * Execute command
     * 
     * @return boolean
     */
    public function run()
    {
        if ($this->parser->argument('help')) {
            return $this->help();
        }
        $name = $this->parser->argument('name', null);
        $command = $this->parser->segment(0);

        switch ($command) {
        case 'down':
            $this->down($name);
            break;
        case 'up':
            $this->up($name);
            break;
        default:
            $this->help();
            break;
        }
        return true;
    }

    /**
     * Enter the maintenance mode
     *
     * @param string $name app key ( like : site, support, sports, shop )
     * 
     * @return void
     */
    public function down($name)
    {
        $this->emptyControl($name);

        $this->config->env['service']['app'][$name]['maintenance'] = 'down';
        $this->config->write();

        $label = empty($this->config->env['service']['app'][$name]['label']) ? $name : $this->config->env['service']['app'][$name]['label'];

        echo "\33[1;31mService \33[1;37m\33[41m$label\33[0m\33[1;31m down for maintenance.\33[0m\n";
    }

    /**
     * Leave from maintenance mode
     *
     * @param string $name route key ( like : site, support, sports, shop )
     * 
     * @return void
     */
    public function up($name)
    {
        $this->emptyControl($name);

        $this->config->env['service']['app'][$name]['maintenance'] = 'up';
        $this->config->write();

        $label = empty($this->config->env['service']['app'][$name]['label']) ? $name : $this->config->env['service']['app'][$name]['label'];

        echo "\33[1;32mService \33[1;37m\33[42m$label\33[0m\33[1;32m up.\33[0m\n";
    }

    /**
     * Check --name is empty
     * 
     * @param string $name route name
     * 
     * @return void
     */
    protected function emptyControl($name)
    {
        if (empty($name)) {
            echo "\33[1;36mService \"--name\" can't be empty.\33[0m\n";
            exit;
        }
        if ( ! isset($this->config->env['service']['app'][$name])) {
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

    --name   : Sets service name.'."\n\033[0m\n";

echo "\33[1;36mUsage:\33[0m\33[0;36m

php task service down --name=queue\n\n";

echo "\33[1;36mDescription:\33[0m\33[0;36m

Manages service features which are defined in your config.env file.
\n\33[0m\n";

    }
}

// END ServiceController class

/* End of file ServiceController.php */
/* Location: .Obullo/Cli/Controller/ServiceController.php */