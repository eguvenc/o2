<?php

namespace Obullo\Mail\Provider;

use Obullo\Container\ContainerInterface;

/**
 * Null handler
 *
 * @category  Mailer
 * @package   Transport
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 */
class Null
{
    public $msgEvent = array();

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param object $params parameters
     */
    public function __construct(ContainerInterface $c, $params = array())
    {
        $params = null;
        $c['logger']->debug('Null Mailer Class Initialized');
    }

    /**
     * Constructor
     * 
     * @return object
     */
    public function init()
    {   
        return $this;
    }

    /**
     * Initialize the Email Data
     *
     * @param boolean $clearAttachments clear switch
     * 
     * @return object
     */
    public function clear($clearAttachments = false)
    {
        $clearAttachments = null;
        return $this;
    }

    /**
     * Add a Header Item
     *
     * @param string $header key
     * @param string $value  value
     * 
     * @return object
     */
    public function setHeader($header, $value)
    {
        $header = $value = null;
        return $this;
    }

    /**
     * Set From
     * 
     * @param string $from address
     * @param string $name label
     * 
     * @return object
     */
    public function from($from, $name = '')
    {
        $from = $name = null;
        return $this;
    }

    /**
     * Set Recipients
     *
     * @param string $to source emails
     * 
     * @return object
     */
    public function to($to)
    {
        $to = null;
        return $this;
    }

    /**
     * Set Cc
     *
     * @param mixed $cc carbon copy addresses
     * 
     * @return object
     */
    public function cc($cc = null)
    {
        $cc = null;
        return $this;
    }

    /**
     * Set BCC
     *
     * @param mixed $bcc blind carbon copy addresses
     * 
     * @return void
     */
    public function bcc($bcc = null)
    {
        $bcc = null;
        return $this;
    }

    /**
     * Set Reply-to
     *
     * @param string $replyto address
     * @param string $name    label
     * 
     * @return object
     */
    public function replyTo($replyto, $name = '')
    {
        $replyto = $name = null;
        return $this;
    }

    /**
     * Set Mailtype
     *
     * @param string $type default text
     * 
     * @return object
     */
    public function setMailType($type = 'text')
    {
        $type = null;
        return $this;
    }

    /**
     * Set email validation
     * 
     * @param boolean $enabled true or false
     * 
     * @return object
     */
    public function setValidation($enabled = false)
    {
        $enabled = null;
        return $this;
    }

    /**
     * Returns mailt type: html / text
     * 
     * @return string
     */
    public function getMailType()
    {
        return 'text';
    }

    /**
     * Returns to from name & email
     * 
     * @return string
     */
    public function getFrom()
    {
        return 'null';
    }

    /**
     * Returns from name
     * 
     * @return string
     */
    public function getFromName()
    {
        return 'null';
    }

    /**
     * Returns from email
     * 
     * @return string
     */
    public function getFromEmail()
    {
        return 'null';
    }

    /**
     * Set Email Subject
     * 
     * @param string $subject email subject
     * 
     * @return object
     */
    public function subject($subject)
    {
        $subject = null;
        return $this;
    }

    /**
     * Set Body
     *
     * @param string $body email body
     * 
     * @return object
     */
    public function message($body)
    {
        $body = null;
        return $this;
    }

    /**
     * Assign file attachments
     * 
     * @param string $filename    attachment name
     * @param string $disposition default attachment or inline
     * 
     * @return void
     */
    public function attach($filename, $disposition = 'attachment')
    {
        $filename = $disposition = null;
        return $this;
    }

    /**
     * Send Email
     *
     * @return bool
     */
    public function send()
    {
        return true;
    }

    /**
     * Queue Email
     * 
     * @return bool
     */
    public function queue()
    {
        return true;
    }

    /**
     * Set RFC 822 Date
     *
     * @param string $newDate set custom date
     * 
     * @return string
     */
    public function setDate($newDate = null)
    {
        return $newDate;
    }

    /**
     * Validate email address
     * 
     * @param mixed $email address
     * 
     * @return object
     */
    public function validateEmail($email)
    {
        $email = null;
        return $this;
    }

    /**
     * Get Debug Message
     *
     * @return string
     */
    public function printDebugger()
    {
        return;
    }

}