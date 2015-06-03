<?php

namespace Obullo\Mailer\Transport;

use Obullo\Mailer\Text;
use Obullo\Mailer\Validator;
use Obullo\Container\ContainerInterface;

/**
 * StandartMailer Class
 * 
 * This class methods borrowed from Codeigniter Email Library.
 * 
 * @category  Adaptor
 * @package   Mail
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 */
abstract class StandartMailer
{
    use MailerTrait;
    
    public $useragent = 'Obullo Mailer';
    public $mailpath = '/usr/sbin/sendmail';    // Sendmail path
    public $wordwrap = true;       // true/false  Turns word-wrap on/off
    public $mailtype = 'text';     // text/html  Defines email formatting
    public $charset = 'utf-8';     // Default char set: iso-8859-1 or us-ascii
    public $multipart = 'mixed';   // "mixed" (in the body) or "related" (separate)
    public $altMessage = '';       // Alternative message for HTML emails
    public $validate = false;      // true/false.  Enables email validation
    public $priority = '3';        // Default priority (1 - 5)
    public $newline = "\n";        // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)
    public $crlf = "\n";           // The RFC 2045 compliant CRLF for quoted-printable is "\r\n".  Apparently some servers,
                                   // even on the receiving end think they need to muck with CRLFs, so using "\n", while
                                   // distasteful, is the only thing that seems to work for all environment
    public $wrapchars = 76;
    public $validator;
    public $sendMultipart = true;   // true/false - Yahoo does not like multipart alternative, so this is an override.  Set to false for Yahoo.
    public $bccBatchMode = false;   // true/false  Turns on/off Bcc batch feature
    public $bccBatchSize = 200;     // If bcc_batch_mode = true, sets max number of Bccs in each batch
    public $safeMode = false;
    public $subject = '';
    public $body = '';
    public $finalbody = '';
    public $altBoundary = '';
    public $atcBoundary = '';
    public $headerStr = '';
    public $encoding = '8bit';
    public $replytoFlag = false;
    public $debugMsg = array();
    public $recipients = array();
    public $ccArray = array();
    public $bccArray = array();
    public $headers = array();
    public $attachName = array();
    public $attachType = array();
    public $attachDisp = array();
    public $baseCharsets = array('us-ascii', 'iso-2022-');    // 7-bit charsets (excluding language suffix)
    public $bitDepths = array('7bit', '8bit');
    public $priorities = array('1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)');

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->init();
        
