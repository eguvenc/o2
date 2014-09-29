<?php

namespace Obullo\Log\Writer;

use Obullo\Log\Writer\AbstractWriter;

/**
 * Syslog Writer Class
 * 
 * @category  Log
 * @package   Writer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class SyslogWriter extends AbstractWriter
{
    /**
     * Config
     * 
     * @var array
     */
    public $config;
    
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
    public $name = 'Log.Handler.Syslog';

    /**
     * Constructor
     *
     * @param array $params configuration
     */
    public function __construct($params)
    {
        parent::__construct($params);

        $this->config = $params;

        if (isset($params['facility'])) {
            $this->facility = $params['facility'];  // Application facility
        }
        if (isset($params['name'])) {       // Application name
            $this->name = $params['name'];
        }
        openlog($this->name, LOG_PID, $this->facility);
    }

    /**
     * Config
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Write line to file
     * 
     * @param string $record single record data
     * @param string $type   request types ( app, cli, ajax )
     * 
     * @return boolean
     */
    public function write($record, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        syslog($record['level'], $record);
        return true;
    }

    /**
     * NO batch reuired in syslog
     * 
     * @param array  $records multiline recor data
     * @param string $type    request types ( app, cli, ajax )
     * 
     * @return boolean
     */
    public function batch(array $records, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        foreach ($records as $record) {
            syslog($record['level'], $record);
        }
        return true;
    }

    /**
     * Close connection
     * 
     * @return void
     */
    public function close()
    {
        closelog();
    }

}

// END SyslogWriter class

/* End of file SyslogWriter.php */
/* Location: .Obullo/Log/Writer/SyslogWriter.php */