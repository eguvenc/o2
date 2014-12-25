<?php

namespace Obullo\Mail\Transport;

/**
 * Mail Transport Interface
 * 
 * @category  Transport
 * @package   Log
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 */
interface TransportInterface
{
    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params connection parameters
     */
    public function __construct($c, $params = array());

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     */
    public function getKey();

    /**
     * Set the API key being used by the transport.
     *
     * @param string $key api key
     * 
     * @return void
     */
    public function setKey($key);

    /**
     * Set Recipients
     *
     * @param string $to source emails
     * 
     * @return voi
     */
    public function to($to);

    /**
     * Set Cc
     *
     * @param mixed $cc carbon copy addresses
     * 
     * @return void
     */
    public function cc($cc = null);

    /**
     * Set Bcc
     *
     * @param mixed $bcc   blind carbon copy addresses
     * @param mixed $limit batch size
     * 
     * @return void
     */
    public function bcc($bcc = null, $limit = null);

    /**
     * Set Email Subject
     * 
     * @param string $subject email subject
     * 
     * @return void
     */
    public function subject($subject);

     /**
     * Send email with cUrl post method
     * 
     * @return boelean
     */
    public function spoolEmail();

    /**
     * Returns to response object
     * 
     * @return object
     */
    public function response();
    
}

// END TransportInterface class

/* End of file TransportInterface.php */
/* Location: .Obullo/Mail/TransportInterface.php */