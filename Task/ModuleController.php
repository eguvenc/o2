<?php

namespace Obullo\Task;

use Controller;
use Obullo\Cli\Console;
use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

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
     * @param string $module name
     * 
     * @return void
     */
    public function add($module = null)
    {   
        $module = (empty($module)) ? strtolower($this->cli->argument('name')) : $module;

        if (empty($module)) {
            echo Console::fail("Module name can't be empty.");
            return;
        }
        $moduleFolder = OBULLO .'Application'. DS .'Modules'. DS .$module;

        if (is_dir(MODULES .$module)) {
            echo Console::fail("Module #$module already exist in .modules/ folder.");
            return;
        }
        if ( ! is_dir($moduleFolder)) {
            echo Console::fail("Module #$module does not exist in Obullo/Task/Modules folder.");
            return;
        }
        if ( ! is_writable(MODULES)) {
            echo Console::fail("We could not create directory in modules folder please check your write permissions.");
            return;
        }
        if (is_dir($moduleFolder. DS .'controllers')) {
            $this->recursiveCopy($moduleFolder. DS .'controllers', MODULES .$module);
        }
        if (is_dir($moduleFolder. DS .'config')) {
            $this->recursiveCopy($moduleFolder. DS .'config', APP .'config'. DS .$module);
        }
        if (is_dir($moduleFolder. DS .'tasks')) {
            $this->recursiveCopy($moduleFolder. DS .'tasks', MODULES .'tasks'. DS .$module);
        }
        if (is_dir($moduleFolder. DS .'service')) {
            copy($moduleFolder. DS .'service'. DS .ucfirst($module).'.php', APP .'classes'. DS .'Service'. DS .ucfirst($module).'.php');
            chmod(APP .'classes'. DS .'Service'. DS .ucfirst($module).'.php', 0777);
        }
        echo Console::success("New module #$module added successfully.");
    }

    /**
     * Remove 
     *
     * @param string $module name
     * 
     * @return void
     */
    public function remove($module = null)
    {
        $module = (empty($module)) ? strtolower($this->cli->argument('name')) : $module;

        if (empty($module)) {
            echo Console::fail("Module name can't be empty.");
            return;
        }
        $moduleFolder = OBULLO .'Application'. DS .'Modules'. DS .$module;

        if ( ! is_dir(MODULES .$module)) {
            echo Console::fail("Module #$module already removed from .modules/ folder.");
            return;
        }
        if ( ! is_dir($moduleFolder)) {
            echo Console::fail("Module #$module does not exist in Obullo/Task/Modules folder.");
            return;
        }
        if ( ! is_writable(MODULES)) {
            echo Console::fail("We could not remove directories in modules folder please check write permissions.");
            return;
        }
        if (is_dir($moduleFolder. DS .'controllers') && is_dir(MODULES .$module)) {
            $this->recursiveRemove(MODULES .$module);
        }
        if (is_dir($moduleFolder. DS .'config') && is_dir(APP .'config'. DS .$module)) {
            $this->recursiveRemove(APP .'config'. DS .$module);
        }
        if (is_dir($moduleFolder. DS .'tasks') && is_dir(MODULES .'tasks'. DS .$module)) {
            $this->recursiveRemove(MODULES .'tasks'. DS .$module);
        }
        if (is_dir($moduleFolder. DS .'service') && is_file(APP .'classes'. DS .'Service'. DS .ucfirst($module).'.php')) {
            unlink(APP .'classes'. DS .'Service'. DS .ucfirst($module).'.php');
        }
        echo Console::success("Module #$module removed successfully.");
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
                if ( is_dir($src . DS . $file) ) {
                    $this->recursiveCopy($src . DS . $file, $dst . DS . $file);
                } else {
                    copy($src . DS . $file, $dst . DS . $file);
                    chmod($dst . DS . $file, 0777);
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

echo Console::help("Help:", true);
echo Console::newline(2);
echo Console::help(
"Available Commands

    add      : Add new module to .modules/ directory.
    remove   : Remove module from .modules/ directory.

Available Arguments

    --name   : Module name.");
echo Console::newline(2);
echo Console::help("Usage:", true);
echo Console::newline(2);
echo Console::help(
"php task module [command] name

    php task module add name
    php task module remove name");
echo Console::newline(2);
echo Console::help("Description:", true);
echo Console::newline(2);
echo Console::help("Add / remove modules to modules directory.");
echo Console::newline(2);
    }

}

// END ModuleController class

/* End of file ModuleController.php */
/* Location: .Obullo/Task/ModuleController.php */