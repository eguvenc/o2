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
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
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
            $str = var_export($unformattedRecord['context'], true);
            $record['context'] = strtr($str, array("\r\n" => '', "\r" => '', "\n" => ''));
            // $record['context'] = preg_replace('/[\r\n]+/', '', var_export($unformattedRecord['context'], true));
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
        if (isset($data['batch'])) {
            $lines = '';
            foreach ($data['record'] as $record) {
                $lines.= $this->formatter->format($record);
            }
            $this->mailer->message(sprintf($this->message, $lines));
            $this->mailer->send();
            return;
        }
        $this->mailer->message(sprintf($this->message, $data['record']));
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