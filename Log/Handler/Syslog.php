<?php

namespace Obullo\Log\Handler;

use Obullo\Container\Container;

/**
 * Syslog LogHandler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Class Syslog extends AbstractHandler implements HandlerInterface
{
    use \Obullo\Log\Formatter\LineFormatterTrait;

    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Facility used by this syslog instance
     * 
     * @var string
     */
    public $facility = LOG_USER;

    /**
     * Syslog application name
     * 
     * @var string
     */
    public $name = 'LogHandler.Syslog';

    /**
     * Config Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct(Container $c, array $params = array())
    {
        $this->c = $c;

        parent::__construct($c, $params);

        if (isset($params['facility'])) {
            $this->facility = $params['facility'];  // Application facility
        }
        if (isset($params['name'])) {       // Application name
            $this->name = $params['name'];
        }
        openlog($this->name, LOG_PID, $this->facility);
    }

    /**
     * Write output
     *
     * @param string $data single record data
     * 
     * @return void
     */
    public function write(array $data)
    {
        foreach ($data['record'] as $record) {
            $record = $this->arrayFormat($data['time'], $record);
            syslog($record['level'], $this->lineFormat($record));
        }
    }

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close() 
    {
        closelog();
    }
}

// END Syslog class

/* End of file Syslog.php */
/* Location: .Obullo/Log/Handler/Syslog.php */