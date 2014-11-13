<?php

namespace Obullo\Log\JobHandler;

use Obullo\Log\Formatter\LineFormatter;

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
     * Formatter
     * 
     * @var object
     */
    protected $formatter;

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
        $this->formatter = new LineFormatter($this->c);
    }

    /**
    * Format log records and build lines
    *
    * @param string $dateFormat        log date format
    * @param array  $unformattedRecord log data
    * 
    * @return array formatted record
    */
    public function format($dateFormat, $unformattedRecord)
    {
        $record = array(
            'datetime' => date($dateFormat),
            'channel'  => $unformattedRecord['channel'],
            'level'    => $unformattedRecord['level'],
            'message'  => $unformattedRecord['message'],
            'context'  => null,
            'extra'    => null,
        );
        if (isset($unformattedRecord['context']['extra']) AND count($unformattedRecord['context']['extra']) > 0) {
            $record['extra'] = var_export($unformattedRecord['context']['extra'], true);
            unset($unformattedRecord['context']['extra']);     
        }
        if (count($unformattedRecord['context']) > 0) {
            $record['context'] = preg_replace('/[\r\n]+/', '', var_export($unformattedRecord['context'], true));
        }
        return $record; // formatted record
    }

    /**
     * Write output
     *
     * @param string $data single record data
     * 
     * @return mixed
     */
    public function write(array $data)
    {
        if ( ! $this->isAllowed($data['type'])) {
            return;
        }
        $lines = $data['record'];
        if (isset($data['batch'])) {
            $lines = '';
            foreach ($data['record'] as $record) {
                $lines.= $record; // $this->formatter->format($record);
            }
            $this->path = static::replace($this->config['file']['path']['http']); // Default http requests
            if ($data['type'] == 'ajax') {
                $this->path = static::replace($this->config['file']['path']['ajax']); // Replace with ajax request path
            }
            if ($data['type'] == 'cli') {
                $this->path = static::replace($this->config['file']['path']['cli']); // Replace with cli request path
            }
        }
        if ( ! $fop = fopen($this->path, 'ab')) {
            return false;
        }
        flock($fop, LOCK_EX);
        fwrite($fop, $lines);
        flock($fop, LOCK_UN);
        fclose($fop);
        return true;
    }

    /**
     * If log path has "data/logs" folder, we replace it with "DIRECTORY_SEPERATOR. data".
     * 
     * @param string $path log path
     * 
     * @return string current path
     */
    protected static function replace($path)
    {
        if (strpos($path, 'data') === 0) {
            $path = str_replace('/', DS, trim($path, '/'));
            $path = DATA .substr($path, 5);
        }
        return $path;
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
/* Location: .Obullo/Log/JobHandler/JobHandlerFile.php */