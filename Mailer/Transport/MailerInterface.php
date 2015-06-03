<?php

namespace Obullo\Mailer\Transport;

use Obullo\Container\ContainerInterface;

/**
 * HttpMailerInterface for Http Apis
 * 
 * @category  Mailer
 * @package   Transport
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/mailer
 */
interface MailerInterface
{
    /**
     * Constructor
     * 
     * @param array $c container
     */
    public function __construct(ContainerInterface $c);

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
    
}

// END HttpMailerInterface class

/* End of file HttpMailerInterface.php */
/* Location: .Obullo/Mailer/Transport/HttpMailerInterface.php */