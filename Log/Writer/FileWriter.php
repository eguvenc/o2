<?php

namespace Obullo\Log\Writer;

use Obullo\Log\Writer\AbstractWriter;

/**
 * File Writer Class
 * 
 * @category  Log
 * @package   Writer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class FileWriter extends AbstractWriter
{
    /**
     * Config
     * 
     * @var array
     */
    public $config;

    /**
     * File path
     * 
     * @var string
     */
    public $path;

    /**
     * Constructor
     *
     * @param array $params configuration
     */
    public function __construct($params)
    {
        parent::__construct($params);

        $this->config = $params;
        
        /**
         * Replace application request path
         */
        $this->path = static::replace($params['path']['app']);
        /**
         * Replace ajax request path
         */
        if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->path = static::replace($params['path']['ajax']);
        }
        /**
         * Replace cli request path
         */
        if (defined('STDIN')) {
            $this->path = static::replace($params['path']['cli']);
        }
    }

    /**
     * Config
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Write output
     *
     * @param string $record single record data
     * @param string $type   request types ( app, cli, ajax )
     * 
     * @return mixed
     */
    public function write($record, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        if ( ! $fop = fopen($this->path, 'ab')) {
            return false;
        }
        flock($fop, LOCK_EX);
        fwrite($fop, $record);
        flock($fop, LOCK_UN);
        fclose($fop);
        if ( ! defined('STDIN')) {   // Do not do ( chmod ) in CLI mode, it cause write errors
            chmod($this->path, 0666);
        }
        return true;
    }

    /**
     * Batch Operation
     * 
     * @param array  $records multiline record data
     * @param string $type    request types ( app, cli, ajax )
     * 
     * @return boolean
     */
    public function batch(array $records, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        $lines = '';
        foreach ($records as $record) {
            $lines.= $record;
        }
        return $this->write($lines, $type);
    }

    /**
     * If log path has "data/logs" folder, we replace it with "DIRECTORY_SEPERATOR. data".
     * 
     * @param string $path log path
     * 
     * @return string current path
     */
    public static function replace($path)
    {
        if (strpos($path, 'data') === 0) {
            $path = str_replace('/', DS, trim($path, '/'));
            $path = DATA .substr($path, 5);
        }
        return $path;
    }

    /**
     * Close connection
     * 
     * @return void
     */
    public function close()
    {
        return;
    }
}

// END FileWriter class

/* End of file FileWriter.php */
/* Location: .Obullo/Log/Writer/FileWriter.php */