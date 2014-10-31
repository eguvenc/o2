<?php

namespace Obullo\Log\Handler;

use Obullo\Log\PriorityQueue,
    Obullo\Log\Formatter\LineFormatter,
    Obullo\Log\Handler\AbstractHandler;

/**
 * Email Handler Class
 *
 * You should use this handler for emergency, alerts or rarely used important notices.
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class EmailHandler extends AbstractHandler
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Config
     * 
     * @var array
     */
    public $config;

    /**
     * Service mailer
     * 
     * @var object
     */
    public $mailer;

    /**
     * Sender email
     * 
     * @var string
     */
    public $from;

    /**
     * Receiver
     * 
     * @var string
     */
    public $to;

    /**
     * Carbon copy addresses
     * 
     * @var string
     */
    public $cc = null;

    /**
     * Blind carbon copy addresses
     * 
     * @var string
     */
    public $bcc = null;

    /**
     * Subject
     * 
     * @var string
     */
    public $subject;

    /**
     * Message body
     * 
     * @var string
     */
    public $message;

    /**
     * Config Constructor
     *
     * @param object $c      container
     * @param object $mailer mailer object
     * @param array  $params configuration
     */
    public function __construct($c, $mailer, $params)
    {
        $this->c = $c;
        $this->config = $params;
        $this->mailer = $mailer;

        parent::__construct($params);

        $this->message = $params['message'];
        $this->mailer->from($params['from']);
        $this->mailer->to($params['to']);
        $this->mailer->cc($params['cc']); 
        $this->mailer->bcc($params['bcc']);
        $this->mailer->subject($params['subject']);
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
     * Write line to file
     * 
     * @param string $record single record data
     * @param string $type   request types ( app, cli, ajax )
     * 
     * @return boolean
     */
    public function write($record, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        $this->mailer->message(sprintf($this->message, $record)); 
        $this->mailer->send();
        return true;
    }

    /**
     * Store multiple log records into variable then send.
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
        $this->mailer->message(sprintf($this->message, $lines));
        $this->mailer->send();
        return true;
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

// END EmailHandler class

/* End of file EmailHandler.php */
/* Location: .Obullo/Log/Handler/EmailHandler.php */