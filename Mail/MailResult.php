<?php

namespace Obullo\Mail;

use Obullo\Log\LoggerInterface;
use Obullo\Mail\Provider\AbstractProvider;

/**
 * MailResult (Api Result Controller)
 *
 * @category  Mail
 * @package   MailResult
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/mail
 */
class MailResult
{
    /**
     * General Failure
     */
    const FAILURE = 0;

    /**
     * You must include recipients: To, Cc, or Bcc
     */
    const NO_RECIPIENTS = -1;

    /**
     * Email validation failure
     */
    const INVALID_EMAIL = -2;

    /**
     * Attachment not located
     */
    const ATTACHMENT_UNREADABLE = -3;

    /**
     * Mail event could not to send to queue
     */
    const QUEUE_ERROR = -4;

    /**
     * Json decode error
     */
    const JSON_PARSE_ERROR = -5;

    /**
     * Xml decode error
     */
    const XML_PARSE_ERROR = -6;

    /**
     * Api error
     */
    const API_ERROR = -7;

    /**
     * Send success.
     */
    const SUCCESS = 1;

    /**
     * Authentication result code
     *
     * @var int
     */
    protected $code;

    /**
     * Response body
     *
     * @var mixed
     */
    protected $body;

    /**
     * Mailer class
     * 
     * @var object
     */
    protected $mailer;

    /**
     * An array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this should be an empty array.
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Sets the mailer object
     *
     * @param object $mailer mailer
     * @param object $logger logger
     */ 
    public function __construct(AbstractProvider $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * Returns whether the result represents a successful send attempt
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->code > 0) {
            return true;
        } else {
            $this->logger->error(
                'Email api request failed', 
                [
                    'api' => get_class($this->mailer)
                ]
            );
            return false;
        }
    }

    /**
     * Get the result code for this authentication attempt
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns curl response
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Returns an array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this method returns an empty array.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Returns to curl_info() array
     * 
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Set custom error code
     * 
     * @param int $code error code
     *
     * @return void
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Set response body
     *
     * @param string $body curl response
     *
     * @return object
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Set curl_info() data
     *
     * @param array $info curl_info array
     *
     * @return object
     */
    public function setInfo(array $info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * Set custom error messages
     * 
     * @param string $message message
     *
     * @return object
     */
    public function setMessage($message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Gets all messages
     * 
     * @return array
     */
    public function getArray()
    {
        return array(
            'code' => $this->code,
            'messages' => $this->messages,
            'identifier' => $this->identifier
        );
    }

}