<?php

namespace Obullo\Tasks;

use Controller;
use Obullo\Tasks\Helper\Console;

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
        echo Console::logo("Welcome to Task Manager (c) 2015");
        echo Console::description("You are running \$php task help command. For more help type php task [command] --help.");

echo Console::help("Available commands:\n\n", true);
echo Console::help("
log        : Follow the application log file.
log clear  : Clear all log data.
queue      : Queue control functions.
domain     : Domain maintenance control.
help       : See list all of available commands.\n\n"
);
echo Console::help("Usage:\n\n", true);
echo Console::help("php task [command] [arguments]\n\n");
echo Console::help("php task [command] --help\n\n\n");

    }

}

// END HelpController class

/* End of file HelpController.php */
/* Location: .Obullo/Tasks/HelpController.php */