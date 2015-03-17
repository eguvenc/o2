<?php

namespace Obullo\Log\Console\Printer;

/**
 * Log Colorful Printer
 * 
 * @category  Log
 * @package   Console
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      https://wiki.archlinux.org/index.php/Color_Bash_Prompt
 * @link      http://obullo.com/package/cli
 */
Class Colorful
{
    /**
     * Print current line
     * 
     * @param integer $i    line number
     * @param string  $line line text
     * 
     * @return void
     */
    public function printLine($i, $line)
    {
        if ($i == 0) {
            $line = str_replace("\n", '', $line);
        }
        $line = trim(preg_replace('/[\r\n]/', "\n", $line), "\n"); // Remove all newlines   
        $out  = explode('.', $line);
        if (isset($out[1])) {
            $messageBody = $out[1];
        }
        if (isset($messageBody)) {
            $line = $this->writeSQL($messageBody, $line);
            $line = $this->writeHeader($messageBody, $line);
            $line = $this->writeBody($messageBody, $line);

            $this->writeFinalOutput($messageBody, $line);
            $this->writeLevels($messageBody, $line);
        }

    }

    /**
     * Write header
     * 
     * @param string $messageBody text
     * @param string $line        line
     * 
     * @return string line
     */
    protected function writeHeader($messageBody, $line)
    {
        $break = "\n------------------------------------------------------------------------------------------";
        if (strpos($messageBody, '$_') !== false) {
            $line = preg_replace('/\s+/', ' ', $line);
            $line = preg_replace('/\[/', "[", $line);  // Do some cleaning

            if (strpos($messageBody, '$_REQUEST_URI') !== false) {
                $line  = "\033[0;37m".$break."\n".$line.$break."\033[0m";
            } elseif (strpos($messageBody, '$_LAYER') !== false) {
                $line = "\033[0;37m".strip_tags($line)."\033[0m";
            } else {
                $line = "\033[0;37m".$line."\033[0m";
            }
        }
        return $line;
    }

    /**
     * Write header
     * 
     * @param string $messageBody text
     * @param string $line        line
     * 
     * @return string line
     */
    protected function writeBody($messageBody, $line)
    {        
        if (strpos($messageBody, '$_TASK') !== false) {
            $line = "\033[0;37m".$line."\033[0m";
        }
        if (strpos($messageBody, 'loaded:') !== false) {
            $line = "\033[0;37m".$line."\033[0m";
        }
        return $line;
    }

    /**
     * Write sql
     * 
     * @param string $messageBody text
     * @param string $line        line
     * 
     * @return string line
     */
    protected function writeSQL($messageBody, $line)
    {
        if (strpos($messageBody, '$_SQL') !== false) {   // Remove unnecessary spaces from sql output
            $line = "\033[1;32m".preg_replace('/[\s]+/', ' ', $line)."\033[0m";
            $line = preg_replace('/[\r\n]/', "\n", $line);
        }
        return $line;
    }

    /**
     * Write final response info
     * 
     * @param string $messageBody text
     * @param string $line        line
     * 
     * @return void
     */
    protected function writeFinalOutput($messageBody, $line)
    {
        if (strpos($messageBody, 'debug') !== false) {   // Do not write two times
            if (strpos($messageBody, '--> Final output sent') !== false) {
                $line = "\033[0m"."\033[0;37m".$line."\033[0m";
            }
            if (strpos($messageBody, '--> Header redirect') !== false) {
                $line = "\033[0m"."\033[0;35m".$line."\033[0m";
            }
            $line = "\033[0;37m".$line."\033[0m";
            echo $line."\n";
        }
    }

    /**
     * Write log levels
     * 
     * @param string $messageBody text
     * @param string $line        line
     * 
     * @return void
     */
    protected function writeLevels($messageBody, $line)
    {
        if (strpos($messageBody, 'info') !== false) {
            $line = "\033[1;33m".$line."\033[0m";
            echo $line."\n";
        } elseif (strpos($messageBody, 'error') !== false) {
            $line = "\033[1;31m".$line."\033[0m";
            echo $line."\n";
        } elseif (strpos($messageBody, 'alert') !== false) {
            $line = "\033[1;31m".$line."\033[0m";
            echo $line."\n";
        } elseif (strpos($messageBody, 'emergency') !== false) {
            $line = "\033[1;31m".$line."\033[0m";
            echo $line."\n";
        } elseif (strpos($messageBody, 'critical') !== false) {
            $line = "\033[1;31m".$line."\033[0m";
            echo $line."\n";
        } elseif (strpos($messageBody, 'warning') !== false) {
            $line = "\033[1;31m".$line."\033[0m";
            echo $line."\n";
        } elseif (strpos($messageBody, 'notice') !== false) {
            $line = "\033[1;33m".$line."\033[0m";
            echo $line."\n";
        }
    }


}

// Terminal Colour Codes.
/*
$BLACK="33[0;30m";
$DARKGRAY="33[1;30m";
$BLUE="33[0;34m";
$LIGHTBLUE="33[1;34m";
$MAGENTA="33[0;35m";
$CYAN="33[0;36m";
$LIGHTCYAN="33[1;36m";
$RED="33[0;31m";
$LIGHTRED="33[1;31m";
$GREEN="33[0;32m";
$LIGHTGREEN="33[1;32m";
$PURPLE="33[0;35m";
$LIGHTPURPLE="33[1;35m";
$BROWN="33[0;33m";
$YELLOW="33[1;33m";
$LIGHTGRAY="33[0;37m";
$WHITE="33[1;37m";
*/

// END Colorful class

/* End of file Colorful.php */
/* Location: .Obullo/Log/Console/Printer/Colorful.php */