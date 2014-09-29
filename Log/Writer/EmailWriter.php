<?php

namespace Obullo\Log\Writer;

use Obullo\Log\Writer\AbstractWriter;

/**
 * Email Writer Class
 * 
 * @category  Log
 * @package   Writer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class EmailWriter extends AbstractWriter
{
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
     * Constructor
     *
     * @param object $mailer service mailer instance
     * @param array  $params configuration
     */
    public function __construct($mailer, $params)
    {
        parent::__construct($params);
        
        $this->config = $params;

        $this->mailer = $mailer;
        $this->message = $params['message'];
        $this->mailer->from($params['from']);
        $this->mailer->to($params['to']);
        $this->mailer->cc($params['cc']); 
        $this->mailer->bcc($params['bcc']);
        $this->mailer->subject($params['subject']);
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
     * Close connection
     * 
     * @return void
     */
    public function close()
    {
        // $debugOutput = $this->mailer->printDebugger();
        // insert into failed jobs.
        return;
    }

}

// END EmailWriter class

/* End of file EmailWriter.php */
/* Location: .Obullo/Log/Writer/EmailWriter.php */