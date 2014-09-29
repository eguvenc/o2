<?php

namespace Obullo\Console\Commands;

/**
 * Help Command
 * 
 * @category  Console
 * @package   Commands
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/console
 */
Class Help implements CommandInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Execute command
     * 
     * @return boolean
     */
    public function run()
    {
        echo "\33[1;36m".'
        ______  _            _  _
       |  __  || |__  _   _ | || | ____
       | |  | ||  _ || | | || || ||  _ |
       | |__| || |_||| |_| || || || |_||
       |______||____||_____||_||_||____|

        Welcome to Task Manager (c) 2014
You are running $php task help command which is located in app / tasks folder.'."\n\033[0m\n";

echo "\33[1;36mAvailable commands:\33[0m\33[0;36m
log        : Follow the application log file.
clear      : Clear application log data.
update     : Update your Obullo version.
queue      : Queue control functions.
host       : Create a virtual host file for apache2.
help       : See list all of available commands.\33[0m\n\n";

echo "\33[1;36mUsage:\33[0m\33[0;36m
php task [command] [arguments]\n\33[0m\n";

        return true;
    }

}

// END Help class

/* End of file Help.php */
/* Location: .Obullo/Console/Commands/Help.php */