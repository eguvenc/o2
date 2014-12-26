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

        if ( ! isset($database['key'][$provider['db']])) {
            throw new RuntimeException(
                sprintf(
                    'Failed job database "%s" is not defined in your config database.php',
                    $provider['db']
                )
            );
        }
        $this->db = $c->load('return new service/provider/'.$provider['name'], array('db' => $provider['db'], 'provider' => $provider['provider']));

        if ( ! $c->exists('provider:'.strtolower($provider['name']))) {  // If provider not exists ! Alert to developer
            throw new RuntimeException(
                sprintf(
                    'FailedJob class requires %s service provider but it is not defined in your app/classes/Service/Provider folder.', 
                    $provider['name']
                )
            );
        }
        $this->table = $queue['failed']['table'];
    }

}

// END FailedJob class

/* End of file FailedJob.php */
/* Location: .Obullo/Queue/Failed/FailedJob.php */