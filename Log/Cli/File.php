<?php

namespace Obullo\Log\Cli;

use Obullo\Container\ContainerInterface;

/**
 * File Reader
 * 
 * @category  Log
 * @package   Console
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/Cli
 */
class File
{
    /**
     * Follow logs
     * 
     * @param string $c     container
     * @param string $dir   sections ( http, ajax, cli )
     * @param string $table tablename
     * 
     * @return void
     */
    public function follow(ContainerInterface $c, $dir = 'http', $table = null)
    {
        $c['config']->load('logger');

        $table = null; // Unused variable
        if (! isset($c['config']['logger']['file']['path'][$dir])) {
            echo("\n\n\033[1;31mPath Error: $dir item not found in ['config']['logger']['file']['path'][$dir] array.\033[0m\n");
            exit;
        }
        $path = str_replace('/', DS, trim($c['config']['logger']['file']['path'][$dir], '/'));
        $file = $path;
        if (strpos($path, 'data') === 0) {  // Replace "data" word to application data path
            $file = str_replace('data', DS . trim(DATA, DS), $path);
        }
        echo "\n\33[0;37mFollowing File Handler ".ucfirst($dir)." Logs ...\33[0m\n";

        $size = 0;
        while (true) {
            clearstatcache();           // Clear the cache
            if (! file_exists($file)) { // Start process when file exists.
                continue;
            }
            $currentSize = filesize($file); // Continue the process when file size change.
            if ($size == $currentSize) {
                usleep(50);
                continue;
            }
            if (! $fh = fopen($file, 'rb')) {
                echo("\n\n\033[1;31mPermission Error: You need to have root access or log folder has not got write permission.\033[0m\n");
                die;
            }
            fseek($fh, $size);
            $printer = new Printer;
            $i = 0;
            while ($line = fgets($fh)) {
                $printer->printLine($i, $line);
                $i++;
            }
            fclose($fh);
            clearstatcache();
            $size = $currentSize;
        }

    }

}

// END File class

/* End of file File.php */
/* Location: .Obullo/Log/Cli/File.php */