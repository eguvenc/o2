<?php

namespace Obullo\Mail\Provider;

use Obullo\Container\ContainerInterface;

/**
 * ProviderInterface for Mail Api Providers
 * 
 * @category  Mailer
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/mailer
 */
interface ProviderInterface
{
    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params config & service parameters
     */
    public function __construct(ContainerInterface $c, array $params);
}