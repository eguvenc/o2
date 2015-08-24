<?php

namespace Obullo\Mail\Provider;

use Obullo\Log\LoggerInterface;
use Obullo\Container\ContainerInterface;
use Obullo\Translation\TranslatorInterface;

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
     * @param object $c          \Obullo\Container\ContainerInterface
     * @param object $translator \Obullo\Translation\TranslatorInterface
     * @param object $logger     \Obullo\Log\LogInterface
     * @param array  $params     service parameters
     */
    public function __construct(ContainerInterface $c, TranslatorInterface $translator, LoggerInterface $logger, array $params);
}