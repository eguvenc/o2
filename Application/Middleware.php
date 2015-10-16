<?php

namespace Obullo\Application;

use RuntimeException;
use Obullo\Container\ContainerInterface;

/**
 * Middleware
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Middleware
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Count
     * 
     * @var integer
     */
    protected $count;

    /**
     * Middleware stack
     * 
     * @var array
     */
    protected $middlewares = array();

    /**
     * Registered middlewares
     * 
     * @var array
     */
    protected $registered = array();

    /**
     * Names
     * 
     * @var array
     */
    protected $names;

    /**
     * Constructor
     * 
     * @param ContainerInterface $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Register application middlewares
     * 
     * @param array $array middlewares
     * 
     * @return object Middleware
     */
    public function configure(array $array)
    {
        $this->registered = $array;

        $this->add('Begin');  // Add first middleware
        return $this;
    }

    /**
     * Add middleware
     * 
     * @param string|array $name middleware key
     * 
     * @return object Middleware
     */
    public function add($name)
    {
        if (is_string($name)) {
            return $this->resolveMiddleware($name);
        } elseif (is_array($name)) { 
            foreach ($name as $key) {
                $this->resolveMiddleware($key);
            }
        }
        return $this;
    }

    /**
     * Resolve middleware
     * 
     * @param string $name middleware key
     * 
     * @return object mixed
     */
    protected function resolveMiddleware($name)
    {
        $this->validateMiddleware($name);
        $Class = $this->registered[$name];
        ++$this->count;
        $this->names[$name] = $this->count;
        return $this->middlewares[$this->count] = $this->c['dependency']->resolveDependencies($name, $Class);  // Store middlewares
    }

    /**
     * Removes middleware
     * 
     * @param string|array $name middleware key
     * 
     * @return void
     */
    public function remove($name)
    {
        if (is_string($name)) {
            $this->validateMiddleware($name);
            $index = $this->middlewaresNames[$name];
            unset($this->middlewares[$index], $this->names[$name]);
            --$this->count;
        }
        if (is_array($name)) {
            foreach ($name as $key) {
                $this->remove($key);
            }
        }
    }

    /**
     * Validate middleware
     * 
     * @param string $name middleware
     * 
     * @return void
     */
    protected function validateMiddleware($name)
    {
        if (! isset($this->registered[$name])) {
            throw new RuntimeException(
                sprintf(
                    'Middleware "%s" is not registered in middlewares.php',
                    $name
                )
            );
        }
    }

    /**
     * Returns to all middleware objects
     * 
     * @return array
     */
    public function getValues()
    {
        $this->add('Finalize'); // Add last middleware

        return array_values($this->middlewares);
    }

    /**
     * Returns to all middleware names
     * 
     * @return array
     */
    public function getNames()
    {
        return array_keys($this->names);
    }

}