<?php

namespace Obullo\Rate\Listener;

/**
 * Http_Request_Listener_Interface
 * 
 * @category  Listener
 * @package   Interface
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
interface ListenerInterface
{
    /**
     * Constructor
     * 
     * @param object $c          container
     * @param string $identifier identifier
     * @param string $channel    channel
     * @param array  $params     parameters
     */
    public function __construct($c, $identifier, $channel, array $params = array());

    /**
     * Get current listener
     * 
     * @return string
     */
    public function getListener();
}


// END Interface Class

/* End of file Interface.php */
/* Location: .Obullo/Rate/Listener/Interface.php */