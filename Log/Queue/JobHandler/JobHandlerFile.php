<?php

namespace Obullo\Log\Queue\JobHandler;

use Obullo\Log\Writer\FileWriter;

/**
 * File JobHandler Class
 * 
 * @category  Log
 * @package   JobHandler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class JobHandlerFile implements JobHandlerInterface
{
    /**
     * File writer
     * 
     * @var object
     */
    public $writer;

    /**
     * Config Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct($c, array $params = array())
    {
        $this->c = $c;
        $params['worker'] = true;
        $this->writer = new FileWriter($params);
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
        if (isset($data['batch'])) {
            $this->writer->batch($data['record'], $data['type']);
            return;
        }
        $this->writer->write($data['record'], $data['type']);
    }

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close() 
    {
        return $this->writer->close();
    }
}

// END JobHandlerFile class

/* End of file JobHandlerFile.php */
/* Location: .Obullo/Log/Queue/JobHandler/JobHandlerFile.php */