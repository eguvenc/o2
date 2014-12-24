<?php

namespace Obullo\Mail;

use RuntimeException;

/**
 * Mail Connection Manager
 *
 * @category  Mail
 * @package   Connection
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mail
 */
Class Connection
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Database Config Parameters
     * 
     * @var array
     */
    protected $params;

    /**
     * Database provider
     * 
     * @var object
     */
    protected $provider;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params configuration array
     */
    public function __construct($c, $params)
    {
        $this->c = $c;
        $this->c['config']->load('mail');

        $this->handlers = $c['config']['mail']['handlers'];
        $this->provider = empty($params['provider']) ? $c['config']['mail']['default']['provider'] : $params['provider'];
        $this->params = $params;
    }

    /**
     * Connect to database
     * 
     * @return void
     */
    public function connect()
    {
        if ( ! isset($this->handlers[$this->provider])) {
            throw new RuntimeException(
                sprintf(
                    'Provider %s not defined in your mail.php configuration.',
                    $this->provider
                )
            );
        }
        $Class = $this->handlers[$this->provider];
        $mailer = new $Class($this->c, $this->params);

        $from = $this->c['config']['mail']['from']['address'];
        
        if ( ! empty($this->params['from'])) {
            $from = $this->params['from'];
        }
        $mailer->from($from);
        return $mailer;
    }
}

// END Connection class

/* End of file Connection.php */
/* Location: .Obullo/Database/Connection.php */