<?php

namespace Obullo\Service\Provider;

use Obullo\Container\AbstractProvider;
use Obullo\Container\ContainerInterface;
use Obullo\Container\ServiceProviderInterface;

use Obullo\Database\Doctrine\DBAL\QueryBuilder;

/**
 * Query Builder Provider
 *
 * @category  Provider
 * @package   QbServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class DoctrineQueryBuilder implements ServiceProviderInterface
{
    /**
     * Container
     *
     * @var object
     */
    public $c;

    /**
     * Constructor
     * 
     * @param string $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Get connection
     *
     * @param array $params array
     *
     * @return object
     */
    public function get($params = array())
    {
        return new QueryBuilder($this->c['app']->provider('database')->get($params)); // Get existing connection
    }

    /**
     * Create unnamed connection
     *
     * @param array $params array
     *
     * @return object
     */
    public function factory($params = array())
    {
        return new QueryBuilder($this->c['app']->provider('database')->factory($params));  // Create new undefined connection
    }
}