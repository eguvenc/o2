<?php

namespace Obullo\Log\Handler;

/**
 * Logger Abstract Handler
 * 
 * @category  Log
 * @package   Writer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Abstract Class AbstractJobHandler
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Application request type
     * 
     * @var string
     */
    public $type;

    /**
     * Constructor
     *
     * @param array $params configuration
     */
    public function __construct($params = array())
    {
        global $c;
        $this->c = $c;
        $params = array();
    }

    /**
     * Check log writing is allowed, 
     * don't allow log writing for cli commands
     *
     * @param string $type request type
     * 
     * @return boolean
     */
    public function isAllowed($type)
    {
        if ($type == 'worker') {  //  If worker logs allowed from config file.
            return $this->c['config']['log']['queue']['workers']['logging'];
        }
        if (in_array($type, array(null, 'http','ajax','cli'))) {
            return true;
        }
        return false;
    }

    /**
    * Format log records and build lines
    *
    * @param string $dateFormat        log date format
    * @param array  $unformattedRecord log data
    * 
    * @return array formatted record
    */
    abstract public function format($dateFormat, $unformattedRecord);

    /**
     * Write processor output to file
     *
     * @param array $records log data
     * 
     * @return boolean
     */
    abstract public function write(array $records);

    /**
     * Close connection
     * 
     * @return void
     */
    abstract public function close();

}

// END AbstractJobHandler class

/* End of file AbstractJobHandler.php */
/* Location: .Obullo/Log/JobHandler/AbstractJobHandler.php */