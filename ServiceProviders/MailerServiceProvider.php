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
Class MailerServiceProvider
{
    /**
     * Registry
     *
     * @param object $c      container
     * @param array  $params parameters
     * 
     * @return void
     */
    public function register(Container $c, $params = array())
    {
        $connector = new MailerConnectionProvider($c);
        return $connector->getFactory($params);   // Get a mailer instance before we registered into container
    }
}

// END MailerServiceProvider Class

/* End of file MailerServiceProvider.php */
/* Location: .Obullo/ServiceProviders/MailerServiceProvider.php */