<?php

namespace Obullo\Mail\Provider;

use RuntimeException;
use Obullo\Mail\Text;
use Obullo\Mail\File;
use Obullo\Mail\Utils;
use Obullo\Mail\Validator;
use Obullo\Mail\MailResult;
use Obullo\Log\LoggerInterface;
use Obullo\Container\ContainerInterface;
use Obullo\Translation\TranslatorInterface;

/**
 * AbstractMailer
 * 
 * @category  Mail
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 */
abstract class AbstractProvider
{
    protected $date;
    protected $body = '';
    protected $logger;
    protected $validator;
    protected $translator;
    protected $subject = '';
    protected $result = null;
    protected $crlf = "\n";            // The RFC 2045 compliant CRLF for quoted-printable is "\r\n".  Apparently some servers,
    protected $files = array();        // Provider compatible attached files
    protected $newline = "\n";         // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)
    protected $altMessage = '';        // Alternative message for HTML emails
    protected $useragent = '';
    protected $wordwrap = true;        // true/false  Turns word-wrap on/off
    protected $wrapchars = 76;
    protected $mailtype = 'text';      // text/html  Defines email formatting
    protected $charset = 'utf-8';      // Default char set: iso-8859-1 or us-ascii
    protected $validate = false;       // true/false.  Enables email validation
    protected $priority = '3';         // Default priority (1 - 5)
    protected $params = array();       // Service & config parameters
    protected $headers = array();
    protected $ccArray = array();
    protected $bccArray = array();
    protected $recipients = array();
    protected $attachCount = 0;        // Number of attachments
    protected $replytoFlag = false;
    protected $msgEvent = array();     // Request data

    /**
     * Constructor
     *
     * @param object $c          \Obullo\Container\ContainerInterface
     * @param object $translator \Obullo\Translation\TranslatorInterface
     * @param object $logger     \Obullo\Log\LogInterface
     * @param array  $params     service parameters
     */
    public function __construct(ContainerInterface $c, TranslatorInterface $translator, LoggerInterface $logger, array $params)
    {
        $this->c = $c;
        $this->params = $params;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->translator->load('mailer');
        $this->init();
    }

    /**
     * Send email with http request
     * 
     * @return boolean
     */
    protected abstract function sendEmail();

    /**
     * Send email with Queue
     *
     * @param array $options queue options
     * 
     * @return object MailResult
     */
    protected abstract function queueEmail($options = array());

    /**
     * Constructor
     * 
     * @return object
     */
    public function init()
    {
        $this->clear();
        $this->validate  = $this->params['default']['validate'];
        $this->useragent = $this->params['default']['useragent'];
        $this->charset   = $this->params['message']['charset'];
        $this->priority  = $this->params['message']['priority'];
        $this->wordwrap  = $this->params['message']['wordwrap'];
        $this->wrapchars = $this->params['message']['wrapchars'];
        $this->mailtype  = $this->params['message']['mailtype'];
        $this->crlf      = $this->params['message']['crlf'];
        $this->newline   = $this->params['message']['newline'];
        if ($this->validate) {
            $this->validator = new Validator;
        }
        $this->result = new MailResult($this, $this->logger);
        return $this;
    }

    /**
     * Initialize the Email Data
     * 
     * @return object
     */
    public function clear()
    {
        $this->subject = "";
        $this->body = "";
        $this->headerStr = "";
        $this->replytoFlag = false;
        $this->recipients = array();
        $this->headers = array();
        $this->attachCount = 0;
        $this->msgEvent = array();
        $this->setHeader('User-Agent', $this->getUserAgent());
        $this->setDate();
        return $this;
    }

    /**
     * Add a Header Item
     *
     * @param string $header key
     * @param string $value  value
     * 
     * @return object
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * Set From
     * 
     * @param string $from address
     * @param string $name label
     * 
     * @return object
     */
    public function from($from, $name = '')
    {
        if (empty($from)) {
            $result = $this->getMailResult();
            $result->setCode($result::INVALID_EMAIL);
            $result->setMessage($this->c['translator']->get('OBULLO:MAILER:NO_SENDER'));
            return $this;
        }
        if (preg_match('/(.*?)\<(.*)\>/', $from, $match)) {
            $name = trim($match[1]);
            $from = $match[2];
        }
        $this->validateEmail(Utils::strToArray($from));
        $this->fromName = $name;
        $this->fromEmail = $from;
        return $this;
    }

    /**
     * Set Recipients
     *
     * @param mixed $to source emails
     * 
     * @return object
     */
    public function to($to = null)
    {
        if ($this->isEmpty($to)) {
            return $this;
        }
        $to = Utils::strToArray($to);
        $this->setHeader('To', implode(", ", $to));

        $this->validateEmail($to);
        $to = Utils::formatEmail($to);

        foreach ($to as $value) {
            $this->recipients[] = array('type' => 'to', 'email' => $value['email'], 'name' => $value['name']);
        }
        return $this;
    }

