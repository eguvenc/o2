<?php

namespace Obullo\Log\Handler;

use Closure;
use Obullo\Container\ContainerInterface;

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
class Email extends AbstractHandler implements HandlerInterface
{
    /**
     * Mail message
     * 
     * @var string
     */
    protected $message;

    /**
     * Closure function
     * 
     * @var object
     */
    protected $closure;

    /**
     * Newline character
     * 
     * @var string
     */
    protected $newlineChar = '<br />';

    /**
     * Config Constructor
     *
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
    }

    /**
     * Sets your custom newline character
     * 
     * @param string $newline char
     *
     * @return void
     */
    public function setNewlineChar($newline = '<br />')
    {
        $this->newlineChar = $newline;
    }

    /**
     * Set mailer message
     * 
     * @param string $message message
     *
     * @return void
     */
    public function setMessage($message)
    {
        $this->message = (string)$message;
    }

    /**
     * Sets closure function for send method
     * 
     * @param Closure $closure closure
     * 
     * @return void
     */
    public function func(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * Writer 
     *
     * @param array $data log record
     * 
     * @return void
     */
    public function write(array $data)
    {
        $lines = '';
        foreach ($data['record'] as $record) {
            $record = $this->arrayFormat($data, $record);
            $lines .= str_replace("\n", $this->newlineChar, $this->lineFormat($record));
        }
        $message = sprintf($this->message, $lines);
        $closure = $this->closure;

        if (is_callable($closure)) {  // Send formatted message
            return $closure($message);
        }
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