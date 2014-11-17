<?php

namespace Obullo\Log;

use Obullo\Log\PriorityQueue,
    Obullo\Log\Formatter\LineFormatter;

/**
 * Logger Output Class
 * 
 * @category  Log
 * @package   Debug
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class Debug
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Logger class
     * 
     * @var object
     */
    protected $logger;

    /**
     * Config
     * 
     * @var array
     */
    protected $config;

    /**
     * Constructor
     * 
     * @param object $c       container
     * @param object $logger  logger object
     * @param object $handler current handler constant
     */
    public function __construct($c, $logger, $handler = 'file')
    {
        $this->c = $c;
        $this->logger = $logger;
        $this->config = $c->load('config');
        $this->handler = strtolower($handler);
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
     * @param object $pQ priorityQueue
     * 
     * @return string echo the log output
     */
    public function printDebugger($pQ)
    {
        $isXmlHttp = ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
        if ( ! $isXmlHttp AND ! defined('STDIN')) {      // Disable html output for ajax and task requests
            $lines = '';
            if ($pQ == false) {
                $lines = sprintf('The log handler %s is not defined.', $this->handler);
            } else {
                $pQ->setExtractFlags(PriorityQueue::EXTR_DATA); // Queue mode of extraction 
                $count = $pQ->count();
                if ($count > 0) {
                    $i = 0;
                    $pQ->top();  // Go to Top
                    $records = array();
                    while ($pQ->valid()) {         // Prepare Lines 
                        $records[$i] = $this->format($this->config['log']['format']['date'], $pQ->current());
                        $pQ->next();
                        ++$i;                               
                    }
                    $formatter = new LineFormatter($this->c, $this->logger);
                    foreach ($records as $record) {
                        $lines.= $formatter->format($record);
                    }
                } else {
                    $lines = sprintf('There is no data in %s handler.', $this->handler);
                }
            }
            return '<div style="
                    overflow-y:scroll;
                    background:#fff;
                    border-top: 2px solid #006857;
                    color:#006857;
                    padding:5px 5px;
                    position: absolute;
                    left: 0;
                    line-height:15px;
                    width: 100%;
                    height: 100%;
                    border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;font-size:12px;"><b>'.sprintf('LOGGER %s', 'DEBUG').'</b>
                    <pre style="
                    white-space: pre-wrap;       /* css-3 */
                    white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
                    white-space: -pre-wrap;      /* Opera 4-6 */
                    white-space: -o-pre-wrap;    /* Opera 7 */
                    word-wrap: break-word;       /* Internet Explorer 5.5+ */
                    background:#fff;
                    border: none;
                    color:#006857;
                    border-radius:4px;
                    -moz-border-radius:4px;
                    -webkit-
                    border-radius:4px;
                    padding:5px 10px;
                    font-size:12px;
                    padding:0;
                    margin-top:8px;">'.sprintf('%s', $lines).'</pre>
                    </div><style>html{position:relative !important;} body{position:static;min-height:100% !important;height: 100% !important;};</style>';

        }

    }
}

// END Debug class
/* End of file Debug.php */

/* Location: .Obullo/Log/Debug.php */