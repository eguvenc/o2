<?php

namespace Obullo\Cli\Commands;

use Log\Constants;

/**
 * Clear Command
 * 
 * @category  Cli
 * @package   Commands
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/Cli
 */
Class Clear implements CommandInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Config array
     * 
     * @var array
     */
    public $config;

    /**
     * Logger
     * 
     * @var object
     */
    public $logger;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->config = $this->c->load('config');
        $this->logger = $c->load('service/logger');
    }

    /**
     * Execute command
     * 
     * @return boolean
     */
    public function run()
    {
        /**
         * Clear File handler data
         */
        $files = array(
            trim($this->config['log']['file']['path']['http'], '/'),
            trim($this->config['log']['file']['path']['ajax'], '/'),
            trim($this->config['log']['file']['path']['cli'], '/'),
        );
        foreach ($files as $file) {
            $file = str_replace('/', DS, $file);
            if (strpos($file, 'data') === 0) { 
                $file = str_replace('data', rtrim(DATA, DS), $file);
            } 
            $exp = explode(DS, $file);
            $filename = array_pop($exp);
            $path= implode(DS, $exp). DS;
            if (is_file($path.$filename)) {
                unlink($path.$filename);
            }
        }
        /**
         * Clear Queue handler data
         */
        $queue = $this->c->load('service/queue');
        $queue->deleteQueue($this->config['log']['queue']['route']);
        
        echo "\33[1;36mApplication logs deleted.\33[0m\n";

        return true;
    }

}

// END Clear class

/* End of file Clear.php */
/* Location: .Obullo/Cli/Commands/Clear.php */