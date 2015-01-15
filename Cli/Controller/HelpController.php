<?php

namespace Obullo\Cli\Controller;

use Controller;

/**
 * Help Controller
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/Cli
 */
Class HelpController extends Controller
{
    /**
     * Execute command
     * 
     * @return void
     */
    public function index()
    {
        echo "\33[1;36m".'
        ______  _            _  _
       |  __  || |__  _   _ | || | ____
       | |  | ||  _ || | | || || ||  _ |
       | |__| || |_||| |_| || || || |_||
       |______||____||_____||_||_||____|

        Welcome to Task Manager (c) 2014
You are running $php task help command. For more help type php task [command] --help.'."\n\033[0m\n";

echo "\33[1;36mAvailable commands:\33[0m\33[0;36m

log        : Follow the application log file.
clear      : Clear all log data.
queue      : Queue control functions.
route      : Web route config.env file update manager. ( System maintenance and config update functions )
service    : Service config.env file update manager. 
help       : See list all of available commands.\33[0m\n\n";

echo "\33[1;36mUsage:\33[0m\33[0;36m

php task [command] [arguments]\n\33[0m\n";

echo "\33[1;36mUsage help:\33[0m\33[0;36m

php task [command] --help\n\33[0m\n";

    }

}

// END HelpController class

/* End of file HelpController.php */
/* Location: .Obullo/Cli/Controller/HelpController.php */