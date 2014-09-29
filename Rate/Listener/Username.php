<?php

namespace Obullo\Rate\Listener;

/**
 * Rate Listener Username
 * 
 * @category  Listener
 * @package   Username
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Username extends Adapter implements ListenerInterface
{
    /**
     * Constructor
     * 
     * @param object $c        container
     * @param string $username username
     * @param string $channel  channel
     * @param array  $params   parameters
     */
    public function __construct($c, $username = '', $channel = 'User', array $params = array())
    {
        parent::__construct($c, $username, $channel, $params);
    }
    
    /**
     * Get current listener
     * 
     * @return string
     */
    public function getListener()
    {
        return 'Username';
    }
}


// END Username Class

/* End of file Username.php */
/* Location: .Obullo/Rate/Listener/Username.php */