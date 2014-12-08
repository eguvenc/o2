<?php

namespace Obullo\Mail;

use Obullo\Mail\Transport\AbstractAdapter,
    LogicException;

/**
 * Queue Mailer Transport
 *
 * @category  Mail
 * @package   Transactional
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/mail
 * @link      https://mandrillapp.com/api/docs/messages.JSON.html
 */
Class QueueMailer extends AbstractAdapter
{
    /**
     * Config params
     *
     * @var array
     */
    protected $config;

    /**
     * Queue service
     * 
     * @var object
     */
    protected $queue;

    /**
     * Logger service
     * 
     * @var object
     */
    public $logger;

    /**
     * Create a Queue transport instance.
     *
     * @param object $c      container
     * @param array  $config configuration array
     * 
     * @return void
     */
    public function __construct($c, $config = array())
    {
        $this->config = $config;
        $this->queue = $c->load('service/queue');
        $this->logger = $c->load('service/logger');
        $this->logger->debug('QueueMailer Class Initialized');

        parent::__construct($c, $config);
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
        $this->setHeader('To', implode(", ", $to));

        $to = $this->formatEmail($to);
        if ($this->validate) {
            $this->validator->validateEmail($to);
            if ($this->validator->isError()) {
                $this->debugMsg[] = $this->c['translator']->sprintf($this->validator->getError(), $this->validator->getValue());
            }
        }
        foreach ($to as $value) {
            $this->recipients[] = array('type' => 'to', 'email' => $value['email'], 'name' => $value['name']);
        }
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
        $cc = $this->formatEmail($cc);
        if ($this->validate) {
            $this->validator->validateEmail($cc);
            if ($this->validator->isError()) {
                $this->debugMsg[] = $this->c['translator']->sprintf($this->validator->getError(), $this->validator->getValue());
            }
        }
        foreach ($cc as $value) {
            $this->recipients[] = array('type' => 'cc', 'email' => $value['email'], 'name' => $value['name']);
        }
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
        $bcc = $this->formatEmail($bcc);
        if ($this->validate) {
            $this->validator->validateEmail($bcc);
            if ($this->validator->isError()) {
                $this->debugMsg[] = $this->c['translator']->sprintf($this->validator->getError(), $this->validator->getValue());
            }
        }
        foreach ($bcc as $value) {
            $this->recipients[] = array('type' => 'bcc', 'email' => $value['email'], 'name' => $value['name']);
        }
    }

    /**
     * Build mail body
     * 
     * @return void
     */
    protected function buildMessage()
    {    
        if ($this->mailtype == 'html') {
            $this->message['html'] = $this->body;
        }
        if ($this->wordwrap === true AND $this->mailtype != 'html') {
            $text = new Text($this->newline);
            $this->body = $text->wordWrap($this->body, $this->wrapchars);
            $this->message['text'] = $this->body;
        }
    }

    /**
     * Build mandrill api attachments
     *
     * @see https://mandrillapp.com/api/docs/messages.JSON.html
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
                    $this->message['attachments'][$i]['type'] = $value['type'];
                    $this->message['attachments'][$i]['name'] = $value['name'];
                    $this->message['attachments'][$i]['fileurl'] = $value['fileurl'];
                    ++$i;
                } else {
                    $this->message['images'][$j]['type'] = $value['type'];
                    $this->message['images'][$j]['name'] = $value['name'];
                    $this->message['images'][$j]['fileurl'] = $value['fileurl'];
                    ++$j;
                }
            }
        }
    }

    /**
     * Creates message array items
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
     * Send new AMQP message
     *
     * @param array $payload queue data
     * 
     * @return boolean
     */
    protected function push(array $payload)
    {
        $start = microtime(true);

        $route = $this->config['send']['queue']['route'];

        $this->queue->channel($this->config['send']['queue']['channel']);   // Push
        $push = $this->queue->push($this->config['send']['queue']['worker'], $route, $payload);
        $this->debugMsg[] = ($push) ? 'QueueMailer push success.' : 'QueueMailer push failed.';

        $time = microtime(true) - $start;
        $this->logger->debug('Queue mailer push', array('message' => $payload['message'], 'time' => number_format($time * 1000, 2) . 'ms'));
    
        if ( ! $push) {
            $this->logger->error('Queue mailer push failed', array('route' => $route));
            $this->debugMsg[] = $this->c['translator']->sprintf('OBULLO:MAIL:QUEUE_MAILER_PUSH_FAILED', $route);
        }
        return $push;
    }

    /**
     * Send email with Queue
     * 
     * @return boelean
     */
    public function spoolEmail()
    {
        if ( ! isset($this->message['mailer'])) {
            $this->message['mailer'] = $this->config['send']['queue']['mailer'];
        }
        $this->message['mailtype'] = $this->getMailType();
        $this->message['subject'] = $this->subject;
        $this->message['from_email'] = $this->fromEmail;
        $this->message['from_name'] = $this->fromName;

        foreach ($this->recipients as $value) {
            $this->message['to'][] = $value;
        }
        if (count($this->headers) > 0) {
            foreach ($this->headers as $key => $value) {
                $this->message['headers'][$key] = $value;
            }
        }
        $this->message['send_at'] = $this->setDate();

        $this->buildAttachments();

        $push = $this->push(array('message' => $this->message));

        if ( ! $push) {
            $this->logger->error(
                'Queue mailer push failed', 
                array(
                    'route' => $this->config['send']['queue']['route'],
                    'message' => $this->message,
                    )
            );
            return false;
        }
        if (empty($this->debugMsg)) {
            return true;
        }
        return false;
    }

    /**
     * Returns to response object
     * 
     * @return object
     */
    public function response()
    {   
        throw new LogicException('QueueMailer class has not got a response. Don\'t use this method.');
    }

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the API key being used by the transport.
     *
     * @param string $key api key
     * 
     * @return void
     */
    public function setKey($key)
    {
        return $this->key = $key;
    }

}

// END QueueMailer class
/* End of file QueueMailer.php */

/* Location: .Obullo/Mail/QueueMailer.php */