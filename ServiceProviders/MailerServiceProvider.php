<?php

namespace Obullo\ServiceProviders;

use Obullo\Container\Container;

/**
 * Mailer Service Provider
 *
 * @category  ServiceProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/serviceProviders
 */
Class MailerServiceProvider implements ServiceProviderInterface
{
    /**
     * Connector
     * 
     * @var object
     */
    public $connector;
    
    /**
     * Registry
     * 
     * @param object $c container
     * 
     * @return void
     */
    public function register(Container $c)
    {
        $this->connector = new MailConnectionProvider($c);  // Register all Connectors as shared services
    }

    /**
     * Get connection
     * 
     * @param array $params array
     * 
     * @return object
     */
    public function get($params = array())
    {
        return $this->connector->getFactory($params);  // Get a mailer instance before we registered into container
    }
}

// END MailerServiceProvider Class

/* End of file MailerServiceProvider.php */
/* Location: .Obullo/ServiceProviders/MailerServiceProvider.php */