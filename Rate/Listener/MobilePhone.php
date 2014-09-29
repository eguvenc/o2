<?php

namespace Obullo\Rate\Listener;

/**
 * Rate Listener Mobile Phone
 * 
 * @category  Listener
 * @package   MobilePhone
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class MobilePhone extends Adapter implements ListenerInterface
{
    /**
     * Constructor
     * 
     * @param object $c           container
     * @param string $mobilePhone mobile phone
     * @param string $channel     channel
     * @param array  $params      parameters
     */
    public function __construct($c, $mobilePhone, $channel = 'Global', array $params = array())
    {
        parent::__construct($c, $mobilePhone, $channel, $params);
    }

    /**
     * Get current listener
     * 
     * @return string
     */
    public function getListener()
    {
        return 'MobilePhone';
    }
}


// END MobilePhone Class

/* End of file MobilePhone.php */
/* Location: .Obullo/Rate/Listener/MobilePhone.php */