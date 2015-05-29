<?php

namespace Obullo\Service;

use Obullo\Container\Container;

/**
 * Service Interface
 * 
 * @category  Interface
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */  
interface ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register(Container $c);
}

// END ServiceInterface class

/* End of file ServiceInterface.php */
/* Location: .Obullo/Service/ServiceInterface.php */