<?php

namespace Obullo\QueueLogger\JobHandler;

/**
 * File JobHandler Class
 * 
 * @category  Log
 * @package   JobHandler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class JobHandlerFile implements JobHandlerInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Config parameters
     * 
     * @var array
     */
    protected $config;

    /**
     * Config Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct($c, array $params = array())
    {
        $this->c = $c;
        $this->config = $params;
    }

    /**
     * Write output
     *
     * @param string $record single record data
     * 
     * @return mixed
     */
    public function write($record)
    {
        if (isset($data['batch'])) {
            $lines = '';
            foreach ($data['record'] as $record) {
                $lines.= $record;
            }
            if ( ! $fop = fopen($this->path, 'ab')) {
                return false;
            }
            flock($fop, LOCK_EX);
            fwrite($fop, $lines);
            flock($fop, LOCK_UN);
            fclose($fop);
            if ( ! defined('STDIN')) {   // Do not do ( chmod ) in CLI mode, it cause write errors
                chmod($this->path, 0666);
            }
            return true;
        }
        return false;
    }

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close() 
    {
        return;
    }
}

// END JobHandlerFile class

/* End of file JobHandlerFile.php */
/* Location: .Obullo/QueueLogger/JobHandler/JobHandlerFile.php */