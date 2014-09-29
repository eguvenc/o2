<?php

namespace Obullo\Console\Commands;

/**
 * Service Command
 *
 * Manage "data/globals/config.xml" <service></service> item.
 * 
 * @category  Console
 * @package   Commands
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/console
 */
Class Service extends App implements CommandInterface
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

        if ( ! isset($this->config->xml->service->{$service})) {
            $serviceName = (isset($this->config->xml->service->{$service}->name)) ? $this->config->xml->service->{$service}->name : $service;
            echo "\33[1;31m\33[1;37m\33[41m".ucfirst($serviceName)."\33[0m\33[1;31m service is not defined in your xml config file.\33[0m\n";
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
     * Console help
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
/* Location: .Obullo/Console/Commands/Service.php */