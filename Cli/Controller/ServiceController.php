<?php

namespace Obullo\Cli\Controller;

/**
 * Service Controller
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/cli
 */
Class ServiceController extends RouteController implements CliInterface
{
    /**
     * Execute command
     * 
     * @return boolean
     */
    public function run()
    {
        $service = $this->parser->segment(0);
        $command = $this->parser->segment(1);

        if ( ! isset($this->config->xml()->service->{$service})) {
            $serviceName = (isset($this->config->xml()->service->{$service}->attributes()->label)) ? $this->config->xml()->service->{$service}->attributes()->label : $service;
            echo "\33[1;31m\33[1;37m\33[41m".ucfirst($serviceName)."\33[0m\33[1;31m must be defined in your xml config <service></service> tags.\33[0m\n";
            die;
        }
        
        switch ($command) {
        case 'down':
            $this->down($service, 'service');
            break;
        case 'up':
            $this->up($service, 'service');
            break;
        default:
            $this->help();
            break;
        }
        return true;
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

// END ServiceController class

/* End of file ServiceController.php */
/* Location: .Obullo/Cli/Controller/ServiceController.php */