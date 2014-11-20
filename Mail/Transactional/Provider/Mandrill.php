<?php 

namespace Obullo\Mail\Transactional;

/**
 * Mandrill Transactional Api Client
 * 
 * @category  Mail
 * @package   Transactional
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/mail
 */
Class Mandrill extends AbstractAdapter
{
    /**
     * The Mandrill API key.
     *
     * @var string
     */
    protected $key;

    /**
     * Create a new Mandrill transport instance.
     *
     * @param object $c      container
     * @param array  $config configuration array
     * 
     * @return void
     */
    public function __construct($c, $config = array())
    {
        $this->key = $config['send']['transactional']['key'];

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
        $to = $this->formatEmail($to);
        if ($this->validate) {
            $this->validator->validateEmail($to);
            if ($this->validator->isError()) {
                $this->setErrorMessage($this->validator->getError(), $this->validator->getValue());
            }
        }
        $this->setHeader('To', implode(", ", $to));
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
                $this->setErrorMessage($this->validator->getError(), $this->validator->getValue());
            }
        }
        $this->setHeader('Cc', implode(", ", $cc));
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
                $this->setErrorMessage($this->validator->getError(), $this->validator->getValue());
            }
        }
        foreach ($bcc as $value) {
            $this->recipients[] = array('type' => 'bcc', 'email' => $value['email'], 'name' => $value['name']);
        }
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
     * Build mail body
     * 
     * @return void
     */
    public function buildMessage()
    {    
        $this->message['html'] = $this->body;
        if ($this->wordwrap === true AND $this->mailtype != 'html') {
            $text = new Text($this->newline);
            $this->body = $text->wordWrap($this->body, $this->wrapchars);
            $this->message['text'] = $this->body;
        }
        $this->writeHeaders();
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

    /**
     * Send email with curl
     * 
     * @return void
     */
    public function spoolEmail()
    {
        // [
        //     'body' => [
        //         'key' => $this->key,
        //         'raw_message' => (string) $message,
        //         'async' => false,
        //     ],
        // ]
        // 
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
        // Async defaults to false for messages with no more than 10 recipients; 
        // messages with more than 10 recipients are always sent asynchronously, regardless of the value of async
        $this->message['async'] = false;   
        $this->message['ip_pool'] = 'Main Pool';
        $this->message['send_at'] = $this->setDate();
                                            
        // "html": "<p>Example HTML content</p>",
        // "text": "Example text content",
        // "subject": "example subject",
        // "from_email": "message.from_email@example.com",
        // "from_name": "Example Name",
        // "to": [
        //     {
        //         "email": "recipient.email@example.com",
        //         "name": "Recipient Name",
        //         "type": "to"
        //     }
        // ],
        // "headers": {
        //     "Reply-To": "message.reply@example.com"
        // },
        //"async": false,
        // "ip_pool": "Main Pool",  // The name of the dedicated ip pool that should be used to send the message.
        // "send_at": "example send_at"
        $this->post(
            'https://mandrillapp.com/api/1.0/messages/send-raw.json', 
            array(
                'key' => $this->key,
                'message' => $this->message
            )
        );
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
     * @param string $key
     * 
     * @return void
     */
    public function setKey($key)
    {
        return $this->key = $key;
    }

}
