<?php

namespace Obullo\Event;

use Obullo\Container\Container;

/**
 * Event Listener Interface
 * 
 * @category  Event
 * @package   EventListenerInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/event
 */
interface EventListenerInterface
{
    /**
     * Constructor
     * 
     * @param object $c container object
     */
    public function __construct(Container $c);
}

// END EventListenerInterface.php File
/* End of file EventListenerInterface.php

/* Location: .Obullo/Event/EventListenerInterface.php */