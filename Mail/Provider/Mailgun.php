<?php

namespace Obullo\Mail\Provider;

use Obullo\Log\LoggerInterface;
use Mailgun\Mailgun as MailgunApi;
use Obullo\Container\ContainerInterface;
use Obullo\Translation\TranslatorInterface;

/**
 * Mailgun Transactional Api Provider
 *
 * @category  Mailer
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 * @link      https://mandrillapp.com/api/docs/messages.JSON.html
 */
class Mailgun extends AbstractProvider implements ProviderInterface
{
    /**
     * Constructor
     *
     * @param object $c          \Obullo\Container\ContainerInterface
     * @param object $translator \Obullo\Translation\TranslatorInterface
     * @param object $logger     \Obullo\Log\LogInterface
     * @param array  $params     service parameters
     */
    public function __construct(ContainerInterface $c, TranslatorInterface $translator, LoggerInterface $logger, array $params)
    {
        parent::__construct($c, $translator, $logger, $params);
    }

    /**
     * Create message event
     * 
     * @return void
     */
    protected function buildEvent()
    {
        // Create new message event

        $this->msgEvent['from'] = $this->getFrom();
        $this->msgEvent['subject'] = $this->getSubject();

        $this->setMailType($this->getMailType());  // text / html
        foreach ($this->getRecipients() as $v) {
            $this->msgEvent[$v['type']][] = $v['email'];
        }
        $headers = $this->getHeaders();

        if (! empty($headers['Reply-To'])) {
            $this->msgEvent['h:Reply-To'] = $headers['Reply-To'];
        }
        if (! empty($headers['Message-ID'])) {
            $this->msgEvent['h:Message-Id'] = $headers['Message-ID'];
        }
        $this->msgEvent['o:deliverytime'] = $this->setDate();

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
        foreach ($this->getAttachments() as $value) {
            $this->files[$value['disposition']][] = $value['fileurl'];
        }
    }

    /**
     * Send email with http request
     * 
     * @return boolean
     */
    protected function sendEmail()
    {
        $this->buildEvent();
        unset($this->msgEvent['files']);  // Remove files we already had it

        $mailResult = $this->getMailResult();
        if ($mailResult->hasError()) {
            return $mailResult;
        }
        $client = new MailgunApi($this->params['provider']['mailgun']['key']);
        $result = $client->sendMessage(
            $this->params['provider']['mailgun']['domain'],
            $this->msgEvent, 
            $this->files
        );
        $mailResult = $this->parseResult($result);
        return $mailResult;
    }

    /**
     * Send email with queue
     *
     * @param array $options queue options
     * 
     * @return object MailResult
     */
    protected function queueEmail($options = array())
    {
        $this->buildEvent();
        $mailResult = $this->getMailResult();
        if ($mailResult->hasError()) {
            return $mailResult;
        }
        return $this->push($this->msgEvent, $options);
    }

    /**
     * Parse response
     * 
     * @param string $result response
     * 
     * @return object MailResult
     */
    protected function parseResult($result)
    {
        $mailResult = $this->getMailResult();

        // object(stdClass)#77 (2) { ["http_response_body"]=> object(stdClass)#73 (2) { ["id"]=> string(43) "<20150730091836.29406.7449@news.obullo.com>" ["message"]=> string(18) "Queued. Thank you." } ["http_response_code"]=> int(200) }

        if ($result->http_response_code != 200) {  //  Failure
            $mailResult->setCode($mailResult::FAILURE);
            $mailResult->setMessage("Http request failed.");
            return $mailResult;
        }
        if (isset($result->http_response_body->id)) {
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