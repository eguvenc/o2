<?php

namespace Obullo\Database;

use Obullo\Container\ContainerInterface;

/**
 * DatabaseManager Class
 * 
 * @category  Manager
 * @package   DatabaseManager
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
class DatabaseManager
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Service Parameters
     * 
     * @var array
     */
    protected $params;

    /**
     * Create classes
     * 
     * @param object $c container
     * 
     * @return object
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Set parameters
     * 
     * @param array $params parameters
     *
     * @return void
     */
    public function setParameters(array $params)
    {
        $this->params = $params;
    }

    /**
     * Returns to selected cache handler object
     * 
     * @return object
     */
    public function getProvider()
    {
        return $this->c['app']
            ->provider($this->params['provider']['name'])
            ->get($this->params['provider']['params']);
    }
}