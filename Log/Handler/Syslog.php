<?php

namespace Obullo\Log\Handler;

use Obullo\Config\ConfigInterface;
use Obullo\Application\Application;

/**
 * Syslog LogHandler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class Syslog extends AbstractHandler implements HandlerInterface
{
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
     * Constructor
     * 
     * @param object $app    \Obullo\Application\Application
     * @param object $config \Obullo\Config\ConfigInterface
     * @param array  $params syslog parameters
     */
    public function __construct(Application $app, ConfigInterface $config, array $params = array())
    {
        parent::__construct($app, $config);

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
            $record = $this->arrayFormat($data, $record);
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