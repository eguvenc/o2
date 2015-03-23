<?php

namespace Obullo\Log\Debugger;

use RuntimeException;
use Obullo\Log\PriorityQueue;
use Obullo\Log\AbstractLogger;
use Obullo\Container\Container;
use Obullo\Log\Formatter\LineFormatterTrait;

/**
 * Log Debugger Output Class
 * 
 * @category  Log
 * @package   Debugger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class Output
{
    use LineFormatterTrait;

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
     * @param object $c      container
     * @param object $logger logger object
     */
    public function __construct(Container $c, AbstractLogger $logger)
    {
        $this->c = $c;
        $this->logger = $logger;
        $this->config = $c['config'];
    }

    /**
     * Set debug handler
     * 
     * @param string $handler name
     *
     * @return void
     */
    public function setHandler($handler = 'file')
    {
        $this->handler = strtolower($handler);
    }

    /**
    * Format log records
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
     * Write IFRAME Contents
     * 
     * @param string $prepend output we want to add before the response
     * @param string $output  response
     * 
     * @return string
     */
    public function writeIframe($prepend, $output)
    {
        if ( ! is_writable(RESOURCES .'data'. DS .'temp'. DS)) {
            die(
                sprintf(
                    "Temp directory <b>%s</b> is not writable. We could not create debugger data.",  // Exceptions not works at this level
                    str_replace(ROOT, 'ROOT/', RESOURCES .'data'. DS .'temp'. DS)
                )
            );
        }
        $content = RESOURCES .'data'. DS .'temp'. DS .'debugger-content.html';
        $bottom  = RESOURCES .'data'. DS .'temp'. DS .'debugger-bottom.html';

        $prepend = str_replace('@ASSETS', rtrim($this->c['config']['url']['assets'], '/'), $prepend);  // Build assets links
        $output  = preg_replace('#</head>#', "<base target=\"_parent\" />\n</head>", $output);  // Convert all page links to parent otherwise
                                                                                                // iframe not works
        file_put_contents($content, $output);
        file_put_contents($bottom, $prepend);

        return '<frameset rows="60%,40%" frameborder="0">;
            <frame name="1" src="/resources/data/temp/debugger-content.html">
            <frame name="1" src="/resources/data/temp/debugger-bottom.html">
        </frameset>';
    }

    /**
     * Write processor output to file
     *
     * @param object $pQ priorityQueue array queue data
     * 
     * @return string echo the log output
     */
    public function writeBody($pQ)
    {
        ob_start();
        include_once 'Views/Debugger.php';
        $layout = ob_get_clean();

        if ( ! defined('STDIN')) {   // Disable html output for task requests
            $lines = '';
            if ($pQ == false) {
                $lines = sprintf('The log handler %s is not defined.', $this->handler);
            } else {
                $pQ->setExtractFlags(PriorityQueue::EXTR_DATA); // Queue mode of extraction 
                $count = $pQ->count();
                if ($count > 0) {
                    $i = 0;
                    $pQ->top();  // Go to Top
                    while ($pQ->valid()) {         // Prepare Lines 
                        $record = $this->format($this->c['config']['logger']['format']['date'], $pQ->current());
                        $lines.= static::view($record, $layout);
                        $pQ->next();
                        ++$i;                               
                    }
                    $body = preg_replace('/\{\{LOGS\}\}/', $lines, $layout);
                    $this->c['response']->prepend($body);
                } else {
                    return sprintf('There is no data in %s handler.', $this->handler);
                }
            }
        }

    }

    /**
     * Debug view
     * 
     * @param array $record data
     * 
     * @return void
     */
    protected static function view($record)
    {
        $logs = '<p>';
        $logs.= '<span class="date">['.$record['datetime'].']</span>';
        $logs.= '<span class="info">'.$record['channel'].'.'.$record['level'].':</span>';
        $logs.= '--> '.$record['message'].' -- '. str_replace(array('"', "'"), array('&quot;', '&quot;'), $record['context']);
        $logs.= '</p>';
        return $logs;
    }
}

// END Output class
/* End of file Output.php */

/* Location: .Obullo/Log/Debbuger/Output.php */