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
    public $c;

    /**
     * Logger class
     * 
     * @var object
     */
    public $logger;

    /**
     * Constructor
     * 
     * @param object $c       container
     * @param object $logger  logger object
     * @param object $handler current handler constant
     */
    public function __construct($c, $logger, $handler = LOGGER_FILE)
    {
        $this->c = $c;
        $this->logger = $logger;
        $this->handler = $handler;
    }

    /**
     * Format the line which is defined in app/config/$env/config.php
     * This feature just for line based loggers.
     * 
     * 'line' => '[%datetime%] %channel%.%level%: --> %message% %context% %extra%\n',
     * 
     * @param array $record array of log data
     * 
     * @return string returns to formated string
     */
    public function lineFormat($record)
    {
        if ( ! is_array($record)) {
            return;
        }
        return str_replace(
            array(
            '%datetime%',
            '%channel%',
            '%level%',
            '%message%',
            '%context%',
            '%extra%',
            ), array(
            $record['datetime'],
            $record['channel'],
            $record['level'],
            $record['message'],
            (empty($record['context'])) ? '' : $record['context'],
            (empty($record['extra'])) ? '' : $record['extra'],
            $record['extra'],
            ),
            str_replace('\n', "\n", $this->c['config']['log']['line'])
        );
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
            if ($pQ == false) {
                $lines = sprintf('The log handler %s is not defined.', $this->handler);
            } else {
                $formatter = new LineFormatter($this->c, $this->logger);
                $pQ->setExtractFlags(PriorityQueue::EXTR_DATA); // Queue mode of extraction 
                $count = $pQ->count();
                if ($count > 0) {
                    $pQ->top();  // Go to Top
                    $lines = '';
                    while ($pQ->valid()) {         // Prepare Lines 
                        $lines.= str_replace('\n', '<br />', $formatter->format($pQ->current()));  // output handler must be file for debugging
                        $pQ->next();                                       
                    }
                }
                if ($count == 0) {
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