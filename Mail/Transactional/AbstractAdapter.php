<?php

namespace Obullo\Log;

use Obullo\Mail\Text,
    Obullo\Mail\Validator,
    Obullo\Mail\File;

/**
 * AbstractAdapter
 * 
 * @category  Mail
 * @package   Transactional
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/mail
 */
Abstract Class AbstractAdapter
{
    public $useragent = 'Framework';
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
    public $finalbody = '';
    public $altBoundary = '';
    public $atcBoundary = '';
    public $altMessage = '';        // Alternative message for HTML emails
    public $headerStr = '';
    public $replytoFlag = false;
    public $debugMsg = array();
    public $recipients = array();
    public $ccArray = array();
    public $bccArray = array();
    public $headers = array();
    public $attachName = array();
    public $attachType = array();
    public $attachDisp = array();
    public $attachContent = array();
    // public $baseCharsets = array('us-ascii', 'iso-2022-');    // 7-bit charsets (excluding language suffix)
    // public $priorities = array('1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)');
    public $logger;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $config preferences
     */
    public function __construct($c, $config = array())
    {
        $this->c = $c;
        $this->init($config['settings']);

        $this->logger = $c->load('service/logger');
        $this->logger->debug('Mail Class Initialized');
    }

    /**
     * Constructor
     * 
     * @param array $config preferences
     * 
     * @return object
     */
    public function init($config = array())
    {
        $this->clear();
        foreach ($config as $key => $val) {
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
        $this->finalbody = "";
        $this->headerStr = "";
        $this->replytoFlag = false;
        $this->recipients = array();
        $this->headers = array();
        $this->debugMsg = array();

        $this->setHeader('User-Agent', $this->useragent);
        $this->setHeader('Date', $this->setDate());

        if ($clearAttachments !== false) {
            $this->attachName = array();
            $this->attachType = array();
            $this->attachDisp = array();
            $this->attachContent = array();
        }
    }

    /**
     * Send new http post request
     *
     * @param string $url  post url
     * @param array  $post post data
     * 
     * @return string $result
     */
    protected function post($url, array $post)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
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
        return sprintf("%s %s%04d", date("D, j M Y H:i:s"), $operator, $floorTimezone);
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
        $this->fromName = $name;
        $this->fromEmail = $from;

        // $this->setHeader('From', $name . ' <' . $from . '>');
        // $this->setHeader('Return-Path', '<' . $from . '>');
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
        $this->subject = $this->prepQencoding($subject);
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
        $this->attachContent[] = file_get_contents($filename);
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
        if (( ! isset($this->recipients) AND ! isset($this->headers['To']) ) 
            AND ( ! isset($this->bccArray) AND ! isset($this->headers['Bcc'])) 
            AND ( ! isset($this->headers['Cc']))
        ) {
            $this->setErrorMessage('OBULLO:MAIL:NO_RECIPIENTS');
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
     * Prep Q Encoding
     *
     * Performs "Q Encoding" on a string for use in email headers.  It's related
     * but not identical to quoted-printable, so it has its own method
     *
     * @param str  $str  string
     * @param bool $from set to true for processing From: headers
     * 
     * @return string
     */
    public function prepQencoding($str, $from = false)
    {
        $str = str_replace(array("\r", "\n"), array('', ''), $str);
        // Line length must not exceed 76 characters, so we adjust for
        // a space, 7 extra characters =??Q??=, and the charset that we will add to each line
        $limit = 75 - 7 - strlen($this->charset);
        // these special characters must be converted too
        $convert = array('_', '=', '?');
        if ($from === true) {
            $convert[] = ',';
            $convert[] = ';';
        }
        $output = '';
        $temp = '';
        for ($i = 0, $length = strlen($str); $i < $length; $i++) {
            $char = substr($str, $i, 1); // Grab the next character
            $ascii = ord($char);
            if ($ascii < 32 OR $ascii > 126 OR in_array($char, $convert)) { // convert ALL non-printable ASCII characters and our specials
                $char = '=' . dechex($ascii);
            }
            if ($ascii == 32) { // handle regular spaces a bit more compactly than =20
                $char = '_';
            }
            // If we're at the character limit, add the line to the output,
            // reset our temp variable, and keep on chuggin'
            if ((strlen($temp) + strlen($char)) >= $limit) {
                $output .= $temp . $this->crlf;
                $temp = '';
            }
            $temp .= $char; // Add the character to our temporary line
        }
        $str = $output . $temp;
        // wrap each line with the shebang, charset, and transfer encoding
        // the preceding space on successive lines is required for header "folding"
        $str = trim(preg_replace('/^(.*)$/m', ' =?' . $this->charset . '?Q?$1?=', $str));
        return $str;
    }

    /**
     * Convert a String to an Array
     *
     * @param mixed $email email data
     * 
     * @return array
     */
    protected function strToArray($email)
    {
        if ( ! is_array($email)) {
            if (strpos($email, ',') !== false) {
                $email = preg_split('/[\s,]/', $email, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $email = trim($email);
                settype($email, "array");
            }
        }
        return $email;
    }

    /**
     * Clean Extended Email Address: Joe Smith <joe@smith.com>
     *
     * @param string $email address
     * 
     * @return string
     */
    public function formatEmail($email)
    {
        if ( ! is_array($email)) {
            if (strpos($email, '<') > 0 AND preg_match('/(.*?)\<(.*)\>/', $email, $match)) {
                return array('email' => $match['1'], 'name' => $match[2]);
            } else {
                return array('email' => $email, 'name' => null);
            }
        }
        $formatted = array();
        foreach ($email as $address) {
            if (strpos($address, '<') > 0 AND preg_match('/(.*?)\<(.*)\>/', $address, $match)) {
                $formatted[] = array('email' => $match['1'], 'name' => $match[2]);
            } else {
                $formatted[] = array('email' => $address, 'name' => null);
            }
        }
        return $formatted;
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
        $this->c['translator']->load('mail');
        $this->debugMsg[] = $this->c['translator']->sprintf($msg, $val) . "<br />";
    }

}

// END AbstractLogger class
/* End of file AbstractLogger.php */

/* Location: .Obullo/Log/AbstractLogger.php */