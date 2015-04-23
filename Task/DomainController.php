<?php

namespace Obullo\Task;

use Controller;
use Obullo\Cli\Console;

/**
 * Domain Controller
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
class DomainController extends Controller
{
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
        echo Console::logo("Welcome to Domain Manager (c) 2015");
        echo Console::description("You are running \$php task domain command. For help type php task domain help");
    }

    /**
     * Enter the maintenance mode
     * 
     * @return void
     */
    public function down()
    {
        $name = $this->cli->argument('name', null);
        $this->isEmpty($name);

        $this->config->array['domain'][$name]['maintenance'] = 'down';
        $this->config->write(APP .'config'. DS . 'env'. DS .$this->c['app']->env() . DS .'domain.php', $this->config['domain']);

        $this->c['logger']->debug('php task domain down --name='.$name);

        echo Console::fail("Domain ".Console::foreground($name, 'red')." down for maintenance.");
    }

    /**
     * Leave from maintenance mode
     *
     * @return void
     */
    public function up()
    {
        $name = $this->cli->argument('name', null);
        $this->isEmpty($name);

        $this->config->array['domain'][$name]['maintenance'] = 'up';
        $this->config->write(APP .'config'. DS . 'env'. DS . $this->c['app']->env() . DS .'domain.php', $this->config['domain']);

        $this->c['logger']->debug('php task domain up --name='.$name);

        echo Console::success("Domain ".Console::foreground($name, 'green')." up.");
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
            echo Console::fail('Domain "--name" can\'t be empty.');
            exit;
        }
        if ( ! isset($this->config['domain'][$name])) {
            echo Console::fail('Domain name "'.ucfirst($name).'" must be defined in your domain.php config file.');
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

echo Console::help("Help:\n", true);
echo Console::help("
Available Commands

    down     : Sets domain down to enter maintenance mode.
    up       : Sets domain up to leaving from maintenance mode.

Available Arguments

    --name   : Sets domain name.\n\n"
);

echo Console::help("Usage:\n\n", true);
echo Console::help("php task domain [command] --name=site\n\n");
echo Console::help("Description:\n\n", true);
echo Console::help("Manages domain features which are defined in your domain.php config file.\n\n");

    }
}

// END DomainController class

/* End of file DomainController.php */
/* Location: .Obullo/Task/DomainController.php */