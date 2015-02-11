<?php

namespace Obullo\Log\Handler;

use Obullo\Container\Container;

/**
 * Email Handler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Class Email extends AbstractHandler implements HandlerInterface
{
    use \Obullo\Log\Formatter\LineFormatterTrait;

    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Service mailer
     * 
     * @var object
     */
    public $mailer;

    /**
     * Message body
     * 
     * @var string
     */
    public $message;
    
    /**
     * Config Constructor
     *
     * @param object $c      container
     * @param object $mailer mailer service
     * @param array  $params parameters
     */
    public function __construct(Container $c, $mailer, array $params = array())
    {
        $this->mailer = $mailer;
        $this->message = $params['message'];

        parent::__construct($c);

        $this->mailer->from($params['from']);
        $this->mailer->to($params['to']);
        $this->mailer->cc($params['cc']); 
        $this->mailer->bcc($params['bcc']);
        $this->mailer->subject($params['subject']);
    }

    /**
     * Writer 
     *
     * @param array $data log record
     * 
     * @return boolean
     */
    public function write(array $data)
    {
        $lines = '';
        foreach ($data['record'] as $record) {
            $record = $this->arrayFormat($data['time'], $record);
            $lines .= $this->lineFormat($record);
        }
        $this->mailer->message(sprintf($this->message, $lines));
        $this->mailer->send();
    }

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close() 
    {
        return;
    }
}

// END Email class

/* End of file Email.php */
/* Location: .Obullo/Log/Handler/Email.php */