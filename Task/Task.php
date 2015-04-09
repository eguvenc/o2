<?php

namespace Obullo\Task;

use Obullo\Log\Logger;

/**
 * Task Class
 * 
 * @category  Cli
 * @package   Task
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/task
 */
class Task
{
    /**
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->logger = $c['logger'];
        if ($this->logger instanceof Logger) {  // We need to sure logger object is available
            $this->logger->debug('Task Class Initialized');
        }
    }

    /**
     * Run cli task
     *
     * e.g: $this->c['task']->run('');
     * 
     * @param string  $uri   task uri
     * @param boolean $debug On / Off print debugger
     * 
     * @return void
     */
    public function run($uri, $debug = false)
    {
        $delimiter = (strpos($uri, '/') > 0) ? '/' : ' ';
        $uri = explode($delimiter, trim($uri));
        $directory = array_shift($uri);
        $segments = self::getSegments($uri);

        $host = isset($_SERVER['HTTP_HOST']) ? '--host='.$_SERVER['HTTP_HOST'] : '';  // Add http host variable if request comes from http
        $shell = PHP_PATH .' '. FPATH .'/'. TASK_FILE .' '.$directory.' '. implode('/', $segments).' '. $host;

        if ($debug) {  // Enable debug output to log folder.
            $output = preg_replace(array('/\033\[36m/', '/\033\[31m/', '/\033\[0m/'), array('', '', ''), shell_exec($shell)); // Clean cli color codes
            if ($this->logger instanceof Logger) {
                $this->logger->debug('$_TASK request', array('command' => $shell, 'output' => $output));
            }
            return $output;
        }
        shell_exec($shell . ' > /dev/null &');  // Async task

        if ($this->logger instanceof Logger) {
            $this->logger->debug('$_TASK executed', array('shell' => $shell));
        }
    }

    /**
     * Create segments
     * 
     * @param array $uri segments
     *
     * @return array
     */
    protected static function getSegments($uri)
    {
        $segments = array();
        foreach ($uri as $k => $v) {
            if ( ! $v) {
                $segments[$k] = 'false';
            } else {
                $segments[$k] = static::ucwordsUnderscore($v);
            }
        }
        return $segments;
    }

    /**
     * Replace underscore to spaces to use ucwords
     * 
     * Before : widgets\tutorials_a  
     * After  : Widgets\Tutorials_A
     * 
     * @param string $string namespace part
     * 
     * @return void
     */
    protected static function ucwordsUnderscore($string)
    {
        if (strpos($string, '_') > 0) {
            $str = str_replace('_', '{__DELIM__}', $string);
            $exp = explode('{__DELIM__}', $str);
            $newArray = array();
            foreach ($exp as $value) {
                $newArray[] = ucfirst($value);
            }
            return implode('_', $newArray);
        }
        return $string;
    }

}

// END Task.php File
/* End of file Task.php

/* Location: .Obullo/Task/Task.php */