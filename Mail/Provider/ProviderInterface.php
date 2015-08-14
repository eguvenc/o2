<?php

namespace Obullo\Mail\Provider;

/**
 * ProviderInterface for Mail Api Providers
 * 
 * @category  Mailer
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/mailer
 */
interface ProviderInterface
{
    /**
     * Constructor
     * 
     * @param array $params config & service parameters
     */
    public function __construct(array $params);
}