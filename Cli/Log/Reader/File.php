<?php

namespace Obullo\Cli\Log\Reader;

use Obullo\Cli\Log\Printer\Colorful;

/**
 * File Reader
 * 
 * @category  Cli
 * @package   Log
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/Cli
 */
Class File
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
    public function follow($c, $dir = 'http', $table = null)
    {
        $table = null; // unused variable

        $path = str_replace('/', DS, trim($c['config']['log']['file']['path'][$dir], '/'));
        $file = $path;
        if (strpos($path, 'data') === 0) {  // Replace "data" word to application data path
            $file = str_replace('data', DS . trim(DATA, DS), $path);
        }
        echo "\n\33[1;36mFollowing File Handler ".ucfirst($dir)." Logs ...\33[0m\n";

        $size = 0;
        while (true) {
            clearstatcache(); // Clear the cache
            if ( ! file_exists($file)) { // Start process when file exists.
                continue;
            }
            $currentSize = filesize($file); // Continue the process when file size change.
            if ($size == $currentSize) {
                usleep(50);
                continue;
            }
            if ( ! $fh = fopen($file, 'rb')) {
                echo("\n\n\033[1;31mPermission Error: You need to have root access or log folder has not got write permission.\033[0m\n");
                die;
            }
            fseek($fh, $size);
            $printer = new Colorful;
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
/* Location: .Obullo/Cli/Log/Reader/File.php */