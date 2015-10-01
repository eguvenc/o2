<?php

namespace Obullo\Container\Loader;

/**
 * Php Service Loader for Obullo Container
 * 
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class PhpServiceLoader
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Env variable
     * 
     * @var string
     */
    protected $env;

    /**
     * Service files
     * 
     * @var array
     */
    protected $services;

    /**
     * Service lazy loading stack
     * 
     * @var array
     */
    protected $registered;

    /**
     * Register service path
     * 
     * @var string
     */
    protected static $path;

    /**
     * Service folder namespace
     * 
     * @var string
     */
    protected static $folder;

    /**
     * Scan service folder
     * 
     * @return object
     */
    public function scan()
    {
        $this->services = scandir($this->getPath().'/'.$this->getFolder());
        return $this;
    }

    /**
     * Returns service files / folders array
     * 
     * @return array
     */
    public function __invoke()
    {
        $safeServices = array();
        foreach ($this->services as $value) {
            // Allow to use Provider directory to locate custom service providers
            if ($value != "." && $value != ".." && $value != "Provider") {
                $safeServices[] = $value;
            } 
        }
        return $safeServices;
    }

    /**
     * Resolve service 
     * 
     * @param object $c        container
     * @param string $class    container id
     * @param array  $services service files
     * 
     * @return boolean
     */
    public function resolve($c, $class, $services)
    {
        $cid  = strtolower($class);  // service container id
        $name = ucfirst($class);     // service name
        $this->env = $c->getEnv();

        $isDir = in_array($name, $services);

        if ($isDir || in_array($name.'.php', $services)) {  // Resolve services

            $Class = $this->resolveNamespace($name, $isDir);

            if (! isset($this->registered[$name])) {

                $service = new $Class($c);
                $service->register($c);

                if (! $c->has($cid)) {
                    throw new RuntimeException(
                        sprintf(
                            "%s service configuration error service class name must be same with container key.",
                            $name
                        )
                    );
                }
                $this->registered[$name] = true;
            }
            return true;
        }
        return false;
    }

    /**
     * Reuturns to service folder namespace using service loader object.
     * 
     * If its a directory we use app environment.
     * 
     * @param string  $name  service class name
     * @param boolean $isDir is it directory or not
     * 
     * @return string
     */
    protected function resolveNamespace($name, $isDir = false)
    {
        $namespace = str_replace(DIRECTORY_SEPARATOR, "\\", $this->getFolder());
        if ($isDir) {
            return '\\'.$namespace.'\\'.$name.'\\'. ucfirst($this->env);
        }
        return '\\'.$namespace.'\\'.$name;
    }

    /**
     * Register service path
     * 
     * @param string $path folder path
     * 
     * @return void
     */
    public function registerPath($path)
    {
        static::$path = $path;
    }

    /**
     * Returns to service path
     * 
     * @return string
     */
    public function getPath()
    {
        return static::$path;
    }

    /**
     * Register service folder namespace
     * 
     * @param string $folder service folder
     * 
     * @return object
     */
    public function registerFolder($folder)
    {
        static::$folder = $folder;
        return $this;
    }

    /**
     * Returns to service namespace
     * 
     * @return string
     */
    public function getFolder()
    {
        return static::$folder;
    }

}