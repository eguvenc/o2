<?php

namespace Obullo\Log\Handler;

use Obullo\Log\PriorityQueue,
    Obullo\Log\Formatter\LineFormatter,
    Obullo\Log\Handler\AbstractHandler;

/**
 * File Handler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class FileHandler extends AbstractHandler
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Writer class name
     * 
     * @var string
     */
    public $path;

    /**
     * Config variable
     * 
     * @var array
     */
    public $config;

    /**
     * Config Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct($c, $params)
    {
        $this->c = $c;
        $this->config = $params;

        parent::__construct($params);
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
     * Write processor output to file
     *
     * @param object $pQ priorityQueue object
     * 
     * @return boolean
     */
    public function exec(PriorityQueue $pQ)
    {
        $pQ->setExtractFlags(PriorityQueue::EXTR_DATA); // Queue mode of extraction 
        $formatter = new LineFormatter($this->c);

        if ($pQ->count() > 0) {
            $pQ->top();  // Go to Top
            $records = array();
            $i = 0;
            while ($pQ->valid()) {    // Prepare Lines
                $i++;
                $records[$i] = $formatter->format($pQ->current());
                $pQ->next(); 
            }
            $this->batch($records);
        }
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

// END FileHandler class

/* End of file FileHandler.php */
/* Location: .Obullo/Log/Handler/FileHandler.php */