    /**
     * Set Cc
     *
     * @param mixed $cc carbon copy addresses
     * 
     * @return object
     */
    public function cc($cc = null)
    {
        if ($this->isEmpty($cc)) {
            return $this;
        }
        $cc = Utils::strToArray($cc);
        $this->setHeader('Cc', implode(", ", $cc));

        $this->validateEmail($cc);
        $cc = Utils::formatEmail($cc);

        foreach ($cc as $value) {
            $this->recipients[] = array('type' => 'cc', 'email' => $value['email'], 'name' => $value['name']);
        }
        return $this;
    }

    /**
     * Set BCC
     *
     * @param mixed $bcc blind carbon copy addresses
     * 
     * @return object
     */
    public function bcc($bcc = null)
    {
        if ($this->isEmpty($bcc)) {
            return $this;
        }
        $bcc = Utils::strToArray($bcc);
        $this->validateEmail($bcc);
        $bcc = Utils::formatEmail($bcc);

        foreach ($bcc as $value) {
            $this->recipients[] = array('type' => 'bcc', 'email' => $value['email'], 'name' => $value['name']);
        }
        return $this;
    }

    /**
     * Set Reply-to
     *
     * @param string $replyto address
     * @param string $name    label
     * 
     * @return object
     */
    public function replyTo($replyto, $name = '')
    {
        if ($this->isEmpty($replyto)) {
            return $this;
        }
        if (preg_match('/\<(.*)\>/', $replyto, $match)) {
            $replyto = $match['1'];
        }
        $this->validateEmail(Utils::strToArray($replyto));
        if ($name == '') {
            $name = $replyto;
        }
        if (strncmp($name, '"', 1) != 0) {
            $name = '"' . $name . '"';
        }
        $this->setHeader('Reply-To', $name . ' <' . $replyto . '>');
        $this->replytoFlag = true;
        return $this;
    }

    /**
     * Check recipient is null
     * 
     * @param mixed $recipient recipients to, cc or bcc
     * 
     * @return boolean
     */
    protected function isEmpty($recipient)
    {
        if (empty($recipient)) {
            $mailResult = $this->getMailResult();
            $mailResult->setCode($mailResult::INVALID_EMAIL);
            $mailResult->setMessage($this->c['translator']->get('OBULLO:MAILER:NO_RECIPIENTS'));
            return true;
        }
        return false;
    }

