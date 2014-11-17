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
Class JobHandlerFile extends AbstractJobHandler implements JobHandlerInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

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
        $this->formatter = new LineFormatter($this->c);

        parent::__construct($params);
    }

    /**
    * Format log records and build lines
    *
    * @param string $timestamp         unix time
    * @param array  $unformattedRecord log data
    * 
    * @return array formatted record
    */
    public function format($timestamp, $unformattedRecord)
    {
        $record = array(
            'datetime' => date($this->config['log']['format']['date'], $timestamp),
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
            $str = var_export($unformattedRecord['context'], true);
            $record['context'] = strtr($str, array("\r\n" => '', "\r" => '', "\n" => ''));
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
        $lines = '';
        foreach ($data['record'] as $record) {
            $record = $this->format($data['time'], $record);
            $lines.= $this->formatter->format($record);
        }
        $this->path = static::replace($this->config['log']['file']['path']['http']); // Default http requests
        if ($data['request'] == 'ajax') {
            $this->path = static::replace($this->config['log']['file']['path']['ajax']); // Replace with ajax request path
        }
        if ($data['request'] == 'cli') {
            $this->path = static::replace($this->config['log']['file']['path']['cli']); // Replace with cli request path
        }
        if ( ! $fop = fopen($this->path, 'ab')) {
            return false;
        }
        flock($fop, LOCK_EX);
        fwrite($fop, $lines);
        flock($fop, LOCK_UN);
        fclose($fop);
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