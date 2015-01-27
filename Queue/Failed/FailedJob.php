<?php 

namespace Obullo\Queue\Failed;

use RuntimeException;

/**
 * Failed Job Class
 * 
 * @category  Queue
 * @package   Failed
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 */
Abstract Class FailedJob
{
    /**
     * Db instance
     * 
     * @var object
     */
    public $db;

    /**
     * Db table
     * 
     * @var array
     */
    public $table;

    /**
     * Constuctor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $queue    = $c['config']->load('queue');
        $database = $c['config']->load('database');

        $provider = $queue['failed']['provider'];

        if ( ! isset($database['connections'][$provider['connection']])) {
            throw new RuntimeException(
                sprintf(
                    'Failed job database connection "%s" is not defined in your config database.php',
                    $provider['connection']
                )
            );
        }
        $this->db = $c->load('service provider '.$provider['name'], array('connection' => $provider['connection'], 'driver' => $provider['driver']));
        $this->table = $queue['failed']['table'];
    }

}

// END FailedJob class

/* End of file FailedJob.php */
/* Location: .Obullo/Queue/Failed/FailedJob.php */