<?php

namespace Obullo\Mail\Send\Protocol;

use Obullo\Mail\Send\Adapter,
    Obullo\Mail\Text;

/**
 * Smtp Protocol Class
 * 
 * @category  Smtp
 * @package   Mail
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/mail
 */
Class Smtp extends Adapter
{
    public $smtpAuth = false;
    public $smtpConnect = '';
    public $smtpHost = '';        // SMTP Server.  Example: mail.earthlink.net
    public $smtpUser = '';        // SMTP Username
    public $smtpPass = '';        // SMTP Password
    public $smtpPort = '25';      // SMTP Port
    public $smtpTimeout = 5;      // SMTP Timeout in seconds

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $config preferences
     */
    public function __construct($c, $config = array())
    {
        parent::__construct($c, $config);

        $smtp = $config['send']['protocol']['smtp'];

        $this->smtpHost = $smtp['host'];
        $this->smtpUser = $smtp['user'];
        $this->smtpPass = $smtp['pass'];
        $this->smtpPort = $smtp['port'];
        $this->smtpTimeout = $smtp['timeout'];
        $this->smtpAuth = ($this->smtpUser == '' AND $this->smtpPass == '') ? false : true;
    }

    /**
     * SMTP Connect
     * 
     * @return string
     */
    public function connect()
    {
        $this->smtpConnect = fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, $this->smtpTimeout);
        if ( ! is_resource($this->smtpConnect)) {
            $this->setErrorMessage('OBULLO:MAIL:SMTP_ERROR', $errno . " " . $errstr);
            return false;
        }
        $this->setErrorMessage($this->getData());
        return $this->sendCommand('hello');
    }

    /**
     * Send SMTP command
     * 
     * @param string $cmd  command
     * @param string $data data
     * 
     * @return string
     */
    public function sendCommand($cmd, $data = '')
    {
        switch ($cmd) {
        case 'hello' :
            if ($this->smtpAuth OR $this->getEncoding() == '8bit')
                $this->sendData('EHLO ' . $this->getHostname());
            else
                $this->sendData('HELO ' . $this->getHostname());
            $resp = 250;
            break;
        case 'from' :
            $this->sendData('MAIL FROM:<' . $data . '>');
            $resp = 250;
            break;
        case 'to' :
            $this->sendData('RCPT TO:<' . $data . '>');
            $resp = 250;
            break;
        case 'data' :
            $this->sendData('DATA');
            $resp = 354;
            break;
        case 'quit' :
            $this->sendData('QUIT');
            $resp = 221;
            break;
        }
        $reply = $this->getData();
        $this->debugMsg[] = "<pre>" . $cmd . ": " . $reply . "</pre>";

        if (substr($reply, 0, 3) != $resp) {
            $this->setErrorMessage('OBULLO:MAIL:SMTP_ERROR', $reply);
            return false;
        }
        if ($cmd == 'quit') {
            fclose($this->smtpConnect);
        }
        return true;
    }

    /**
     * SMTP Authenticate
     * 
     * @return  bool
     */
    public function authenticate()
    {
        if ( ! $this->smtpAuth) {
            return true;
        }
        if ($this->smtpUser == "" AND $this->smtpPass == "") {
            $this->setErrorMessage('OBULLO:MAIL:NO_SMTP_UNPW');
            return false;
        }
        $this->sendData('AUTH LOGIN');
        $reply = $this->getData();
        if (strncmp($reply, '334', 3) != 0) {
            $this->setErrorMessage('OBULLO:MAIL:FAILED_SMTP_LOGIN', $reply);
            return false;
        }
        $this->sendData(base64_encode($this->smtpUser));
        $reply = $this->getData();
        if (strncmp($reply, '334', 3) != 0) {
            $this->setErrorMessage('OBULLO:MAIL:SMTP_AUTH_UN', $reply);
            return false;
        }
        $this->sendData(base64_encode($this->smtpPass));
        $reply = $this->getData();
        if (strncmp($reply, '235', 3) != 0) {
            $this->setErrorMessage('OBULLO:MAIL:SMTP_AUTH_PW', $reply);
            return false;
        }
        return true;
    }

    /**
     * Send SMTP data
     *
     * @param string $data body
     * 
     * @return bool
     */
    public function sendData($data)
    {
        if ( ! fwrite($this->smtpConnect, $data . $this->newline)) {
            $this->setErrorMessage('OBULLO:MAIL:SMTP_DATA_FAILURE', $data);
            return false;
        }
        return true;
    }

    /**
     * Get SMTP data
     *
     * @return string
     */
    public function getData()
    {
        $data = "";
        while ($str = fgets($this->smtpConnect, 512)) {
            $data .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }
        return $data;
    }

    /**
     * Set Recipients
     *
     * @param string $to source emails
     * 
     * @return void
     */
    public function to($to)
    {
        $to = $this->strToArray($to);
        $to = $this->cleanEmail($to);
        if ($this->validate) {
            $this->validator->validateEmail($to);
            if ($this->validator->isError()) {
                $this->setErrorMessage($this->validator->getError(), $this->validator->getValue());
            }
        }
        $this->setHeader('To', implode(", ", $to));
        $this->recipients = $to;
    }

    /**
     * Set Cc
     *
     * @param mixed $cc carbon copy addresses
     * 
     * @return void
     */
    public function cc($cc = null)
    {
        if (empty($cc)) {
            return;
        }
        $cc = $this->strToArray($cc);
        $cc = $this->cleanEmail($cc);
        if ($this->validate) {
            $this->validator->validateEmail($cc);
            if ($this->validator->isError()) {
                $this->setErrorMessage($this->validator->getError(), $this->validator->getValue());
            }
        }
        $this->setHeader('Cc', implode(", ", $cc));
        $this->ccArray = $cc;
    }

    /**
     * Set BCC
     *
     * @param mixed $bcc   blind carbon copy addresses
     * @param mixed $limit batch size
     * 
     * @return void
     */
    public function bcc($bcc = null, $limit = '')
    {
        if (empty($bcc)) {
            return;
        }
        if ($limit != '' && is_numeric($limit)) {
            $this->bccBatchMode = true;
            $this->bccBatchSize = $limit;
        }
        $bcc = $this->strToArray($bcc);
        $bcc = $this->cleanEmail($bcc);
        if ($this->validate) {
            $this->validator->validateEmail($bcc);
            if ($this->validator->isError()) {
                $this->setErrorMessage($this->validator->getError(), $this->validator->getValue());
            }
        }
        $this->bccArray = $bcc;
    }

    /**
     * Build final headers
     * 
     * @return void
     */
    public function buildHeaders()
    {
        $this->setHeader('X-Sender', $this->cleanEmail($this->headers['From']));
        $this->setHeader('X-Mailer', $this->useragent);
        $this->setHeader('X-Priority', $this->priorities[$this->priority - 1]);
        $this->setHeader('Message-ID', $this->getMessageId());
        $this->setHeader('Mime-Version', '1.0');
    }

    /**
     * Write Headers as a string
     *
     * @return    void
     */
    public function writeHeaders()
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
     * Build Final Body and attachments
     * 
     * @return void
     */
    public function buildMessage()
    {
        if ($this->wordwrap === true AND $this->mailtype != 'html') {
            $text = new Text($this->newline);
            $this->body = $text->wordWrap($this->body, $this->wrapchars);
        }
        $this->setBoundaries();
        $this->writeHeaders();
        $hdr = '';
        switch ($this->getContentType()) {
        case 'plain' :
            $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
            $hdr .= "Content-Transfer-Encoding: " . $this->getEncoding();
            $hdr .= $this->newline . $this->newline . $this->body;
            $this->finalbody = $hdr;
            return;
            break;
        case 'html' :
            if ($this->sendMultipart === false) {
                $hdr .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: quoted-printable";
            } else {
                $hdr .= "Content-Type: multipart/alternative; boundary=\"" . $this->altBoundary . "\"" . $this->newline . $this->newline;
                $hdr .= $this->getMimeMessage() . $this->newline . $this->newline;
                $hdr .= "--" . $this->altBoundary . $this->newline;
                $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: " . $this->getEncoding() . $this->newline . $this->newline;
                $hdr .= $this->getAltMessage() . $this->newline . $this->newline . "--" . $this->altBoundary . $this->newline;
                $hdr .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: quoted-printable";
            }
            $this->body = $this->prepQuotedPrintable($this->body);
            $hdr .= $this->newline . $this->newline;
            $hdr .= $this->body . $this->newline . $this->newline;
            if ($this->sendMultipart !== false) {
                $hdr .= "--" . $this->altBoundary . "--";
            }
            $this->finalbody = $hdr;
            return;
            break;
        case 'plain-attach' :
            $hdr .= "Content-Type: multipart/" . $this->multipart . "; boundary=\"" . $this->atcBoundary . "\"" . $this->newline . $this->newline;
            $hdr .= $this->getMimeMessage() . $this->newline . $this->newline;
            $hdr .= "--" . $this->atcBoundary . $this->newline;
            $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
            $hdr .= "Content-Transfer-Encoding: " . $this->getEncoding();
            $hdr .= $this->newline . $this->newline;
            $hdr .= $this->body . $this->newline . $this->newline;
            break;
        case 'html-attach' :
            $hdr .= "Content-Type: multipart/" . $this->multipart . "; boundary=\"" . $this->atcBoundary . "\"" . $this->newline . $this->newline;
            $hdr .= $this->getMimeMessage() . $this->newline . $this->newline;
            $hdr .= "--" . $this->atcBoundary . $this->newline;
            $hdr .= "Content-Type: multipart/alternative; boundary=\"" . $this->altBoundary . "\"" . $this->newline . $this->newline;
            $hdr .= "--" . $this->altBoundary . $this->newline;
            $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
            $hdr .= "Content-Transfer-Encoding: " . $this->getEncoding() . $this->newline . $this->newline;
            $hdr .= $this->getAltMessage() . $this->newline . $this->newline . "--" . $this->altBoundary . $this->newline;
            $hdr .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
            $hdr .= "Content-Transfer-Encoding: quoted-printable";
            $this->body = $this->prepQuotedPrintable($this->body);
            $hdr .= $this->newline . $this->newline;
            $hdr .= $this->body . $this->newline . $this->newline;
            $hdr .= "--" . $this->altBoundary . "--" . $this->newline . $this->newline;
            break;
        }
        $attachment = array();
        $z = 0;
        for ($i = 0; $i < count($this->attachName); $i++) {
            $filename = $this->attachName[$i];
            $basename = basename($filename);
            $ctype = $this->attachType[$i];
            if ( ! file_exists($filename)) {
                $this->setErrorMessage('OBULLO:MAIL:ATTACHMENT_MISSING', $filename);
                return false;
            }
            $h = "--" . $this->atcBoundary . $this->newline;
            $h .= "Content-type: " . $ctype . "; ";
            $h .= "name=\"" . $basename . "\"" . $this->newline;
            $h .= "Content-Disposition: " . $this->attachDisp[$i] . ";" . $this->newline;
            $h .= "Content-Transfer-Encoding: base64" . $this->newline;
            $attachment[$z++] = $h;
            $file = filesize($filename) + 1;
            if (!$fp = fopen($filename, 'rb')) {
                $this->setErrorMessage('OBULLO:MAIL:ATTACHMENT_UNREADABLE', $filename);
                return false;
            }
            $attachment[$z++] = chunk_split(base64_encode(fread($fp, $file)));
            fclose($fp);
        }
        $this->finalbody = $hdr . implode($this->newline, $attachment) . $this->newline . "--" . $this->atcBoundary . "--";
        return;
    }

    /**
     * Execute send operation
     * 
     * @return boolean
     */
    public function spoolEmail()
    {
        $this->unwrapSpecials();
        if ( ! $this->_sendWithSmtp()) {
            $this->setErrorMessage('OBULLO:MAIL:SEND_FAILURE_SMTP');
            return false;
        }
        $this->setErrorMessage('OBULLO:MAIL:SENT', 'smtp');
        return true;
    }

    /**
     * Run smtp operation
     * 
     * @return boolean
     */
    private function _sendWithSmtp()
    {
        if ($this->smtpHost == '') {
            $this->setErrorMessage('OBULLO:MAIL:NO_HOSTNAME');
            return false;
        }
        $this->connect();
        $this->authenticate();
        $this->sendCommand('from', $this->cleanEmail($this->headers['From']));

        foreach ($this->recipients as $val) {
            $this->sendCommand('to', $val);
        }
        if (count($this->ccArray) > 0) {
            foreach ($this->ccArray as $val) {
                if ($val != "") {
                    $this->sendCommand('to', $val);
                }
            }
        }
        if (count($this->bccArray) > 0) {
            foreach ($this->bccArray as $val) {
                if ($val != "") {
                    $this->sendCommand('to', $val);
                }
            }
        }
        $this->sendCommand('data');
        // Perform dot transformation on any lines that begin with a dot
        $this->sendData($this->headerStr . preg_replace('/^\./m', '..$1', $this->finalbody));
        $this->sendData('.');
        $reply = $this->getData();
        $this->setErrorMessage($reply);
        if (strncmp($reply, '250', 3) != 0) {
            $this->setErrorMessage('OBULLO:MAIL:SMTP_ERROR', $reply);
            return false;
        }
        $this->sendCommand('quit');
        return true;
    }

    /**
     * Batch Bcc Send. Sends groups of BCCs in batches
     *
     * @return bool
     */
    public function batchBccSend()
    {
        $float = $this->bccBatchSize - 1;
        $set = "";
        $chunk = array();
        for ($i = 0; $i < count($this->bccArray); $i++) {
            if (isset($this->bccArray[$i])) {
                $set .= ", " . $this->bccArray[$i];
            }
            if ($i == $float) {
                $chunk[] = substr($set, 1);
                $float = $float + $this->bccBatchSize;
                $set = "";
            }
            if ($i == count($this->bccArray) - 1) {
                $chunk[] = substr($set, 1);
            }
        }
        for ($i = 0; $i < count($chunk); $i++) {
            unset($this->headers['Bcc']);
            unset($bcc);
            $bcc = $this->strToArray($chunk[$i]);
            $bcc = $this->cleanEmail($bcc);
            $this->bccArray = $bcc;
            $this->buildMessage();
            $this->spoolEmail();
        }
    }

}

// END Smtp class

/* End of file Smtp.php */
/* Location: .Obullo/Mail/Send/Protocol/Smtp.php */