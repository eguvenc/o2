<?php

namespace Obullo\Mail\Provider;

/**
 * Mandrill Transactional Api Provider
 *
 * @category  Mailer
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 * @link      https://mandrillapp.com/api/docs/messages.JSON.html
 */
class Mandrill extends AbstractProvider implements ProviderInterface
{
    /**
     * Create a new Mandrill transport instance.
     *
     * @param array $params config & service parameters
     * 
     * @return void
     */
    public function __construct(array $params)
    {
        parent::__construct($params);
    }

    /**
     * Create message event
     * 
     * @return void
     */
    protected function buildEvent()
    {
        // Create new message event

        $this->msgEvent['from_email'] = $this->getFromEmail();
        $this->msgEvent['from_name']  = $this->getFromName();
        $this->msgEvent['subject']    = $this->getSubject();

        $this->setMailType($this->getMailType());  // text / html

        foreach ($this->getRecipients() as $v) {
            $this->msgEvent['to'][] = array(
                'email' => $v['email'],
                'name'  => $v['name'],
                'type'  => $v['type'],
            );
        }
        $headers = $this->getHeaders();
        if (count($headers) > 0) {
            foreach ($headers as $key => $value) {
                $this->msgEvent['headers'][$key] = $value;
            }
        }
        $this->msgEvent['send_at'] = $this->setDate();

        // Async defaults to false for messages with no more than 10 recipients
        // messages with more than 10 recipients are always sent asynchronously, regardless of the value of async

        $this->buildMessage();
        $this->buildAttachments();
    }

    /**
     * Build attachments
     * 
     * @return void
     */
    protected function buildAttachments()
    {
        if (! $this->hasAttachment()) {
            return;
        }
        foreach ($this->getAttachments() as $v) {
            $key = ($v['disposition'] == 'attachment') ? "attachments" : "images";
            $this->msgEvent[$key][] = array(
                'name' => $v['name'],
                'type' => $v['type'],
                'content' => base64_encode(file_get_contents($v['fileurl'])),
            );
        }
    }

    /**
     * Send email with curl
     * 
     * @return boelean
     */
    protected function sendEmail()
    {
        $this->buildEvent();
        unset($this->msgEvent['files']);

        $mailResult = $this->getMailResult();
        if ($mailResult->hasError()) {
            return $mailResult;
        }
        try {
            $mandrill = new \Mandrill($this->params['provider']['mandrill']['key']);

            $result = $mandrill->messages->send(
                $this->msgEvent,
                $this->params['provider']['mandrill']['async'],
                $this->params['provider']['mandrill']['pool']
            );
            $mailResult = $this->parseResult($result);

        } catch(\Mandrill_Error $e) {
            $mailResult->setCode($mailResult::API_ERROR);
            $mailResult->setMessage('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
        }
        return $mailResult;
    }

    /**
     * Send email with Queue
     *
     * @param array $options queue options
     * 
     * @return object MailResult
     */
    protected function queueEmail($options = array())
    {
        $this->buildEvent();
        unset($this->msgEvent['attachments'], $this->msgEvent['images']);  // We use $this->msgEvent['files']

        $mailResult = $this->getMailResult();
        if ($mailResult->hasError()) {
            return $mailResult;
        }
        return $this->push($this->msgEvent, $options);
    }

    /**
     * Parse response body
     * 
     * @param array $result result data
     * 
     * @return object MailResult
     */
    protected function parseResult($result)
    {
        $mailResult = $this->getMailResult();

        if (isset($result[0]['status'])) {
            $mailResult->setCode($mailResult::SUCCESS);
            $mailResult->setMessage("Queued. Thank you.");
        } else {
            $mailResult->setCode($mailResult::API_ERROR);
            $mailResult->setMessage("Unknown Api Error.");
            return $mailResult;
        }
        return $mailResult;
    }

}