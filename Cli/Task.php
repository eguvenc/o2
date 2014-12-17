<?php

namespace Obullo\Cli;

use Obullo\Log\Logger;

/**
 * Task Class
 * 
 * @category  Cli
 * @package   Task
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/task
 */
Class Task
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
        $this->logger = $c->load('return service/logger');
        if ($this->logger instanceof Logger) {  // We need to sure logger object is available
            $this->logger->debug('Task Class Initialized');
        }
    }

    /**
     * Run cli task
     * 
     * @param string  $uri   task uri
     * @param boolean $debug On / Off print debugger
     * 
     * @return void
     */
    public function run($uri, $debug = false)
    {
        $uri       = explode('/', trim($uri));
        $directory = array_shift($uri);
        foreach ($uri as $i => $section) {
            if ( ! $section) {
                $uri[$i] = 'false';
            }
        }
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $shell = PHP_PATH .' '. FPATH .'/'. TASK_FILE .' '.$directory.' '. implode('/', $uri) .' '. $host;
        // die;
        // echo $shell;
        if ($debug) {  // Enable debug output to log folder.
            $output = preg_replace(array('/\033\[36m/', '/\033\[31m/', '/\033\[0m/'), array('', '', ''), shell_exec($shell)); // Clean cli color codes
            if ($this->logger instanceof Logger) {
                $this->logger->debug('$_TASK request: ' . $shell, array('output' => $output));
            }
            return $output;
        }
        shell_exec($shell . ' > /dev/null &');  // Continious task
        if ($this->logger instanceof Logger) {
            $this->logger->debug('$_TASK command executed', array('shell' => $shell));
        }
    }

}

// END Task.php File
/* End of file Task.php

/* Location: .Obullo/Cli/Task.php */