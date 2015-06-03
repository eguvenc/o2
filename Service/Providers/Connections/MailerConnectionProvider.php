<?php

namespace Obullo\Service\Providers\Connections;

use RuntimeException;
use Obullo\Mailer\Queue;
use Obullo\Container\ContainerInterface;

/**
 * Mailer Connection Provider
 * 
 * @category  Connections
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class MailerConnectionProvider extends AbstractConnectionProvider
{
    protected $c;            // Container
    protected $config;       // Configuration items

    /**
     * Constructor ( Works one time )
     * 
     * Automatically check if the Mongo PECL extension has been installed / enabled.
     * 
     * @param string $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('mailer/transport');

        $this->setKey('mailer.factory.');
    }

    /**
     * Creates mailer objects
     * 
     * @param array $params parameters
     * 
     * @return void
     */
    protected function createClass($params)
    {
        if ( ! isset($this->config['drivers'][$params['driver']])) {
            throw new RuntimeException(
                sprintf(
                    'Driver key %s does not exist in your mailer.php config file.',
                    $params['driver']
                )
            );
        }
        if ($params['options']['queue']) {   // Queue Mailer Support
            
            $queue = new Queue($this->c);          // Connect to Queue
            $queue->setMailer($params['driver']);  // Set mail driver for Mailer/Queue class
            return $queue;
        }
        $Class = $this->config['drivers'][$params['driver']];
        return new $Class($this->c);
    }

    /**
     * Create a new mailer object returns to old if already exists
     * 
     * @param array $params connection parameters
     * 
     * @return object mongo client
     */
    public function factory($params = array())
    {   
        if ( ! isset($params['driver'])) {
            throw new UnexpectedValueException("Mailer driver requires driver parameter.");
        }
        if ( ! isset($params['options']['queue'])) {  // If queue option not selected we set queue option as "false" by default
            $params['options']['queue'] = false;
        }
        $cid = $this->getKey($this->getConnectionId($params));

        if ( ! $this->c->has($cid)) { //  create shared object if not exists
            $this->c[$cid] = function () use ($params) {  //  create shared objects
                return $this->createClass($params);
            };
        }
        return $this->c[$cid];
    }

}

// END MailerConnectionProvider.php class
/* End of file MailerConnectionProvider.php */

/* Location: .Obullo/Service/Providers/Connections/MailerConnectionProvider.php */