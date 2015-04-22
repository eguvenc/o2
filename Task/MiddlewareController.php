<?php

namespace Obullo\Task;

use Controller;
use Obullo\Cli\Parser;
use Obullo\Cli\Console;

/**
 * Middleware Controller
 * 
 * @category  Console
 * @package   Tasks
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
class MiddlewareController extends Controller
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
        echo Console::logo("Welcome to Middleware Manager (c) 2015");
        echo Console::description("Add / Remove middleware files. For more help type \$php task middleware help.");
    }

    /**
     * Add a new module
     *
     * @return void
     */
    public function add()
    {   
        $this->parser->parse(func_get_args());
        $name = $this->parser->argument('name');

        if (empty($name)) {
            echo Console::fail("Middleware name can't be empty.");
            return;
        }
        $middleware = ucfirst($name).'.php';
        $middlewareFile = OBULLO .'Application'. DS .'Middlewares'. DS .$middleware;

        if ( ! file_exists($middlewareFile)) {
            echo Console::fail("Middleware '$middleware' does not exists in Obullo/Task/Middlewares folder.");
            return;
        }
        if ( ! is_writable(static::getMiddlewarePath())) {
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
     * @return void
     */
    public function remove()
    {
        $this->parser->parse(func_get_args());
        $name = $this->parser->argument('name');

        if (empty($name)) {
            echo Console::fail("Middleware name can't be empty.");
            return;
        }
        $middleware = ucfirst($name).'.php';

        if ( ! file_exists(static::getMiddlewarePath())) {
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

echo Console::help("Help:\n\n", true);
echo Console::help(
"Available Commands

    add      : Add new middleware to .app/classes/Http/Middlewares/ directory.
    remove   : Remove middleware from .app/classes/Http/Middlewares/ directory.

Available Arguments

    --name   : Middleware name.\n\n");

echo Console::help("Usage:\n\n", true);
echo Console::help(
"php task middleware [command] --name=value

    php task middleware add --name=value 
    php task middleware remove --name=value\n\n");


echo Console::help("Description:\n\n", true);
echo Console::help("Add / remove middlewares to .app/classes/Http/Middlewares/ directory.\n\n");

    }

}

// END MiddlewareController class

/* End of file MiddlewareController.php */
/* Location: .Obullo/Task/MiddlewareController.php */