    /**
     * Set RFC 822 Date
     * 
     * @return string
     */
    public function setDate()
    {
        $timezone = date("Z");
        $operator = (strncmp($timezone, '-', 1) == 0) ? '-' : '+';
        $abs = abs($timezone);
        $floorTimezone = floor($abs / 3600) * 100 + ($abs % 3600 ) / 60;
        $date = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $operator, $floorTimezone);
        $this->setHeader('Date', $date);
        return $date;
    }

    /**
     * Set Mailtype
     *
     * @param string $type default text
     * 
     * @return object
     */
    public function setMailType($type = 'text')
    {
        $this->mailtype = ($type == 'html') ? 'html' : 'text';
        return $this;
    }

    /**
     * Set email validation
     * 
     * @param boolean $enabled true or false
     * 
     * @return object
     */
    public function setValidation($enabled = false)
    {
        $this->validate = $enabled;
        return $this;
    }

    /**
     * Set Email Subject
     * 
     * @param string $subject email subject
     * 
     * @return object
     */
    public function subject($subject)
    {
        $this->subject = $subject;
        $this->setHeader('Subject', $subject);
        return $this;
    }

    /**
     * Set Body
     *
     * @param string $body email body
     * 
     * @return object
     */
    public function message($body)
    {
        $this->body = stripslashes(rtrim(str_replace("\r", "", $body)));
        return $this;
    }

    /**
     * Assign file attachments
     * 
     * @param string $filename    attachment name
     * @param string $disposition default attachment or inline
     * 
     * @return object
     */
    public function attach($filename, $disposition = 'attachment')
    {
        $name  = basename($filename);
        $mimes = explode('.', $name);
        $mimes = next($mimes);
        $file  = new File;

        $this->msgEvent['files'][$this->attachCount] = array(
            'name' => $name,
            'type' => $file->mimeTypes($mimes),
            'fileurl' => $filename,
            'disposition' => $disposition,
        );
        ++$this->attachCount;
        return $this;
    }

    /**
     * Build mail message
     * 
     * @return void
     */
    protected function buildMessage()
    {
        if ($this->mailtype == 'html') {
            $this->msgEvent['html'] = $this->body;
        }
        if ($this->wordwrap === true && $this->mailtype != 'html') {
            $text = new Text($this->newline);
            $this->body = $text->wordWrap($this->body, $this->wrapchars);
            $this->msgEvent['text'] = $this->body;
        }
    }

    /**
     * Write Headers as a string
     *
     * @return void
     */
    protected function writeHeaders()
    {
        reset($this->headers);
        $this->headerStr = "";
        foreach ($this->headers as $key => $val) {
            $val = trim($val);
            if ($val != "") {
                $this->headerStr .= $key . ": " . $val . $this->newline;
            }
        }
        $this->msgEvent['headers'] = $this->headers;
    }

    /**
     * Initialize and validate email body before send 
     * 
     * @return bool
     */
    protected function checkEmail()
    {
        if ($this->replytoFlag == false) {
            $this->replyTo($this->fromEmail, $this->fromName);
        }
        if (( ! isset($this->recipients) && ! isset($this->headers['To']) ) 
            && ( ! isset($this->bccArray) && ! isset($this->headers['Bcc'])) 
            && ( ! isset($this->headers['Cc']))
        ) {
            $mailResult = $this->getMailResult();
            $mailResult->setCode($mailResult::NO_RECIPIENTS);
            $mailResult->setMessage($this->translator['OBULLO:MAILER:NO_RECIPIENTS']);
            return false;
        }
        return true;
    }

    /**
     * Send Email
     *
     * @return object MailResult
     */
    public function send()
    {
        $this->checkEmail();
        $this->writeHeaders();
        return $this->sendEmail();
    }

    /**
     * Queue Email
     *
     * @param array $options queue options
     * 
     * @return object MailResult
     */
    public function queue($options = array())
    {
        $this->checkEmail();
        $this->writeHeaders();
        return $this->queueEmail($options);
    }

    /**
     * Send new AMQP message
     *
     * @param array $msgEvent queue data
     * @param array $options  queue options
     * 
     * @return object MailResult
     */
    protected function push(array $msgEvent, $options = array())
    {
        $job = $this->params['queue']['job'];
        $msgEvent['mailer'] = $this->getProvider();
        $mailResult = $this->getMailResult();

        $push = $this->c['queue']
            ->push(
                'Workers@Mailer',
                $job,
                $msgEvent,
                $options
            );
        if (! $push) {
            $mailResult->setCode($mailResult::QUEUE_ERROR);
            $mailResult->setMessage($this->translator->get('OBULLO:MAILER:QUEUE_FAILED', $job));
            $this->logger->error(
                'Mailer queue failed', 
                array(
                    'job' => $job,
                    'msgEvent' => $msgEvent
                )
            );
        } else {
            $mailResult->setCode($mailResult::SUCCESS);
            $mailResult->setMessage("Queued");
        }
        return $mailResult;
    }

    /**
     * Add custom message to msgEvent variable
     * 
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return $this
     */
    public function addMessage($key, $value)
    {
        $this->msgEvent[$key] = $value;
        return $this;
    }

    /**
     * Returns to mail provider name in lowercase letters
     * 
     * @return string
     */
    public function getProvider()
    {
        $exp = explode("\\", get_class($this));
        return strtolower(end($exp));
    }

    /**
     * Returns top header array
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Creates new message ID
     *
     * @param string $returnPath null
     * 
     * @return string
     */
    public function getMessageId($returnPath = null)
    {
        if (! empty($returnPath)) {
            $from = $returnPath;
        }
        if (! empty($this->headers['Return-Path'])) {
            $from = $this->headers['Return-Path'];
        }
        if (empty($from)) {
            $from = $this->fromEmail;
        }
        $from = str_replace(">", "", $from);
        $from = str_replace("<", "", $from);
        return "<" . uniqid('') . strstr($from, '@') . ">";
    }

    /**
     * Returns to recipients
     * 
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Return to attachments
     * 
     * @return array
     */
    public function getAttachments()
    {
        return $this->msgEvent['files'];
    }

    /**
     * Get content type ( text/html/attachment )
     *
     * @return string
     */
    public function getContentType()
    {
        $hasAttachment = $this->hasAttachment();

        if ($this->mailtype == 'html' && $hasAttachment == false) {
            return 'html';
        } elseif ($this->mailtype == 'html' && $hasAttachment) {
            return 'html-attach';
        } elseif ($this->mailtype == 'text' && $hasAttachment) {
            return 'plain-attach';
        } else {
            return 'plain';
        }
    }

    /**
     * Returns to true if we have attached file otherwise false
     * 
     * @return boolean
     */
    public function hasAttachment()
    {
        return ($this->attachCount > 0) ? true : false;
    }

    /**
     * Returns to message subject
     * 
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns from name <from@email>
     * 
     * @return string
     */
    public function getFrom()
    {
        return $this->fromName." <".$this->fromEmail.">";
    }

    /**
     * Returns to from name
     * 
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Returns to from email
     * 
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * Returns to message body
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->body;
    }

    /**
     * Returns to user agent
     * 
     * @return string
     */
    public function getUserAgent()
    {
        return $this->useragent;
    }

    /**
     * Returns mail type: html / text
     * 
     * @return string
     */
    public function getMailType()
    {
        return empty($this->mailtype) ? $this->params['message']['mailtype'] : $this->mailtype;
    }

    /**
     * Returns to mail result object
     * 
     * @return object
     */
    public function getMailResult()
    {
        return $this->result;
    }

    /**
     * Validate email address
     * 
     * @param mixed $email address
     * 
     * @return object
     */
    public function validateEmail($email)
    {
        if ($this->validate) {
            $result = $this->getMailResult();
            $this->validator->validateEmail($email);

            if ($this->validator->isError()) {
                $result->setCode($result::INVALID_EMAIL);
                $result->setMessage($this->translator->get($this->validator->getError(), $this->validator->getValue()));
            }
        }
        return $this;
    }

}