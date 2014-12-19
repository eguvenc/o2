<?php

namespace Obullo\Log\JobHandler;

use Obullo\Log\Formatter\LineFormatter;

/**
 * Email JobHandler Class
 * 
 * @category  Log
 * @package   JobHandler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Class JobHandlerEmail extends AbstractJobHandler implements JobHandlerInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

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
     * Formatter
     * 
     * @var object
     */
    protected $formatter;

    /**
     * Config Constructor
     *
     * @param object $c      container
     * @param object $mailer mailer service
     * @param array  $params parameters
     */
    public function __construct($c, $mailer, array $params = array())
    {
        $c = null;
        $this->mailer = $mailer;
        $this->message = $params['message'];
        $this->formatter = new LineFormatter($this->c);

        parent::__construct($params);

        $this->mailer->from($params['from']);
        $this->mailer->to($params['to']);
        $this->mailer->cc($params['cc']); 
        $this->mailer->bcc($params['bcc']);
        $this->mailer->subject($params['subject']);
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
     * Writer 
     *
     * @param array $data log record
     * 
     * @return boolean
     */
    public function write(array $data)
    {
        $lines = '';
        foreach ($data['record'] as $record) {
            $record = $this->format($data['time'], $record);
            $lines.= $this->formatter->format($record);
        }
        $this->mailer->message(sprintf($this->message, $lines));
        $this->mailer->send();
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

// END JobHandlerEmail class

/* End of file JobHandlerEmail.php */
/* Location: .Obullo/Log/JobHandler/JobHandlerEmail.php */