        $this->safeMode = ((boolean) @ini_get('safe_mode') === false) ? false : true;
        $this->c['logger']->debug('Mail Class Initialized');
    }

    /**
     * Constructor
     * 
     * @return object
     */
    public function init()
    {
        $this->clear();
        $transport = $this->c['config']->load('mailer/transport');

        $this->bccBatchMode = $transport['bccBatch']['mode'];
        $this->bccBatchSize = $transport['bccBatch']['size'];

        foreach ($transport as $key => $val) {
            if (isset($this->$key)) {
                $methodName = ucfirst($key);
                $method = 'set'.$methodName;
                if (method_exists($this, $method)) {
                    $this->$method($val);
                } else {
                    $this->$key = $val;
                }
            }
        }
        $this->safeMode = ((boolean) @ini_get('safe_mode') === false) ? false : true;
        if ($this->validate) {
            $this->validator = new Validator;
        }
        return $this;
    }

    /**
     * Initialize the Email Data
     *
     * @param boolean $clearAttachments clear switch
     * 
     * @return void
     */
    public function clear($clearAttachments = false)
    {
        $this->subject = "";
        $this->body = "";
        $this->finalbody = "";
        $this->headerStr = "";
        $this->replytoFlag = false;
        $this->recipients = array();
        $this->headers = array();
        $this->debugMsg = array();

        $this->setHeader('User-Agent', $this->useragent);
        $this->setDate();

        if ($clearAttachments !== false) {
            $this->attachName = array();
            $this->attachType = array();
            $this->attachDisp = array();
        }
    }

    /**
     * Add a Header Item
     *
     * @param string $header key
     * @param string $value  value
     * 
     * @return void
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
    }

    /**
     * Set From
     * 
     * @param string $from address
     * @param string $name label
     * 
     * @return void
     */
    public function from($from, $name = '')
    {
        if (preg_match('/\<(.*)\>/', $from, $match)) {
            $from = $match['1'];
        }
        if ($this->validate) {
            $this->validator->validateEmail($this->strToArray($from));
            if ($this->validator->isError()) {
                $this->setErrorMessage($this->validator->getError(), $this->validator->getValue());
            }
        }
        if ($name != '') {  // Prepare the display name
                            // Only use Q encoding if there are characters that would require it
            if ( ! preg_match('/[\200-\377]/', $name)) {
                // add slashes for non-printing characters, slashes, and double quotes, and surround it in double quotes
                $name = '"' . addcslashes($name, "\0..\37\177'\"\\") . '"';
            } else {
                $name = $this->prepQencoding($name, true);
            }
        }
        $this->setHeader('From', $name . ' <' . $from . '>');
        $this->setHeader('Return-Path', '<' . $from . '>');
    }

    /**
     * Set Reply-to
     *
     * @param string $replyto address
     * @param string $name    label
     * 
     * @return void
     */
    public function replyTo($replyto, $name = '')
    {
        if (preg_match('/\<(.*)\>/', $replyto, $match)) {
            $replyto = $match['1'];
        }
        if ($this->validate) {
            $this->validator->validateEmail($this->strToArray($replyto));
            if ($this->validator->isError()) {
                $this->setErrorMessage($this->validator->getError(), $this->validator->getValue());
            }
        }
        if ($name == '') {
            $name = $replyto;
        }
        if (strncmp($name, '"', 1) != 0) {
            $name = '"' . $name . '"';
        }
        $this->setHeader('Reply-To', $name . ' <' . $replyto . '>');
        $this->replytoFlag = true;
    }

    /**
     * Set Email Subject
     * 
     * @param string $subject email subject
     * 
     * @return void
     */
    public function subject($subject)
    {
        $subject = $this->prepQencoding($subject);
        $this->setHeader('Subject', $subject);
    }

    /**
     * Set Body
     *
     * @param string $body email body
     * 
     * @return void
     */
    public function message($body)
    {
        $this->body = stripslashes(rtrim(str_replace("\r", "", $body)));
    }

    /**
     * Assign file attachments
     * 
     * @param string $filename    attachment name
     * @param string $disposition default attachment
     * 
     * @return void
     */
    public function attach($filename, $disposition = 'attachment')
    {
        $this->attachName[] = $filename;
        $file = basename($filename);
        $mimes = explode('.', $file);
        $mimes = next($mimes);
        $file = new File;
        $this->attachType[] = $file->mimeTypes($mimes);
        $this->attachDisp[] = $disposition; // Can also be 'inline'  Not sure if it matters
    }

    /**
     * Set Multipart Value
     *
     * @param string $str message
     * 
     * @return void
     */
    public function setAltMessage($str = '')
    {
        $this->altMessage = ($str == '') ? '' : $str;
    }

    /**
     * Set Mailtype
     *
     * @param string $type default text
     * 
     * @return void
     */
    public function setMailtype($type = 'text')
    {
        $this->mailtype = ($type == 'html') ? 'html' : 'text';
    }

    /**
     * Returns mailt type: html / text
     * 
     * @return string
     */
    public function getMailType()
    {
        return $this->mailtype;
    }

    /**
     * Set Wordwrap
     * 
     * @param bool $wordwrap on off
     * 
     * @return void
     */
    public function setWordwrap($wordwrap = true)
    {
        $this->wordwrap = ($wordwrap === false) ? false : true;
    }

    /**
     * Set Priority
     * 
     * @param integer $n priority number
     * 
     * @return void
     */
    public function setPriority($n = 3)
    {
        if ( ! is_numeric($n)) {
            $this->priority = 3;
            return;
        }
        if ($n < 1 OR $n > 5) {
            $this->priority = 3;
            return;
        }
        $this->priority = $n;
    }

    /**
     * Set Newline Character
     * 
     * @param string $newline character
     * 
     * @return void
     */
    public function setNewline($newline = "\n")
    {
        if ($newline != "\n" AND $newline != "\r\n" AND $newline != "\r") {
            $this->newline = "\n";
            return;
        }
        $this->newline = $newline;
    }

    /**
     * Set CRLF
     * 
     * @param string $crlf newlines
     * 
     * @return void
     */
    public function setCrlf($crlf = "\n")
    {
        if ($crlf != "\n" AND $crlf != "\r\n" AND $crlf != "\r") {
            $this->crlf = "\n";
            return;
        }
        $this->crlf = $crlf;
    }

    /**
     * Set Message Boundary
     * 
     * @return  void
     */
    public function setBoundaries()
    {
        $this->altBoundary = "B_ALT_" . uniqid(''); // multipart/alternative
        $this->atcBoundary = "B_ATC_" . uniqid(''); // attachment boundary
    }

    /**
     * Get the Message ID
     *
     * @return string
     */
    public function getMessageId()
    {
        $from = $this->headers['Return-Path'];
        $from = str_replace(">", "", $from);
        $from = str_replace("<", "", $from);
        return "<" . uniqid('') . strstr($from, '@') . ">";
    }

    /**
     * Get Mail Encoding
     *
     * @param bool $return whether to return encoding
     * 
     * @return string
     */
    public function getEncoding($return = true)
    {
        $this->encoding = ( ! in_array($this->encoding, $this->bitDepths)) ? '8bit' : $this->encoding;
        foreach ($this->baseCharsets as $charset) {
            if (strncmp($charset, $this->charset, strlen($charset)) == 0) {
                $this->encoding = '7bit';
            }
        }
        if ($return == true) {
            return $this->encoding;
        }
    }

    /**
     * Get content type ( text/html/attachment )
     *
     * @return string
     */
    public function getContentType()
    {
        if ($this->mailtype == 'html' && count($this->attachName) == 0) {
            return 'html';
        } elseif ($this->mailtype == 'html' && count($this->attachName) > 0) {
            return 'html-attach';
        } elseif ($this->mailtype == 'text' && count($this->attachName) > 0) {
            return 'plain-attach';
        } else {
            return 'plain';
        }
    }

    /**
     * Get Mime message
     *
     * @return string
     */
    public function getMimeMessage()
    {
        return "This is a multi-part message in MIME format." . $this->newline . "Your email application may not support this format.";
    }

    /**
     * Build alternative plain text message
     *
     * This function provides the raw message for use
     * in plain-text headers of HTML-formatted emails.
     * If the user hasn't specified his own alternative message
     * it creates one by stripping the HTML
     *
     * @return string
     */
    public function getAltMessage()
    {
        $text = new Text($this->newline);
        if ($this->altMessage != "") {
            return $text->wordWrap($this->altMessage, $this->wrapchars);
        }
        if (preg_match('/\<body.*?\>(.*)\<\/body\>/si', $this->body, $match)) {
            $body = $match['1'];
        } else {
            $body = $this->body;
        }
        $body = trim(strip_tags($body));
        $body = preg_replace('#<!--(.*)--\>#', "", $body);
        $body = str_replace("\t", "", $body);
        for ($i = 20; $i >= 3; $i--) {
            $n = "";
            for ($x = 1; $x <= $i; $x++) {
                $n .= "\n";
            }
            $body = str_replace($n, "\n\n", $body);
        }
        return $text->wordWrap($body, $this->wrapchars);
    }

    /**
     * Send Email
     *
     * @return bool
     */
    public function send()
    {
        if ($this->replytoFlag == false) {
            $this->replyTo($this->headers['From']);
        }
        if (( ! isset($this->recipients) AND ! isset($this->headers['To'])) AND ( ! isset($this->bccArray) AND ! isset($this->headers['Bcc'])) 
            AND ( ! isset($this->headers['Cc']))
        ) {
            $this->setErrorMessage('OBULLO:MAILER:NO_RECIPIENTS');
            return false;
        }
        $this->buildHeaders();
        if ($this->bccBatchMode AND count($this->bccArray) > 0) {
            if (count($this->bccArray) > $this->bccBatchSize)
                return $this->batchBccSend();
        }
        $this->buildMessage();
        if ( ! $this->spoolEmail()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Unwrap special elements
     *
     * @return void
     */
    public function unwrapSpecials()
    {
        $this->finalbody = preg_replace_callback("/\{unwrap\}(.*?)\{\/unwrap\}/si", array($this, 'removeNlCallback'), $this->finalbody);
    }

    /**
     * Strip line-breaks via callback
     *
     * @param array $matches results
     * 
     * @return string
     */
    public function removeNlCallback($matches)
    {
        if (strpos($matches[1], "\r") !== false OR strpos($matches[1], "\n") !== false) {
            $matches[1] = str_replace(array("\r\n", "\r", "\n"), '', $matches[1]);
        }
        return $matches[1];
    }

    /**
     * Get Hostname
     *
     * @return string
     */
    public function getHostname()
    {
        return (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : 'localhost.localdomain';
    }

    /**
     * Get Debug Message
     *
     * @return string
     */
    public function printDebugger()
    {
        $msg = '';
        if (count($this->debugMsg) > 0) {
            foreach ($this->debugMsg as $val) {
                $msg .= $val;
            }
        }
        $msg .= "<pre>" . $this->headerStr . "\n" . htmlspecialchars($this->subject) . "\n" . htmlspecialchars($this->finalbody) . '</pre>';
        return $msg;
    }

    /**
     * Set Message
     *
     * @param string $msg message
     * @param string $val value
     *
     * @return void
     */
    public function setErrorMessage($msg, $val = '')
    {
        $this->c['translator']->load('mailer');
        $this->debugMsg[] = $this->c['translator']->get($msg, $val) . "<br />";
    }

}

// END StandartMailer class

/* End of file StandartMailer.php */
/* Location: .Obullo/Mailer/Transport/StandartMailer.php */