<?php

namespace Obullo\Service\Providers;

use Obullo\Log\Logger;
use Obullo\Log\NullLogger;
use Obullo\Container\Container;
use Obullo\Service\ServiceProviderInterface;

/**
 * LoggerServiceProvider Class
 * 
 * @category  Provider
 * @package   LoggerServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     *
     * @param object $c container
     *
     * @return void
     */
    public function register(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Returns to logger instance
     *
     * @param array $options provider options
     * 
     * @return object
     */
    public function get($options = array('queue' => false))
    {
        if ( ! $this->c['config']['log']['enabled']) {
            return new NullLogger;  // Use null handler if config disabled.
        }
        return new Logger($this->c, $options);
    }
}

// END LoggerServiceProvider class
/* End of file LoggerServiceProvider.php */

/* Location: .Obullo/Service/Providers/LoggerServiceProvider.php */