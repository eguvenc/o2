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
     * You must include recipients: To, Cc ..
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
     * Api error
     */
    const API_ERROR = -5;

    /**
     * Send success.
     */
    const SUCCESS = 1;

    /**
     * Queued job id
     *
     * @var string
     */
    protected $id;

    /**
     * Authentication result code
     *
     * @var int
     */
    protected $code;

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
    public function hasError()
    {
        if ($this->code > 0 || $this->code == null) {
            return false;
        } else {
            $this->logger->error(
                'Api request failed', 
                [
                    'api' => get_class($this->mailer)
                ]
            );
            return true;
        }
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
     * Set custom error messages
     * 
     * @param string $message message
     *
     * @return object MailResult
     */
    public function setMessage($message)
    {
        $this->messages[] = $message;
        return $this;
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
     * Gets all messages
     * 
     * @return array
     */
    public function getArray()
    {
        return array(
            'code' => $this->code,
            'messages' => $this->messages
        );
    }

}