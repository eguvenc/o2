<?php

namespace Obullo\Cli\Controller;

/**
 * Route Controller
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
Class RouteController implements CliInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Logger
     * 
     * @var object
     */
    public $logger;

    /**
     * Cli parser
     * 
     * @var object
     */
    public $parser;

    /**
     * Config
     * 
     * @var object
     */
    public $config;

    /**
     * Constructor
     *
     * @param object $c         container
     * @param array  $arguments $arguments
     */
    public function __construct($c, array $arguments = array())
    {
        $this->c = $c;
        $this->config = $this->c->load('config');
        $this->logger = $c->load('service/logger');
        $this->parser = $c->load('cli/parser');
        $this->parser->parse($arguments);
    }

    /**
     * Execute command
     * 
     * @return boolean
     */
    public function run()
    {
        $name = $this->parser->segment(0);
        $command = $this->parser->segment(1);

        if ( ! isset($this->config->xml()->route->{$name})) {
            $route = (isset($this->config->xml()->route->{$name}->attributes()->label)) ? $this->config->xml()->route->{$name}->attributes()->label : $name;
            echo "\33[1;31m\33[1;37m\33[41m".ucfirst($route)."\33[0m\33[1;31m must be defined in your xml config <route></route> tags.\33[0m\n";
            die;
        }
        switch ($command) {
        case 'down':
            $this->down($name, 'route');
            break;
        case 'up':
            $this->up($name, 'route');
            break;
        case 'update':
            $this->update();
            break;
        default:
            $this->help();
            break;
        }
        return true;
    }

    /**
     * Enable maintenance mode
     *
     * @param string $name      app key ( like : site, support, sports, shop )
     * @param string $direction host or service
     * 
     * @return void
     */
    public function down($name, $direction = 'route')
    {
        $this->config->xml()->{$direction}->{$name}->attributes()->maintenance = 'down';
        $this->config->save();

        $hostname = (isset($this->config->xml()->{$direction}->{$name}->attributes()->label)) ? $this->config->xml()->{$direction}->{$name}->attributes()->label : $name;

        echo "\33[1;31mHost \33[1;37m\33[41m$hostname\33[0m\33[1;31m down for maintenance.\33[0m\n";
    }

    /**
     * Disable maintenance mode
     *
     * @param string $name      app key ( like : site, support, sports, shop )
     * @param string $direction route or service
     * 
     * @return void
     */
    public function up($name, $direction = 'route')
    {
        $this->config->xml()->{$direction}->{$name}->attributes()->maintenance = 'up';
        $this->config->save();

        $hostname = (isset($this->config->xml()->{$direction}->{$name}->attributes()->label)) ? $this->config->xml()->{$direction}->{$name}->attributes()->label : $name;

        echo "\33[1;32mHost \33[1;37m\33[42m$hostname\33[0m\33[1;32m up.\33[0m\n";
    }

    /**
     * Update Obullo Core
     * 
     * @return void
     */
    public function update()
    {
        echo "\33[1;31mUpdate function not implemented yet.\33[0m\n";
    }

    /**
     * Cli help
     * 
     * @return void
     */
    public function help()
    {
        // ...
    }
}

// END RouteController class

/* End of file RouteController.php */
/* Location: .Obullo/Cli/Controller/RouteController.php */