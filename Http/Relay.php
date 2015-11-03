<?php

namespace Obullo\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Obullo\Container\ContainerInterface as Container;

use Exception;
use Relay\RelayBuilder;
use Obullo\Log\Benchmark;

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
     * Get app request
     * 
     * @return object
     */
    public function getRequest()
    {
        return Benchmark::start($this->c['request']);
    }

    /**
     * Returns to final handler class
     *
     * @param Response $response response
     * 
     * @return object
     */
    public function getFinalHandler($response)
    {
        $class = '\\Http\Middlewares\FinalHandler\\Relay';

        return new $class(
            [
                'env' => $this->c['app.env']
            ],
            $this->c['logger'],
            $response
        );
    }

    /**
     * Creates relay application
     * 
     * @param Psr\Http\Message\ServerRequestInterface $request  request
     * @param Psr\Http\Message\ResponseInterface      $response response
     * @param callable                                $next     final handler
     * 
     * @return response object
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        try {

            $this->c['middleware']->queue('App');
            
            $dispatcher = $this->pipe($this->c['middleware']->getQueue(), $response);
            $response = $dispatcher($request, $response);

        } catch (Exception $e) {
            
            $err = $e;            
            $this->c['app']->handleException($err);
        }

        $done = $this->getFinalHandler($response);

        return $done($request, $response, $err);
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
