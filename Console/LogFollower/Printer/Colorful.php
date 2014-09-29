<?php

namespace Obullo\Console\LogFollower\Printer;

/**
 * LogFollower Colorful Printer
 * 
 * @category  Console
 * @package   LogFollower
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      https://wiki.archlinux.org/index.php/Color_Bash_Prompt
 * @link      http://obullo.com/package/console
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
        $break = "\n------------------------------------------------------------------------------------------";
        if ($i == 0) {
            $line = str_replace("\n", '', $line);
        }
        $line = trim(preg_replace('/[\r\n]/', "\n", $line), "\n"); // Remove all newlines   
        $out  = explode('.', $line);
        if (isset($out[1])) {
            $messageBody = $out[1];
        }
        if (isset($messageBody)) {
            if (strpos($messageBody, '$_SQL') !== false) {   // Remove unnecessary spaces from sql output
                $line = "\033[1;32m".preg_replace('/[\s]+/', ' ', $line)."\033[0m";
                $line = preg_replace('/[\r\n]/', "\n", $line);
            }
            if (strpos($messageBody, '$_') !== false) {
                $line = preg_replace('/\s+/', ' ', $line);
                $line = preg_replace('/\[/', "[", $line);  // Do some cleaning

                if (strpos($messageBody, '$_REQUEST_URI') !== false) {
                    $line  = "\033[1;36m".$break."\n".$line.$break."\n\033[0m";
                } elseif (strpos($messageBody, '$_LAYER') !== false) {
                    $line = "\033[1;34m".strip_tags($line)."\033[0m";
                } else {
                    $line = "\033[1;35m".$line."\033[0m";
                }
            }
            if (strpos($messageBody, '$_TASK') !== false) {
                $line = "\033[1;34m".$line."\033[0m";
            }
            if (strpos($messageBody, 'loaded:') !== false) {
                $line = "\033[0;35m".$line."\033[0m";
            }
            if (strpos($messageBody, 'debug') !== false) {   // Do not write two times
                if (strpos($messageBody, 'Final output sent to browser') !== false) {
                    $line = "\033[1;36m".$line."\033[0m";
                }
                $line = "\033[0;35m".$line."\033[0m";
                if ( ! isset($lines[$i])) {
                    echo $line."\n";
                }
            }
            if (strpos($messageBody, 'info') !== false) {
                $line = "\033[1;33m".$line."\033[0m";
                if ( ! isset($lines[$line])) {
                    echo $line."\n";
                }
            }
            if (strpos($messageBody, 'error') !== false) {
                $line = "\033[1;31m".$line."\033[0m";
                if ( ! isset($lines[$line])) {
                    echo $line."\n";
                }
            }
            if (strpos($messageBody, 'alert') !== false) {
                $line = "\033[1;31m".$line."\033[0m";
                if ( ! isset($lines[$line])) {
                    echo $line."\n";
                }
            }
            if (strpos($messageBody, 'emergency') !== false) {
                $line = "\033[1;31m".$line."\033[0m";
                if ( ! isset($lines[$line])) {
                    echo $line."\n";
                }
            }
            if (strpos($messageBody, 'critical') !== false) {
                $line = "\033[1;31m".$line."\033[0m";
                if ( ! isset($lines[$line])) {
                    echo $line."\n";
                }
            }
            if (strpos($messageBody, 'warning') !== false) {
                $line = "\033[1;31m".$line."\033[0m";
                if ( ! isset($lines[$line])) {
                    echo $line."\n";
                }
            }
            if (strpos($messageBody, 'notice') !== false) {
                $line = "\033[1;35m".$line."\033[0m";
                if ( ! isset($lines[$line])) {
                    echo $line."\n";
                }
            }
        }

    } // end function

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
/* Location: .Obullo/Console/LogFollower/Printer/Colorful.php */