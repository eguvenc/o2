<?php

namespace Obullo\QueueLogger\JobHandler;

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
Class JobHandlerEmail implements JobHandlerInterface
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
     * @param array  $params parameters
     */
    public function __construct($c, array $params = array())
    {
        $c = null;
        $this->mailer = $params['mailer'];
        $this->message = $params['message'];

        $this->mailer->from($params['from']);
        $this->mailer->to($params['to']);
        $this->mailer->cc($params['cc']); 
        $this->mailer->bcc($params['bcc']);
        $this->mailer->subject($params['subject']);
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
                $lines.= $record;
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
/* Location: .Obullo/Log/Queue/JobHandler/JobHandlerEmail.php */