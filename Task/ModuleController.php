<?php

namespace Obullo\Task;

use Controller;
use Obullo\Cli\Parser;
use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Obullo\Task\Helper\Console;

/**
 * Module Controller
 * 
 * @category  Console
 * @package   Tasks
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
class ModuleController extends Controller
{
    /**
     * Loader
     *
     * @return void
     */
    public function load()
    {
        $this->parser = new Parser($this->c);
        $this->c['logger'];
    }

    /**
     * Print Logo
     * 
     * @return string colorful logo
     */
    public function logo() 
    {
        echo Console::logo("Welcome to Module Manager (c) 2015");
        echo Console::description("Add / Remove modules. For more help type \$php task module help.");
    }

    /**
     * Add a new module
     *
     * @return void
     */
    public function add()
    {   
        $this->parser->parse(func_get_args());
        $module = $this->parser->argument('name');

        if (empty($module)) {
            echo Console::fail("Module name can't be empty.");
            return;
        }
        $moduleFolder = OBULLO .'Task'. DS .'Modules'. DS .$module;

        if ( ! is_dir($moduleFolder)) {
            echo Console::fail("Module '$module' does not exists in Obullo/Task/Modules folder.");
            return;
        }
        if ( ! is_writable(MODULES)) {
            echo Console::fail("We could not create directory in modules folder please check your write permissions.");
            return;
        }
        if (is_dir($moduleFolder. DS .'Controllers')) {
            $this->recursiveCopy($moduleFolder. DS .'Controllers', MODULES .$module);
        }
        if (is_dir($moduleFolder. DS .'Tasks')) {
            $this->recursiveCopy($moduleFolder. DS .'Tasks', MODULES .'tasks'. DS .$module);
        }
        echo Console::success("New module '".$module."' added successfully.");
    }

    /**
     * Remove 
     * 
     * @return void
     */
    public function remove()
    {
        $this->parser->parse(func_get_args());
        $module = $this->parser->argument('name');

        if (empty($module)) {
            echo Console::fail("Module name can't be empty.");
            return;
        }
        $moduleFolder = OBULLO .'Task'. DS .'Modules'. DS .$module;

        if ( ! is_writable(MODULES)) {
            echo Console::fail("We could not remove directories in modules folder please check write permissions.");
            return;
        }
        if ( ! is_dir($moduleFolder)) {
            echo Console::fail("Module '$module' does not exists in Obullo/Task/Modules folder.");
            return;
        }
        if (is_dir($moduleFolder. DS .'Controllers')) {
            $this->recursiveRemove(MODULES .$module);
        }
        if (is_dir($moduleFolder. DS .'Tasks')) {
            $this->recursiveRemove(MODULES .'tasks'. DS .$module);
        }
        echo Console::success("Module '".$module."' removed successfully.");
    }

    /**
     * Recursive copy
     * 
     * @param string $src source
     * @param string $dst destionation
     * 
     * @return void
     */
    protected function recursiveCopy($src, $dst)
    { 
        $dir = opendir($src); 
        @mkdir($dst); 
        while (false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file); 
                } 
            } 
        } 
        closedir($dir); 
    }

    /**
     * Remove directory and contents
     * 
     * @param string $dir full path of directory
     * 
     * @return void
     */
    protected function recursiveRemove($dir)
    {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }
        rmdir($dir);
    }

    /**
     * Log help
     * 
     * @return string
     */
    public function help()
    {
        $this->logo();

echo Console::help("Help:\n\n", true);
echo Console::help(
"Available Commands

    add      : Add new module to .modules/ directory.
    remove   : Remove module from .modules/ directory.

Available Arguments

    --name   : Module name.\n\n");

echo Console::help("Usage:\n\n", true);
echo Console::help(
"php task module [command] --name=value

    php task module add --name=value 
    php task module remove --name=value\n\n");


echo Console::help("Description:\n\n", true);
echo Console::help("Add / remove modules to modules directory.\n\n");

    }

}

// END ModuleController class

/* End of file ModuleController.php */
/* Location: .Obullo/Task/ModuleController.php */