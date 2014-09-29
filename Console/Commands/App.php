<?php

namespace Obullo\Console\Commands;

/**
 * App Command
 *
 * Manages "data/globals/config.xml" <app></app> item.
 * 
 * @category  Console
 * @package   Commands
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/console
 */
Class App implements CommandInterface
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
        $this->parser = $c->load('console/parser');
        $this->parser->parse($arguments);
    }

    /**
     * Execute command
     * 
     * @return boolean
     */
    public function run()
    {
        $app = $this->parser->segment(0);
        $command = $this->parser->segment(1);

        if ( ! isset($this->config->xml->app->{$app})) {
            $appName = (isset($this->config->xml->app->{$app}->label)) ? $this->config->xml->app->{$app}->label : $app;
            echo "\33[1;31m\33[1;37m\33[41m".ucfirst($appName)."\33[0m\33[1;31m application is not defined in your xml config file.\33[0m\n";
            die;
        }
        switch ($command) {
        case 'down':
            $this->down($app, 'app');
            break;
        case 'up':
            $this->up($app, 'app');
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
     * @param string $app       app key ( like : site, support, sports, shop )
     * @param string $direction app or service
     * 
     * @return void
     */
    public function down($app, $direction = 'app')
    {
        $this->config->xml->{$direction}->{$app}->maintenance = 'down';
        $this->config->save($this->config->xml->asXML());
        $appName = (isset($this->config->xml->{$direction}->{$app}->label)) ? $this->config->xml->{$direction}->{$app}->label : $app;

        echo "\33[1;31mApplication \33[1;37m\33[41m$appName\33[0m\33[1;31m down for maintenance.\33[0m\n";
    }

    /**
     * Disable maintenance mode
     *
     * @param string $app       app key ( like : site, support, sports, shop )
     * @param string $direction app or service
     * 
     * @return void
     */
    public function up($app, $direction = 'app')
    {
        $this->config->xml->{$direction}->{$app}->maintenance = 'up';
        $this->config->save($this->config->xml->asXML());
        $appName = (isset($this->config->xml->{$direction}->{$app}->label)) ? $this->config->xml->{$direction}->{$app}->label : $app;

        echo "\33[1;32mApplication \33[1;37m\33[42m$appName\33[0m\33[1;32m up.\33[0m\n";
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
     * Console help
     * 
     * @return void
     */
    public function help()
    {
        // ...
    }
}

// END App class

/* End of file App.php */
/* Location: .Obullo/Console/Commands/App.php */