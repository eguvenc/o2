<?php

namespace Obullo\Service\Providers;

use Obullo\Container\Container;
use Obullo\Service\ServiceProviderInterface;
use Obullo\Service\Providers\Connections\MailerConnectionProvider;

/**
 * Mailer Service Provider
 *
 * @category  Provider
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class MailerServiceProvider implements ServiceProviderInterface
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
        $this->connector = new MailerConnectionProvider($c);  // Register all Connectors as shared services
    }

    /**
     * Create new connection
     * 
     * @param array $params array
     *
     * @return object
     */
    public function get($params = array())
    {
        return $this->connector->factory($params);  // Create new mailer instance
    }

}

// END MailerServiceProvider Class

/* End of file MailerServiceProvider.php */
/* Location: .Obullo/Service/Providers/MailerServiceProvider.php */