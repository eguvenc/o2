<?php

namespace Obullo\Log\Writer;

/**
 * Logger Abstract Writer
 * 
 * @category  Log
 * @package   Writer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Abstract Class AbstractWriter
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

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
     * Get config
     * 
     * @return array
     */
    abstract public function getConfig();

    /**
     * Check log writing is allowed, 
     * don't allow log writing for cli commands
     * 
     * @param string $type request types ( app, cli, ajax, worker )
     * 
     * @return boolean
     */
    public function isAllowed($type = null)
    {
        if (isset($_SERVER['argv'][1]) AND $_SERVER['argv'][1] == 'worker' AND $this->c['config']['log']['queue']['workers']) {  //  If worker logs allowed from config file.
            return true;
        }
        if (isset($_SERVER['argv'][1]) AND in_array($_SERVER['argv'][1], array('clear','help','log','queue','update','app','worker'))) {
            return false;
        }
        if (in_array($type, array(null, 'app','ajax','cli'))) {
            return true;
        }
        return false;
    }

    /**
     * Write output
     *
     * @param string $record single record data
     * @param string $type   request types ( app, cli, ajax )
     * 
     * @return mixed
     */
    abstract public function write($record, $type = null);

    /**
     * Batch operation
     * 
     * @param array  $records multiline record data
     * @param string $type    request types ( app, cli, ajax )
     * 
     * @return mixed
     */
    abstract public function batch(array $records, $type = null);

    /**
     * Close connection
     * 
     * @return void
     */
    abstract public function close();

}

// END AbstractWriter class

/* End of file AbstractWriter.php */
/* Location: .Obullo/Log/Writer/AbstractWriter.php */