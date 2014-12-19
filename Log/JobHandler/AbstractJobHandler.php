<?php

namespace Obullo\Log\JobHandler;

/**
 * Logger Abstract Handler
 * 
 * @category  Log
 * @package   Writer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/log
 */
Abstract Class AbstractJobHandler
{
    /**
     * Config
     * 
     * @var array
     */
    public $config = array();

    /**
     * Constructor
     *
     * @param array $params configuration
     */
    public function __construct($params = array())
    {
        global $c;
        $this->c = $c;

        $this->config = array('log' => $this->c->load('config')['log']);

        if ( ! empty($params)) {
            $this->config = array_merge($this->config, $params);
        }
    }

    /**
     * Check log writing is allowed, 
     * don't allow log writing for cli commands
     *
     * @param string $request request type
     * 
     * @return boolean
     */
    public function isAllowed($request)
    {
        if ($request == 'worker') {  //  If worker logs allowed from config file.
            return $this->config['log']['queue']['workers']['logging'];
        }
        if (in_array($request, array(null, 'http','ajax','cli'))) {
            return true;
        }
        return false;
    }

    /**
    * Format log records and build lines
    *
    * @param string $timestamp         unix time
    * @param array  $unformattedRecord log data
    * 
    * @return array formatted record
    */
    abstract public function format($timestamp, $unformattedRecord);

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