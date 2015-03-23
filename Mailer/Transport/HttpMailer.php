<?php

namespace Obullo\Mailer\Transport;

use RuntimeException;
use Obullo\Mailer\Text;
use Obullo\Mailer\File;
use Obullo\Mailer\Validator;
use Obullo\Container\Container;

/**
 * Http Mailer - Send emails using HTTP request ( CURL )
 * 
 * @category  Mail
 * @package   Transport
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 */
abstract class HttpMailer
{
    use MailerTrait;

    public $useragent = 'Obullo Mailer';
    public $wordwrap = true;        // true/false  Turns word-wrap on/off
    public $wrapchars = 76;
    public $mailtype = 'text';      // text/html  Defines email formatting
    public $charset = 'utf-8';      // Default char set: iso-8859-1 or us-ascii
    public $validate = false;       // true/false.  Enables email validation
    public $priority = '3';         // Default priority (1 - 5)
    public $crlf = "\n";            // The RFC 2045 compliant CRLF for quoted-printable is "\r\n".  Apparently some servers,
    public $newline = "\n";         // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)

    public $validator;
    public $subject = '';
    public $body = '';
    public $altMessage = '';        // Alternative message for HTML emails
    public $replytoFlag = false;
    public $debugMsg = array();
    public $recipients = array();
    public $ccArray = array();
    public $bccArray = array();
    public $headers = array();
    public $attachments = array();
    public $logger;
    public $date;
    public $message = array();   // curl data
    
    protected $attachCount = 0;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
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
        $transport = $this->c['config']->load('mailer/transport');

        foreach ($transport as $key => $val) {
            if (isset($this->$key)) {
                $this->$key = $val;
            }
        }
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
        $this->headerStr = "";
        $this->replytoFlag = false;
        $this->recipients = array();
        $this->headers = array();
        $this->debugMsg = array();
        $this->attachCount = 0;

        $this->setHeader('User-Agent', $this->useragent);
        $this->setDate();

        if ($clearAttachments !== false) {
            $this->attachmentsName = array();
            $this->attachmentsType = array();
            $this->attachmentsDisp = array();
            $this->attachmentsContent = array();
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
                $this->debugMsg[] = $this->c['translator']->sprintf($this->validator->getError(), $this->validator->getValue());
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
        $this->fromName = $name;
        $this->fromEmail = $from;
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
                $this->debugMsg[] = $this->c['translator']->sprintf($this->validator->getError(), $this->validator->getValue());
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
     * Set Email Subject
     * 
     * @param string $subject email subject
     * 
     * @return void
     */
    public function subject($subject)
    {
        $this->subject = $this->prepQencoding($subject);
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
     * @param string $disposition default attachment or inline
     * 
     * @return void
     */
    public function attach($filename, $disposition = 'attachment')
    {
        $name = basename($filename);
        $mimes = explode('.', $name);
        $mimes = next($mimes);
        $file = new File;

        $this->attachments[$this->attachCount]['disposition'] = $disposition;
        $this->attachments[$this->attachCount]['type'] = $file->mimeTypes($mimes);
        $this->attachments[$this->attachCount]['name'] = $name;
        $this->attachments[$this->attachCount]['fileurl'] = $filename;
        ++$this->attachCount;
    }

    /**
     * Creates extra message array items for transport mailers
     * 
     * @param string $key item
     * @param mixed  $val value,
     *
     * @return void
     */
    public function addMessage($key, $val)
    {
        $this->message[$key] = $val;
    }

    /**
     * Send new http post request
     *
     * @param string $url    post url
     * @param array  $params post data
     * 
     * @return string $result
     */
    public function httpPostRequest($url, array $params)
    {
        if ( ! extension_loaded('curl')) {
            throw new RuntimeException('Curl extension not installed');
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_VERBOSE, false);

        $start = microtime(true);
        $body = curl_exec($ch);
        $time = microtime(true) - $start;
        $this->logger->debug('Transactional mail api call response', array('body' => $body, 'time' => number_format($time * 1000, 2) . 'ms'));
    
        if (curl_errno($ch)) {
            $this->logger->error('Transactional mail api call failed', array('url' => $url, 'error' => curl_error($ch)));
            $this->debugMsg[] = $this->c['translator']->sprintf('OBULLO:MAILER:API_CALL_FAILED', $url, curl_error($ch));
        }
        $result['raw'] = $body;
        $result['info'] = curl_getinfo($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Send Email
     *
     * @return bool
     */
    public function send()
    {
        if ($this->replytoFlag == false) {
            $this->replyTo($this->fromEmail, $this->fromName);
        }
        if (( ! isset($this->recipients) AND ! isset($this->headers['To']) ) 
            AND ( ! isset($this->bccArray) AND ! isset($this->headers['Bcc'])) 
            AND ( ! isset($this->headers['Cc']))
        ) {
            $this->debugMsg[] = $this->c['translator']['OBULLO:MAILER:NO_RECIPIENTS'];
            return false;
        }
        $this->buildMessage();
        if ( ! $this->spoolEmail()) {
            return false;
        } else {
            return true;
        }
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
                if ( ! is_array($val)) {
                    $val = array($val);
                }
                $msg .= '<pre>'.var_export($val, true).'</pre>';
            }
        }
        $msg .= "<pre>Headers: \n" . implode("\n", $this->headers) . "\n\nSubject:  \n" . htmlspecialchars($this->subject);
        $msg .= "\n\nMessage: \n" . htmlspecialchars($this->body);

        if ($this->response->getArray() != false) {
            $msg .= "\n\nResponse: \n" . var_export($this->response->getArray(), true);
        }
        return $msg.'</pre>';
    }

}

// END HttpMailer class
/* End of file HttpMailer.php */

/* Location: .Obullo/Mailer/Transport/HttpMailer.php */