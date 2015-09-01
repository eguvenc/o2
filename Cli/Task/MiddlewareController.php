<?php

namespace Obullo\Cli\Task;

use Controller;
use Obullo\Cli\Console;

/**
 * Middleware Controller
 * 
 * @category  Console
 * @package   Tasks
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/task
 */
class MiddlewareController extends Controller
{
    /**
     * Print Logo
     * 
     * @return string colorful logo
     */
    public function logo() 
    {
        echo Console::logo("Welcome to Middleware Manager (c) 2015");
        echo Console::description("Add / Remove middleware files. For more help type \$php task middleware help.");
    }

    /**
     * Add a new middleware
     *
     * @param string $name name
     * 
     * @return void
     */
    public function add($name = null)
    {   
        $name = (empty($name)) ? $this->cli->argument('name') : $name;

        if (empty($name)) {
            echo Console::fail("Middleware name can't be empty.");
            return;
        }
        $name = ucfirst($name);
        $middleware = $name.'.php';
        $middlewareFile = OBULLO .'Application'. DS .'Middlewares'. DS .$name. DS .$middleware;

        if (! file_exists($middlewareFile)) {
            echo Console::fail("Middleware #$middleware does not exists in Obullo/Middlewares folder.");
            return;
        }
        if (! is_writable(static::getMiddlewarePath())) {
            echo Console::fail("We could not create file in app/classes/Http/Middlewares folder please check your write permissions.");
            return;
        }
        $dest = static::getMiddlewarePath($middleware);
        copy($middlewareFile, $dest);
        chmod($dest, 0777);

        echo Console::success("New middleware #$middleware added successfully.");
    }

    /**
     * Remove 
     * 
     * @param string $name name
     * 
     * @return void
     */
    public function remove($name = null)
    {
        $name = (empty($name)) ? $this->cli->argument('name') : $name;

        if (empty($name)) {
            echo Console::fail("Middleware name can't be empty.");
            return;
        }
        $name = ucfirst($name);
        $middleware = $name.'.php';

        if (! file_exists(static::getMiddlewarePath())) {
            echo Console::fail("Middleware #$middleware does not exists in app/classes/Http/Middlewares folder.");
            return;
        }
        unlink(static::getMiddlewarePath($middleware));
        echo Console::success("Middleware #$middleware removed successfully.");
    }

    /**
     * Get middleware path
     *
     * @param string $middleware name
     * 
     * @return string
     */
    protected static function getMiddlewarePath($middleware = '')
    {
        return APP .'classes'. DS .'Http'. DS. 'Middlewares'. DS .$middleware;
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

    add      : Add new middleware to .app/classes/Http/Middlewares/ directory.
    remove   : Remove middleware from .app/classes/Http/Middlewares/ directory.

Available Arguments

    --name   : Middleware name.");
echo Console::newline(2);
echo Console::help("Usage:", true);
echo Console::newline(2);
echo Console::help(
"php task middleware [command] --name=value

    php task middleware add --name=value 
    php task middleware remove --name=value");
echo Console::newline(2);
echo Console::help("Description:", true);
echo Console::newline(2);
echo Console::help("Add / remove middlewares to .app/classes/Http/Middlewares/ directory.");
echo Console::newline(2);
    }

}