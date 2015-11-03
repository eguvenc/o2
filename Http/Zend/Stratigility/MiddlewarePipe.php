<?php

namespace Obullo\Http\Zend\Stratigility;

use Obullo\Log\Benchmark;
use Obullo\Http\Middleware\MiddlewareInterface;
use Obullo\Container\ContainerInterface as Container;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SplQueue;

/**
 * Pipe middleware like unix pipes.
 *
 * This class implements a pipe-line of middleware, which can be attached using
 * the `pipe()` method, and is itself middleware.
 *
 * The request and response objects are decorated using the Zend\Stratigility\Http
 * variants in this package, ensuring that the request may store arbitrary
 * properties, and the response exposes the convenience `write()`, `end()`, and
 * `isComplete()` methods.
 *
 * It creates an instance of `Next` internally, invoking it with the provided
 * request and response instances; if no `$out` argument is provided, it will
 * create a `FinalHandler` instance and pass that to `Next` as well.
 *
 * Inspired by Sencha Connect.
 *
 * @see https://github.com/sencha/connect
 */
class MiddlewarePipe implements MiddlewareInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * SplQueue
     * 
     * @var object
     */
    protected $pipeline;

    /**
     * Constructor
     *
     * Initializes the queue.
     * 
     * @param Container $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->pipeline = new SplQueue();

        $c['middleware']->queue('App');
        $c['middleware']->queue('Error');  // Error middleware must be defined end of the queue.
        
        foreach ($c['middleware']->getQueue() as $middleware) {
            $this->pipe($middleware);
        }
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
        $class = '\\Http\Middlewares\FinalHandler\\Zend';

        return new $class(
            [
                'env' => $this->c['app.env']
            ],
            $this->c['logger'],
            $response
        );
    }

    /**
     * Handle a request
     *
     * Takes the pipeline, creates a Next handler, and delegates to the
     * Next handler.
     *
     * If $out is a callable, it is used as the "final handler" when
     * $next has exhausted the pipeline; otherwise, a FinalHandler instance
     * is created and passed to $next during initialization.
     *
     * @param Request  $request  request
     * @param Response $response response
     * @param callable $out      callable
     * 
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $request  = $this->decorateRequest($request);
        $response = $this->decorateResponse($response);
        $done     = $this->getFinalHandler($response);

        $next   = new Next($this->pipeline, $done, $this->c);
        $result = $next($request, $response);

        return ($result instanceof Response ? $result : $response);
    }

    /**
     * Attach middleware to the pipeline.
     *
     * Each middleware can be associated with a particular path; if that
     * path is matched when that middleware is invoked, it will be processed;
     * otherwise it is skipped.
     *
     * No path means it should be executed every request cycle.
     *
     * A handler CAN implement MiddlewareInterface, but MUST be callable.
     *
     * Handlers with arity >= 4 or those implementing ErrorMiddlewareInterface
     * are considered error handlers, and will be executed when a handler calls
     * $next with an error or raises an exception.
     *
     * @see MiddlewareInterface
     * @see ErrorMiddlewareInterface
     * @see Next
     * @param string|callable|object $path Either a URI path prefix, or middleware.
     * @param null|callable|object $middleware Middleware
     * @return self
     */
    public function pipe($path, $middleware = null)
    {
        if (null === $middleware && is_callable($path)) {
            $middleware = $path;
            $path       = '/';
        }

        // Ensure we have a valid handler
        if (! is_callable($middleware)) {
            throw new InvalidArgumentException('Middleware must be callable');
        }

        $this->pipeline->enqueue(
            new Route(
                $this->normalizePipePath($path),
                $middleware
            )
        );

        // @todo Trigger event here with route details?
        return $this;
    }

    /**
     * Normalize a path used when defining a pipe
     *
     * Strips trailing slashes, and prepends a slash.
     *
     * @param string $path
     * @return string
     */
    private function normalizePipePath($path)
    {
        // Prepend slash if missing
        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }

        // Trim trailing slash if present
        if (strlen($path) > 1 && '/' === substr($path, -1)) {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    /**
     * Decorate the Request instance
     *
     * @param Request $request
     * @return Http\Request
     */
    private function decorateRequest(Request $request)
    {
        if ($request instanceof Http\Request) {
            return $request;
        }

        return new Http\Request($request);
    }

    /**
     * Decorate the Response instance
     *
     * @param Response $response
     * @return Http\Response
     */
    private function decorateResponse(Response $response)
    {
        if ($response instanceof Http\Response) {
            return $response;
        }

        return new Http\Response($response);
    }
}
