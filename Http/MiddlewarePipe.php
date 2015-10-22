<?php

namespace Obullo\Http;

use InvalidArgumentException;
use Zend\Stratigility\MiddlewareInterface;
use Zend\Stratigility\FinalHandler;
use Zend\Stratigility\Next;
use Zend\Stratigility\Route;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SplQueue;

/**
 * Borrowed from Zend Stratigility.
 */
class MiddlewarePipe implements MiddlewareInterface
{
    /**
     * @var SplQueue
     */
    protected $pipeline;

    /**
     * Constructor
     *
     * Initializes the queue.
     */
    public function __construct($request, $response)
    {
        $this->pipeline = new SplQueue();
        $this->request = $request;
        $this->response = $response;
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
     * @param Request $request
     * @param Response $response
     * @param callable $out
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $request  = $this->decorateRequest($request);
        $response = $this->decorateResponse($response);

        $done   = $out ?: new FinalHandler([], $response);
                // echo get_class($response);
        $next   = new Next($this->pipeline, $done);
        $result = $next($request, $response);

        // echo get_class($result);

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

        $this->pipeline->enqueue(new Route(
            $this->normalizePipePath($path),
            $middleware
        ));

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
        if ($request instanceof \Obullo\Http\Request) {
            return $request;
        }
        return $this->request;
    }

    /**
     * Decorate the Response instance
     *
     * @param Response $response
     * @return Http\Response
     */
    private function decorateResponse(Response $response)
    {
        if ($response instanceof \Obullo\Http\Response) {
            return $response;
        }
        return $this->response;
    }
}
