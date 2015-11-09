<?php

namespace Obullo\Http\Zend\Stratigility;

use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Obullo\Http\Zend\Stratigility\Http\Request as DecoratedRequest;
use Obullo\Http\Zend\Stratigility\Http\Response as DecoratedResponse;

trait FinalHandlerTrait {

    /**
     * Handle an error condition
     *
     * Use the $error to create details for the response.
     *
     * @param mixed             $error    error
     * @param RequestInterface  $request  Request instance.
     * @param ResponseInterface $response Response instance.
     * 
     * @return Http\Response
     */
    protected function handleError($error, Request $request, Response $response)
    {
        $response = $response->withStatus(
            Utils::getStatusCode($error, $response)
        );
        $message = $response->getReasonPhrase() ?: 'Unknown Error';

        if (! isset($this->options['env'])
            || $this->options['env'] !== 'production'
        ) {
            $message = $this->createDevelopmentErrorMessage($error);
        }
        $response = $this->completeResponse($response, $message);

        $this->triggerError($error, $request, $response);

        $this->terminate($request);
        return $response;
    }

    /**
     * Create a 404 status in the response
     *
     * @param RequestInterface  $request  Request instance.
     * @param ResponseInterface $response Response instance.
     * 
     * @return Http\Response
     */
    protected function create404(Request $request, Response $response)
    {
        $response = $response->withStatus(404);
        $uri = $this->getUriFromRequest($request);

        $message = sprintf(
            "Cannot %s %s\n",
            htmlspecialchars($request->getMethod(), ENT_QUOTES, 'utf-8'),
            htmlspecialchars((string) $uri, ENT_QUOTES, 'utf-8')
        );

        return $this->completeResponse($response, $message);
    }

    /**
     * Create a complete error message for development purposes.
     *
     * Creates an error message with full error details:
     *
     * - If the error is an exception, creates a message that includes the full
     *   stack trace.
     * - If the error is an object that defines `__toString()`, creates a
     *   message by casting the error to a string.
     * - If the error is not an object, casts the error to a string.
     * - Otherwise, cerates a generic error message indicating the class type.
     *
     * In all cases, the error message is escaped for use in HTML.
     *
     * @param mixed $error error
     * 
     * @return string
     */
    protected function createDevelopmentErrorMessage($error)
    {
        if ($error instanceof Exception) {
            $message  = $error->getMessage() . "\n";
            $message .= $error->getTraceAsString();
        } elseif (is_object($error) && ! method_exists($error, '__toString')) {
            $message = sprintf('Error of type "%s" occurred', get_class($error));
        } else {
            $message = (string) $error;
        }

        return htmlspecialchars($message, ENT_QUOTES, 'utf-8');
    }

    /**
     * Trigger the error listener, if present
     *
     * If no `onerror` option is present, or if it is not callable, does
     * nothing.
     *
     * If the request is not an Http\Request, casts it to one prior to invoking
     * the error handler.
     *
     * @param mixed            $error    errors
     * @param RequestInterface $request  request instance
     * @param Http\Response    $response response instance
     *
     * @return void
     */
    protected function triggerError($error, Request $request, DecoratedResponse $response)
    {
        if (! isset($this->options['onerror'])
            || ! is_callable($this->options['onerror'])
        ) {
            return;
        }
        $onError = $this->options['onerror'];
        $onError(
            $error,
            ($request instanceof DecoratedRequest) ? $request : new DecoratedRequest($request),
            $response
        );
    }

    /**
     * Retrieve the URI from the request.
     *
     * If the request instance is a Stratigility decorator, pull the URI from
     * the original request; otherwise, pull it directly.
     *
     * @param RequestInterface $request request instance
     * 
     * @return \Psr\Http\Message\UriInterface
     */
    protected function getUriFromRequest(Request $request)
    {
        if ($request instanceof DecoratedRequest) {
            $original = $request->getOriginalRequest();
            return $original->getUri();
        }

        return $request->getUri();
    }

    /**
     * Write the given message to the response and mark it complete.
     *
     * If the message is an Http\Response decorator, call and return its
     * `end()` method; otherwise, decorate the response and `end()` it.
     *
     * @param ResponseInterface $response response instance
     * @param string            $message  message
     * 
     * @return Http\Response
     */
    protected function completeResponse(Response $response, $message)
    {
        if ($response instanceof DecoratedResponse) {
            return $response->end($message);
        }

        $response = new DecoratedResponse($response);
        return $response->end($message);
    }


}