<?php

namespace Obullo\Log\Queue\JobHandler;

use Obullo\Log\Writer\EmailWriter;

/**
 * Email JobHandler Class
 * 
 * @category  Log
 * @package   JobHandler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class JobHandlerEmail implements JobHandlerInterface
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
        $this->writer = new EmailWriter(
            $c->load('service/mailer'),
            $params
        );
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

// END JobHandlerEmail class

/* End of file JobHandlerEmail.php */
/* Location: .Obullo/Log/Queue/JobHandler/JobHandlerEmail.php */