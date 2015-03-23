<?php

namespace Obullo\Mailer\Transport;

use Obullo\Mailer\Text;
use Obullo\Container\Container;

/**
 * Sendmail Protocol Class
 * 
 * @category  Sendmail
 * @package   Mail
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 */
class Sendmail extends StandartMailer implements MailerInterface
{
    /**
     * Sendmail path
     * 
     * @var string
     */
    public $mailpath;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        parent::__construct($c);

        $this->mailpath = $this->c['config']->load('mailer/sendmail')['path'];
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
        $this->setHeader('To', implode(", ", $to));
        if ($this->validate) {
            $this->validator->validateEmail($to);
            if ($this->validator->isError()) {
                $this->setErrorMessage($this->validator->getError(), $this->validator->getValue());
            }
        }
        $this->recipients = implode(", ", $to);
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
    }

    /**
     * Set BCC
     *
     * @param mixed $bcc   blind carbon copy addresses
     * @param mixed $limit batch size
     * 
     * @return void
     */
    public function bcc($bcc = null, $limit = null)
    {
        if (empty($bcc)) {
            return;
        }
        if ($limit != null && is_numeric($limit)) {
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
        if (($this->bccBatchMode && count($bcc) > $this->bccBatchSize)) {
            $this->bccArray = $bcc;
        } else {
            $this->setHeader('Bcc', implode(", ", $bcc));
        }
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
                $this->setErrorMessage('OBULLO:MAILER:ATTACHMENT_MISSING', $filename);
                return false;
            }
            $h = "--" . $this->atcBoundary . $this->newline;
            $h .= "Content-type: " . $ctype . "; ";
            $h .= "name=\"" . $basename . "\"" . $this->newline;
            $h .= "Content-Disposition: " . $this->attachDisp[$i] . ";" . $this->newline;
            $h .= "Content-Transfer-Encoding: base64" . $this->newline;
            $attachment[$z++] = $h;
            $file = filesize($filename) + 1;
            if ( ! $fp = fopen($filename, 'rb')) {
                $this->setErrorMessage('OBULLO:MAILER:ATTACHMENT_UNREADABLE', $filename);
                return false;
            }
            $attachment[$z++] = chunk_split(base64_encode(fread($fp, $file)));
            fclose($fp);
        }
        $this->finalbody = $hdr . implode($this->newline, $attachment) . $this->newline . "--" . $this->atcBoundary . "--";
    }

    /**
     * Execute send operation
     * 
     * @return boolean
     */
    public function spoolEmail()
    {
        $this->unwrapSpecials();
        if ( ! $this->_sendWithSendMail()) {
            $this->setErrorMessage('OBULLO:MAILER:SEND_FAILURE_SENDMAIL');
            return false;
        }
        $this->setErrorMessage('OBULLO:MAILER:SENT', 'mail');
        return true;
    }

    /**
     * Run smtp operation
     * 
     * @return boolean
     */
    private function _sendWithSendmail()
    {
        $fp = @popen($this->mailpath . " -oi -f " . $this->cleanEmail($this->headers['From']) . " -t", 'w');
        if ($fp === false OR $fp === null) { // Server probably has popen disabled, so nothing we can do to get a verbose error.
            return false;
        }
        fputs($fp, $this->headerStr);
        fputs($fp, $this->finalbody);
        $status = pclose($fp);
        if ($status != 0) {
            $this->setErrorMessage('OBULLO:MAILER:EXIT_STATUS', $status);
            $this->setErrorMessage('OBULLO:MAILER:NO_SOCKET');
            return false;
        }
        return true;
    }

    /**
     * Batch Bcc Send.  Sends groups of BCCs in batches
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
            $this->setHeader('Bcc', implode(", ", $bcc));
            $this->buildMessage();
            $this->spoolEmail();
        }
    }

}

// END Sendmail class

/* End of file Sendmail.php */
/* Location: .Obullo/Mailer/Transport//Sendmail.php */