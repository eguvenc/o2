<?php

namespace Obullo\Mail\Provider;

use RuntimeException;
use Obullo\Mail\Text;
use Obullo\Mail\File;
use Obullo\Mail\Utils;
use Obullo\Mail\Validator;
use Obullo\Mail\MailResult;
use Obullo\Container\ContainerInterface;

/**
 * AbstractMailer
 * 
 * @category  Mail
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 */
abstract class AbstractProvider
{
    protected $date;
    protected $body = '';
    protected $validator;
    protected $subject = '';
    protected $crlf = "\n";            // The RFC 2045 compliant CRLF for quoted-printable is "\r\n".  Apparently some servers,
    protected $newline = "\n";         // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)
    protected $altMessage = '';        // Alternative message for HTML emails
    protected $useragent = '';
    protected $wordwrap = true;        // true/false  Turns word-wrap on/off
    protected $wrapchars = 76;
    protected $mailtype = 'text';      // text/html  Defines email formatting
    protected $charset = 'utf-8';      // Default char set: iso-8859-1 or us-ascii
    protected $validate = false;       // true/false.  Enables email validation
    protected $priority = '3';         // Default priority (1 - 5)
    protected $params = array();       // Config parameters
    protected $headers = array();
    protected $ccArray = array();
    protected $bccArray = array();
    protected $recipients = array();
    protected $attachments = array();
    protected $replytoFlag = false;
    protected $attachCount = 0;        // Number of attachments
    protected $msgEvent = array();     // Request data

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params config & service parameters
     */
    public function __construct(ContainerInterface $c, array $params)
    {
        $this->c = $c;
        $this->params = $params;
        $this->init();
    }

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
        $this->result = new MailResult($this, $this->c['logger']);
        return $this;
    }

    /**
     * Initialize the Email Data
     *
     * @param boolean $clearAttachments clear switch
     * 
     * @return object
     */
    public function clear($clearAttachments = false)
    {
        $this->subject = "";
        $this->body = "";
        $this->headerStr = "";
        $this->replytoFlag = false;
        $this->recipients = array();
        $this->headers = array();
        $this->attachCount = 0;
        $this->setHeader('User-Agent', $this->getUserAgent());
        $this->setDate();
        if ($clearAttachments !== false) {
            $this->attachmentsName = array();
            $this->attachmentsType = array();
            $this->attachmentsDisp = array();
            $this->attachmentsContent = array();
        }
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
        if (preg_match('/\<(.*)\>/', $from, $match)) {
            $from = $match['1'];
        }
        $this->validateEmail(Utils::strToArray($from));
        if ($name != '') {  // Prepare the display name
                            // Only use Q encoding if there are characters that would require it
            if (! preg_match('/[\200-\377]/', $name)) {
                // add slashes for non-printing characters, slashes, and double quotes, and surround it in double quotes
                $name = '"' . addcslashes($name, "\0..\37\177'\"\\") . '"';
            } else {
                $name = Utils::prepQencoding($name, true);
            }
        }
        $this->fromName = $name;
        $this->fromEmail = $from;
        return $this;
    }

    /**
     * Set Recipients
     *
     * @param string $to source emails
     * 
     * @return object
     */
    public function to($to)
    {
        $to = Utils::strToArray($to);
        $this->setHeader('To', implode(", ", $to));

        $to = Utils::formatEmail($to);
        $this->validateEmail($to);

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
        if (empty($cc)) {
            return $this;
        }
        $cc = Utils::strToArray($cc);
        $this->setHeader('Cc', implode(", ", $cc));

        $cc = Utils::formatEmail($cc);
        $this->validateEmail($cc);

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
        if (empty($bcc)) {
            return $this;
        }
        $bcc = Utils::strToArray($bcc);
        $bcc = Utils::formatEmail($bcc);
        $this->validateEmail($bcc);

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
     * Set RFC 822 Date
     *
     * @param string $newDate set custom date
     * 
     * @return string
     */
    public function setDate($newDate = null)
    {
        if (! is_null($newDate)) {
            $this->setHeader('Date', $newDate);
            return $newDate;
        }
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
    public function setMailtype($type = 'text')
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

        $this->attachments[$this->attachCount]['disposition'] = $disposition;
        $this->attachments[$this->attachCount]['type'] = $file->mimeTypes($mimes);
        $this->attachments[$this->attachCount]['name'] = $name;
        $this->attachments[$this->attachCount]['fileurl'] = $filename;
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
    }

    /**
     * Build attachments
     * 
     * @return void
     */
    protected function buildAttachments()
    {
        if (count($this->attachments) > 0) {
            $i = 0;
            $j = 0;
            foreach ($this->attachments as $value) {
                if ($value['disposition'] == 'attachment') {
                    $this->msgEvent['attachments'][$i]['type'] = $value['type'];
                    $this->msgEvent['attachments'][$i]['name'] = $value['name'];
                    if (! $content = file_get_contents($value['fileurl'])) {
                        $this->setAttachmentError($value['fileurl']);
                    }
                    $this->msgEvent['attachments'][$i]['content'] = ($content == false) ? null : base64_encode($content);
                    ++$i;
                } else {
                    $this->msgEvent['images'][$j]['type'] = $value['type'];
                    $this->msgEvent['images'][$j]['name'] = $value['name'];
                    if (! $content = file_get_contents($value['fileurl'])) {
                        $this->setAttachmentError($value['fileurl']);
                    }
                    $this->msgEvent['images'][$j]['content']  = ($content == false) ? null : base64_encode($content);
                    ++$j;
                }
            }
        }
    }

    /**
     * Attachment Errors
     * 
     * @param string $fileurl url
     * 
     * @return void
     */
    protected function setAttachmentError($fileurl)
    {
        $result = $this->getMailResult();
        $result->setCode($result::ATTACHMENT_UNREADABLE);
        $result->setMessage($this->c['translator']->get('OBULLO:MAILER:ATTACHMENT_UNREADABLE', $fileurl));
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
            $result = $this->getMailResult();
            $result->setCode($result::NO_RECIPIENTS);
            $result->setMessage($this->c['translator']['OBULLO:MAILER:NO_RECIPIENTS']);
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
     * @return object MailResult
     */
    public function queue()
    {
        $this->checkEmail();
        $this->writeHeaders();
        return $this->queueEmail();
    }

    /**
     * Queue mail event
     * 
     * @return object MailResult
     */
    protected function queueEmail()
    {
        $this->buildEvent();
        return $this->push($this->msgEvent);
    }

    /**
     * Send new AMQP message
     *
     * @param array $msgEvent queue data
     * 
     * @return object MailResult
     */
    protected function push(array $msgEvent)
    {
        $route = $this->params['queue']['route'];

        $push = $this->c->get('queue')
            ->channel($this->params['queue']['channel'])
            ->push(
                'Workers\Mailer',
                $route,
                $msgEvent
            );
        $result = $this->getMailResult();
        if (! $push) {
            $result->setCode($result::QUEUE_ERROR);
            $result->setMessage($this->c['translator']->get('OBULLO:MAILER:QUEUE_FAILED', $route));
            $this->logger->error(
                'Mailer queue failed', 
                array(
                    'route' => $route,
                    'msgEvent' => $msgEvent
                )
            );
        } else {
            $result->setCode($result::SUCCESS);
        }
        return $result;

    }

    /* PUSH DATA
    {
        "message": {
            "mailer": "mandrill",  // smtp
            "mailtype" "html"      // text
            "html": "<p>Example HTML content</p>",
            "text": "Example text content",
            "subject": "example subject",
            "from_email": "message.from_email@example.com",
            "from_name": "Example Name",
            "to": [
                {
                    "email": "recipient.email@example.com",
                    "name": "Recipient Name",
                    "type": "to"
                }
            ],
            "headers": {
                "Reply-To": "message.reply@example.com"
            },
            "important": false,
            "auto_text": null,
            "auto_html": null,
            "inline_css": null,
            "tags": [
                "password-resets"
            ],
            "attachments": [
                {
                    "type": "text/plain",
                    "name": "myfile.txt",
                    "fileurl": "/var/www/myfile.text"
                }
            ],
            "images": [
                {
                    "type": "image/png",
                    "name": "file.png",
                    "fileurl": "http://www.example.com/images/file.png",
                }
            ]
        },
        "send_at": "example send_at"
    }
    */

    /**
     * Send new http post request
     *
     * @param string $url    post url
     * @param array  $params post data
     * 
     * @return string $result
     */
    // public function httpPostRequest($url, array $params)
    // {
    //     if (! extension_loaded('curl')) {
    //         throw new RuntimeException('Curl extension not installed');
    //     }
    //     $ch = curl_init($url);
    //     curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //     curl_setopt($ch, CURLOPT_HEADER, false);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    //     curl_setopt($ch, CURLOPT_TIMEOUT, 600);
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    //     curl_setopt($ch, CURLOPT_VERBOSE, false);

    //     $start = microtime(true);
    //     $body  = curl_exec($ch);
    //     $time  = microtime(true) - $start;
    //     $this->logger->debug('Mailer api response', array('body' => $body, 'time' => number_format($time * 1000, 2) . 'ms'));
    
    //     if (curl_errno($ch)) {
    //         $this->logger->error('Mailer api failed', array('url' => $url, 'error' => curl_error($ch)));
    //         $this->debugMsg[] = $this->c['translator']->get('OBULLO:MAILER:API_ERROR', $url, curl_error($ch));
    //     }
    //     $result['body'] = $body;
    //     $result['info'] = curl_getinfo($ch);
    //     curl_close($ch);

    //     return $result;
    // }

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
     * Returns to recipients
     * 
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
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
     * Returns From Name <from@email>
     * 
     * @return string
     */
    public function getFrom()
    {
        return $this->fromName." &lt;".$this->fromEmail."&gt;";
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
     * Returns mailt type: html / text
     * 
     * @return string
     */
    public function getMailType()
    {
        return $this->mailtype;
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
                $result->setMessage($this->c['translator']->get($this->validator->getError(), $this->validator->getValue()));
            }
        }
        return $this;
    }

}