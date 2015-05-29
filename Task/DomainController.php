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
     * @param string $name domain
     * 
     * @return void
     */
    public function down($name = null)
    {
        $name = (empty($name)) ? $this->cli->argument('name', null) : $name;
        $this->isEmpty($name);

        $newArray = $this->config['domain'];
        $newArray[$name]['maintenance'] = 'down';

        $this->config->write('domain.php', $newArray);

        echo Console::fail("Domain ".Console::foreground($name, 'red')." down for maintenance.");
    }

    /**
     * Leave from maintenance mode
     *
     * @param string $name domain
     * 
     * @return void
     */
    public function up($name = null)
    {
        $name = (empty($name)) ? $this->cli->argument('name', null) : $name;
        $this->isEmpty($name);

        $newArray = $this->config['domain'];
        $newArray[$name]['maintenance'] = 'up';

        $this->config->write('domain.php', $newArray);

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

echo Console::help("Help:", true);
echo Console::newline(1);
echo Console::help("
Available Commands

    down     : Sets domain down to enter maintenance mode.
    up       : Sets domain up to leaving from maintenance mode.

Available Arguments

    --name   : Sets domain name."
);
echo Console::newline(2);
echo Console::help("Usage:", true);
echo Console::newline(2);
echo Console::help("php task domain [command] name");
echo Console::newline(2);
echo Console::help("Description:", true);
echo Console::newline(2);
echo Console::help("Manages domain features which are defined in your domain.php config file.");
echo Console::newline(2);
    }
}

// END DomainController class

/* End of file DomainController.php */
/* Location: .Obullo/Task/DomainController.php */