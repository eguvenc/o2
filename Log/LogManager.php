<?php

namespace Obullo\Log;

use Obullo\Container\ContainerInterface;
use Obullo\Container\ServiceInterface;

/**
 * Log Service Manager
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class LogManager implements ServiceInterface
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
     * @param ContainerInterface $c      container
     * @param array              $params service parameters
     */
    public function __construct(ContainerInterface $c, array $params)
    {
        $this->c = $c;
        
        if ($this->c['config']['log']['enabled']) {
            $this->c['logger.params'] = array_merge($params, $c['config']->load('logger'));
        }
    }

    /**
     * Register
     * 
     * @return object logger
     */
    public function register()
    {
        $this->c['logger'] = function () {

            if (! $this->c['config']['log']['enabled']) {
                return new NullLogger;
            }
            return new Logger($this->c, $this->c['logger.params']);
        };
    }

}