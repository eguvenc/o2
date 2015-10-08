<?php

namespace Obullo\Container;

use ReflectionClass;
use RuntimeException;

/**
 * Dependency Manager
 * 
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Dependency
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Components
     * 
     * @var array
     */
    protected $components;

    /**
     * Dependecies
     * 
     * @var array
     */
    protected $dependencies;

    /**
     * Set container
     * 
     * @param ContainerInterface|null $c Container
     *
     * @return object
     */
    public function __construct(ContainerInterface $c = null)
    {
        $this->c = $c;
    }

    /**
     * Add component 
     * 
     * @param integer $cid   container id
     * @param string  $class class path
     *
     * @return instance of class
     */
    public function addComponent($cid, $class)
    {
        $this->c[$cid] = function () use ($cid, $class) {
            $this->component[$cid] = $class;
            return $this->resolveDependencies($cid, $class);
        };
    }

    /**
     * Add class to dependecies
     * 
     * @param string $cid class name
     * 
     * @return object
     */
    public function addDependency($cid)
    {
        $this->dependencies[$cid] = $cid;
        return $this;
    }

    /**
     * Remove class from dependencies
     * 
     * @param string $cid 
     * 
     * @return void
     */
    public function removeDependency($cid)
    {
        if (isset($this->dependencies[$cid])) {
            unset($this->dependencies[$cid]);
        }
    }

    /**
     * Resolve dependecies
     * 
     * @param string $cid   class name
     * @param string $class path
     * 
     * @return object class instance
     */
    public function resolveDependencies($cid, $class)
    {
        $Class = '\\'.ltrim($class, '\\');
        $reflector = new ReflectionClass($Class);
        if (! $reflector->hasMethod('__construct')) {
            return $reflector->newInstance();
        } else {
            return $reflector->newInstanceArgs($this->resolveParams($reflector, $cid));
        }
    }

    /**
     * Resolve dependecy parameters
     * 
     * @param \ReflectionClass $reflector reflection instance
     * @param string           $component cid class name
     *
     * @return array params
     */
    protected function resolveParams(ReflectionClass $reflector, $component)
    {
        $parameters = $reflector->getConstructor()->getParameters();
        $params = array();
        foreach ($parameters as $parameter) {
            
            $d = $parameter->getName();

            if ($d == 'c' || $d == 'container') {
                $params[] = $this->c;
            } else {
                $deps = $this->getDependencies();
                $isComponent = isset($deps[$d]);
                if ($isComponent) {
                    $params[] = $this->c[$d];
                } else {

                    if ($isComponent) {
                        throw new RuntimeException(
                            sprintf(
                                'Dependency is missing for "%s" package. <pre>%s $%s</pre>',
                                $component,
                                $parameter->getClass()->name,
                                $d
                            )
                        );
                    }
                }
            }
        }
        return $params;
    }

    /**
     * Get all dependencies
     * 
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Get all components
     * 
     * @return array
     */
    public function getComponents()
    {
        return $this->components;
    }

}
