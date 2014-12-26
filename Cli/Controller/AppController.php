<?php

namespace Obullo\Cli\Controller;

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
Class AppController implements CliInterface
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
    You are running $php task app command. For help type php task app --help.'."\n\033[0m\n";
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

        $this->config->env['web']['app'][$name]['maintenance'] = 'down';
        $this->config->write();

        $hostname = empty($this->config->env['web']['app'][$name]['label']) ? $name : $this->config->env['web']['app'][$name]['label'];

        echo "\33[1;31mApp \33[1;37m\33[41m$hostname\33[0m\33[1;31m down for maintenance.\33[0m\n";
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

        $this->config->env['web']['app'][$name]['maintenance'] = 'up';
        $this->config->write();

        $hostname = empty($this->config->env['web']['app'][$name]['label']) ? $name : $this->config->env['web']['app'][$name]['label'];

        echo "\33[1;32mApp \33[1;37m\33[42m$hostname\33[0m\33[1;32m up.\33[0m\n";
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
            echo "\33[1;36mApp \"--name\" can't be empty.\33[0m\n";
            exit;
        }
        if ( ! isset($this->config->env['web']['app'][$name])) {
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

    down       : Sets app down to enter maintenance mode.
    up         : Sets app up to leaving from maintenance mode.

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
/* Location: .Obullo/Cli/Controller/AppController.php */