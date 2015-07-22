<?php

namespace Obullo\Mail\Provider;

use Obullo\Curl\Curl;
use Obullo\Mail\MailResult;
use Obullo\Container\ContainerInterface;

/**
 * Mailgun Transactional Api Provider
 *
 * @category  Mailer
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 * @link      https://mandrillapp.com/api/docs/messages.JSON.html
 */
class Mailgun extends AbstractProvider implements ProviderInterface
{
    /**
     * Create a new Mailgun transport instance.
     *
     * @param object $c      container
     * @param array  $params config & service parameters
     * 
     * @return void
     */
    public function __construct(ContainerInterface $c, array $params)
    {
        $c['logger']->debug('Mailgun Class Initialized');

        parent::__construct($c, $params);
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

        $this->setMailtype($this->params['message']['mailtype']);  // text / html

        foreach ($this->getRecipients() as $value) {
            $this->msgEvent['to'][] = $value;
        }
        $headers = $this->getHeaders();
        if (count($headers) > 0) {
            foreach ($headers as $key => $value) {
                $this->msgEvent['headers'][$key] = $value;
            }
        }
        $this->buildMessage();
        $this->buildAttachments();
    }

    /**
     * Send email with curl
     * 
     * @return boelean
     */
    protected function sendEmail()
    {
        $this->buildEvent();

        $client = new Curl;
        $body = $client->setUserAgent($this->getUserAgent())
            ->setOpt(CURLOPT_CONNECTTIMEOUT, 30)
            ->setOpt(CURLOPT_TIMEOUT, 600)
            ->setOpt(CURLOPT_RETURNTRANSFER, 1)
            ->setAuth('apikey', $this->params['provider']['mailgun']['key'], 'basic')
            ->setVerbose(false)
            ->post($this->params['provider']['mailgun']['url'], $this->msgEvent)
            ->getBody();
    
        var_dump($body);
        return;

        // $mailResult = $this->parseBody($client, $body);
        // return $mailResult;
    }

    /**
     * Send email with Queue
     * 
     * @return object MailResult
     */
    protected function queueEmail()
    {
        return parent::queueEmail();
    }

    /**
     * Parse response body
     * 
     * @param object $client curl client
     * @param string $body   response
     * 
     * @return object MailResult
     */
    protected function parseBody($client, $body)
    {
        $result = $this->getMailResult();

        if ($client->response->getError() || $client->response->getStatusCode() != 200) {  //  Failure
            $result->setCode($result::FAILURE);
            $result->setMessage($client->response->getError());
            return $result;
        }
        $resultArray = json_decode($body, true);

        if ($resultArray == null) {
            $result->setCode($result::JSON_PARSE_ERROR);
            $result->setMessage("Unable to decode the JSON response from the Mailgun API");
            return $result;
        }
        if (isset($resultArray[0]) && is_array($resultArray[0]) && $resultArray[0]['status'] == 'sent') {
            $result->setCode($result::SUCCESS);
        } elseif ($resultArray['status'] == 'error') {
            $result->setCode($result::API_ERROR);
            $result->setMessage($resultArray['message']);
        }
        $result->setBody($body);
        $result->setInfo($client->response->getInfo());
        return $result;
    }

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->params['provider']['mailgun']['key'];
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
        return $this->params['provider']['mailgun']['key'] = $key;
    }

}