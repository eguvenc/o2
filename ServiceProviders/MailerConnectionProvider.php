<?php

namespace Obullo\ServiceProviders;

use RuntimeException;
use Obullo\Mailer\Queue;
use Obullo\Container\Container;
use Obullo\Utils\SingletonTrait;

/**
 * Mongo Connection Provider
 * 
 * @category  ConnectionProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/provider
 */
Class MailerConnectionProvider
{
    protected $c;            // Container
    protected $config;       // Configuration items
    
    use SingletonTrait, ConnectionTrait;

    /**
     * Constructor ( Works one time )
     * 
     * Automatically check if the Mongo PECL extension has been installed / enabled.
     * 
     * @param string $c container
     */
    protected function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('mailer');
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
                    'Driver key %s not exists in your mailer.php config file.',
                    $params['driver']
                )
            );
        }
        if (isset($params['options']['queue']) AND $params['options']['queue'] == true) {   // Queue Mailer Support

            $queue = new Queue($this->c);
            $queue->setMailer($params['driver']);  // Set mail driver for Mailer/Queue class
            return $queue;
        }
        $Class = $this->config['drivers'][$params['driver']];
        return new $Class($this->c);
    }

    /**
     * Retrieve shared mongo connection instance from connection pool
     *
     * @param array $params provider parameters
     * 
     * @return object MongoClient
     */
    public function getFactory($params = array())
    {
        return $this->factory($params);
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
        $cid = 'mailer.factory.'.static::getConnectionId($params);

        if ( ! $this->c->exists($cid)) { //  create shared object if not exists
            $self = $this;
            $this->c[$cid] = function () use ($self, $params) {  //  create shared objects
                return $self->createClass($params);
            };
        }
        return $this->c[$cid];
    }

}

// END MailerConnectionProvider.php class
/* End of file MailerConnectionProvider.php */

/* Location: .Obullo/ServiceProviders/MailerConnectionProvider.php */