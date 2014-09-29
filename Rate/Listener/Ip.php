<?php

namespace Obullo\Rate\Listener;

/**
 * Rate Listener Ip
 * 
 * @category  Listener
 * @package   Ip
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Ip extends Adapter implements ListenerInterface
{
    /**
     * Constructor
     * 
     * @param object $c       container
     * @param string $ip      ip address
     * @param string $channel channel
     * @param array  $params  parameters
     */
    public function __construct($c, $ip = '', $channel = 'Global', array $params = array())
    {
        parent::__construct($c, $ip, $channel, $params);
    }

    /**
     * Get current listener
     * 
     * @return string
     */
    public function getListener()
    {
        return 'Ip';
    }
}


// END Ip Class

/* End of file Ip.php */
/* Location: .Obullo/Rate/Listener/Ip.php */