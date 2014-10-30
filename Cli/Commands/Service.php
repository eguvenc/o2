<?php

namespace Obullo\Cli\Commands;

/**
 * Service Command
 * 
 * @category  Cli
 * @package   Commands
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/Cli
 */
Class Service extends Route implements CommandInterface
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

// END Service class

/* End of file Service.php */
/* Location: .Obullo/Cli/Commands/Service.php */