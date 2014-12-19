<?php

namespace Obullo\Mail\Transport;

use Obullo\Mail\Transport\Response;

/**
 * Mandrill Transactional Email Api Client
 *
 * @category  Mail
 * @package   Transactional
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/mail
 * @link      https://mandrillapp.com/api/docs/messages.JSON.html
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
     * Mandrill dedicated ip pool
     *
     * @var string
     */
    protected $ip_pool;

    /**
     * Logger
     * 
     * @var object
     */
    public $logger;

    /**
     * Response class
     * 
     * @var object
     */
    public $response;

    /**
     * Mandrill api call response array
     * 
     * @var array
     */
    protected $responseBody = array();

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
        $this->key = $config['send']['transport']['mandrill']['key'];
        $this->ip_pool = $config['send']['transport']['mandrill']['ip_pool'];

        $this->logger = $c->load('service/logger');
        $this->logger->debug('Madrill Class Initialized');

        parent::__construct($c, $config);
    }

    /**
     * Set Recipients
     *
     * @param string $to source emails
     * 
     * @return voi
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
        $this->setHeader('Cc', implode(", ", $cc));

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
        if ($limit != '' AND is_numeric($limit)) {
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
     * Set Email Subject
     * 
     * @param string $subject email subject
     * 
     * @return void
     */
    public function subject($subject)
    {
        $this->subject = $subject; // We don't do prep encoding
        $this->setHeader('Subject', $subject);
    }

    /**
     * Write Headers as a string
     *
     * @return    void
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
        $this->writeHeaders();
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
                    if ( ! $content = file_get_contents($value['fileurl'])) {
                        $this->debugMsg[] = $this->c['translator']->sprintf('OBULLO:MAIL:ATTACHMENT_MISSING', $value['fileurl']);
                    }
                    $this->message['attachments'][$i]['content'] = ($content == false) ? null : base64_encode($content);
                    ++$i;
                } else {
                    $this->message['images'][$j]['type'] = $value['type'];
                    $this->message['images'][$j]['name'] = $value['name'];
                    if ( ! $content = file_get_contents($value['fileurl'])) {
                        $this->debugMsg[] = $this->c['translator']->sprintf('OBULLO:MAIL:ATTACHMENT_MISSING', $value['fileurl']);
                    }
                    $this->message['images'][$j]['content']  = ($content == false) ? null : base64_encode($content);
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

    /**
     * Send new http post request
     *
     * @param string $url    post url
     * @param array  $params post data
     * 
     * @return string $result
     */
    protected function post($url, array $params)
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
            $this->debugMsg[] = $this->c['translator']->sprintf('OBULLO:MAIL:API_CALL_FAILED', $url, curl_error($ch));
        }
        $result['raw'] = $body;
        $result['info'] = curl_getinfo($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Send email with curl
     * 
     * @return boelean
     */
    public function spoolEmail()
    {
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
        $this->message['ip_pool'] = $this->ip_pool;
        $this->message['send_at'] = $this->setDate();

        $this->buildAttachments();

        $url = 'https://mandrillapp.com/api/1.0/messages/send.json';
        $this->responseBody = $this->post(
            $url,
            array(
                'key' => $this->key,
                'message' => $this->message
            )
        );
        $this->responseBody['array'] = json_decode($this->responseBody['raw'], true);

        if ($this->responseBody['array'] === null) {
            $this->debugMsg[] = 'We were unable to decode the JSON response from the Mandrill API, Response is: <pre>'.$this->responseBody['raw'].'</pre>';
            $this->debugMsg[] = $this->responseBody['info'];
            $this->logger->error(
                'Transactional mail api call failed we were unable to decode the JSON', 
                array(
                    'url' => $url, 
                    'body' => $this->responseBody['raw'], 
                    'info' => $this->responseBody['info']
                    )
            );
            return false;
        }
        if (floor($this->responseBody['info']['http_code'] / 100) >= 4) {
            $this->debugMsg[] = 'Cast error unexpected response code: '.$this->responseBody['info']['http_code'];
            $this->debugMsg[] = $this->responseBody['info'];
            return false;
        }
        if (empty($this->debugMsg)) {
            return true;
        }
        $this->debugMsg[] = $this->responseBody['info'];
        return false;
    }

    /**
     * Returns to response object
     * 
     * @return object
     */
    public function response()
    {   
        return new Response($this->responseBody);
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

// END Mandrill class
/* End of file Mandrill.php */

/* Location: .Obullo/Mail/Transport/Mandrill.php */