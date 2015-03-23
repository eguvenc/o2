<?php

namespace Obullo\Mailer;

use Obullo\Mailer\Response;
use Obullo\Container\Container;
use Obullo\Mailer\Transport\HttpMailer;
use Obullo\Mailer\Transport\MailerInterface;

/**
 * Queue Mailer Transport
 *
 * @category  Mail
 * @package   Queue
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 */
class Queue extends HttpMailer implements MailerInterface
{
    /**
     * Logger
     * 
     * @var object
     */
    public $logger;

    /**
     * Queue payload
     * 
     * @var array
     */
    public $message;

    /**
     * Queue service
     * 
     * @var object
     */
    protected $queueService;

    /**
     * Config params
     *
     * @var array
     */
    protected $config;

    /**
     * Mailer name
     * 
     * @var string
     */
    protected $mailer = 'smtp';

    /**
     * Queue push response array
     * 
     * @var array
     */
    protected $responseBody = array();

    /**
     * Create a Queue transport instance.
     *
     * @param object $c container
     * 
     * @return void
     */
    public function __construct(Container $c)
    {
        $this->queueService = $c['queue'];
        $this->config = $c['config']->load('mailer/transport');

        $this->logger = $c['logger'];
        $this->logger->debug('Mailer Queue Class Initialized');

        parent::__construct($c);
    }

    /**
     * Set mail driver: sendmail, mail, smtp, mandrill ..
     * 
     * @param string $mailer driver name
     *
     * @return void
     */
    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
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
        $route = $this->config['queue']['route'];

        $this->queueService->channel($this->config['queue']['channel']);   // Push
        $push = $this->queueService->push($this->config['queue']['worker'], $route, $payload);

        $this->debugMsg[] = ($push) ? 'Mailer push success.' : 'Mailer push failed.';

        $time = microtime(true) - $start;
        $this->logger->debug('Queue mailer push success', array('payload' => $payload, 'time' => number_format($time * 1000, 2) . 'ms'));
    
        if ( ! $push) {
            $this->logger->error('Queue mailer push failed', array('route' => $route));
            $this->debugMsg[] = $this->c['translator']->sprintf('OBULLO:MAILER:QUEUE_MAILER_PUSH_FAILED', $route);
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
            $this->message['mailer'] =  $this->mailer;
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

        /**
         * PUSH DATA TO QUEUE
         */
        $push = $this->responseBody['array']['push'] = $this->push($this->message);
        $this->responseBody['info']['body'] = $this->message;

        if ( ! $push) {
            $this->logger->error(
                'Queue mailer push failed', 
                array(
                    'url' => $this->config['queue']['route'],
                    'body' => $this->message,
                    'info' => 'Queue push failed'
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
     * @param string $key response key
     * 
     * @return object
     */
    public function __get($key)
    {
        if ($key == 'response') {
            return new Response($this->responseBody);
        }
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

// END Queue class
/* End of file Queue.php */

/* Location: .Obullo/Mailer/Queue.php */