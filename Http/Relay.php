<?php

namespace Obullo\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Obullo\Container\ContainerInterface as Container;

use Relay\RelayBuilder;

/**
 * Relay wrapper
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Relay
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     * 
     * @param Obullo\Container\ContainerInterface $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Creates relay application
     * 
     * @param Psr\Http\Message\ServerRequestInterface $request  request
     * @param Psr\Http\Message\ResponseInterface      $response response
     * @param callable                                $out      final handler
     * 
     * @return response object
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $out = null;
        $dispatcher = $this->pipe($this->c['middleware']->getQueue());
        $response = $dispatcher($request, $response);
        return $response;
    }

    /**
     * Returns to relayBuilder
     * 
     * @param array $queue middleware queue
     * 
     * @return object
     */
    public function pipe(array $queue)
    {
        $relay = new RelayBuilder;
        return $relay->newInstance(
            $queue
        );
    }
}
