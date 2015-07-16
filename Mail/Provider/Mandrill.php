<?php

namespace Obullo\Mail\Provider;

use Obullo\Mail\MailResult;
use Obullo\Utils\Curl\Client;
use Obullo\Container\ContainerInterface;

/**
 * Mandrill Transactional Api Provider
 *
 * @category  Mailer
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 * @link      https://mandrillapp.com/api/docs/messages.JSON.html
 */
class Mandrill extends AbstractProvider implements ProviderInterface
{
    /**
     * Create a new Mandrill transport instance.
     *
     * @param object $c      container
     * @param array  $params config & service parameters
     * 
     * @return void
     */
    public function __construct(ContainerInterface $c, array $params)
    {
        $c['logger']->debug('Mandrill Class Initialized');

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

        $this->msgEvent['subject']    = $this->getSubject();
        $this->msgEvent['from_email'] = $this->getFromEmail();
        $this->msgEvent['from_name']  = $this->getFromName();

        foreach ($this->getRecipients() as $value) {
            $this->msgEvent['to'][] = $value;
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

        $this->msgEvent['async'] = false;    
        $this->msgEvent['ip_pool'] = $this->params['mandrill']['pool']; 

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

        $client = new Client;
        $body = $client->setUserAgent($this->getUserAgent())
            ->setOpt(CURLOPT_FOLLOWLOCATION, true)
            ->setOpt(CURLOPT_HEADER, false)
            ->setOpt(CURLOPT_CONNECTTIMEOUT, 30)
            ->setOpt(CURLOPT_TIMEOUT, 600)
            ->setBody(
                json_encode(
                    [
                        'key' => $this->params['mandrill']['key'],
                        'message' => $this->msgEvent
                    ]
                )
            )
            ->setVerbose(false)
            ->post($this->params['mandrill']['url'])
            ->getBody();

        // "[{"email":"example@email.com","status":"sent","_id":"3e5bdc391a8d4602813a5cd1d751773f","reject_reason":null}]" 
    
        $mailResult = $this->parseBody($client, $body);
        return $mailResult;
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
            $result->setMessage("Unable to decode the JSON response from the Mandrill API");
            return $result;
        }
        if (is_array($resultArray) && $resultArray['status'] == 'sent') {
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
        return $this->params['mandrill']['key'];
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
        return $this->params['mandrill']['key'] = $key;
    }

}