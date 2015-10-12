<?php

namespace Obullo\Http\Response;

use Psr\Http\Message\UriInterface;

use InvalidArgumentException;

use Obullo\Log\LoggerInterface;
use Obullo\Container\ContainerInterface;
use Obullo\Http\BenchmarkTrait;
use Obullo\Http\Stream;
use Obullo\Http\Response;

/**
 * Produce a redirect response.
 */
class RedirectResponse
{
    use BenchmarkTrait;

    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Http headers
     * 
     * @var array
     */
    protected $headers;

    /**
     * Create a redirect response.
     *
     * Produces a redirect response with a Location header and the given status
     * (302 by default).
     *
     * Note: this method overwrites the `location` $headers value.
     *
     * @param string|UriInterface       $uri     URI for the Location header.
     * @param object|ContainerInterface $c       Container class
     * @param array                     $headers Array of headers to use at initialization.
     */
    public function __construct($uri, ContainerInterface $c, array $headers = [])
    {
        $this->c = $c;

        if (! is_string($uri) && ! $uri instanceof UriInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Uri provided to %s MUST be a string or Psr\Http\Message\UriInterface instance; received "%s"',
                    __CLASS__,
                    (is_object($uri) ? get_class($uri) : gettype($uri))
                )
            );
        }

        $uri = (string) $uri;
        
        $headers['location'] = [$uri];
        $this->headers = $headers;

        $this->benchmarkEnd('Redirect header sent to browser');  // Close benchmark logging

        $this->c['logger']->shutdown(); // Manually shutdown logger otherwise we use register shutdown
                                        // closing the logger manually is best way ..
    }

    /**
     * Returns to json headers
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
