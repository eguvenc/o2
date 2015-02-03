<?php

namespace Obullo\Addons\Authentication;

use RuntimeException,
    Obullo\Container\Container;

trait UniqueSessionTrait
{
     /**
     * On unique session event addon
     * 
     * @return void
     */
    public function onUniqueSession()
    {
        $sessions = $this->c['auth.storage']->getAllSessions();

        if (sizeof($sessions) < 1) {  // If user have more than one auth session continue to destroy them.
            return;
        }
        $sessionKeys = array();  
        foreach ($sessions as $key => $val) {       // Keep the last session
            $sessionKeys[$val['__time']] = $key;
        }
        $lastSession = max(array_keys($sessionKeys));   // Get the highest integer time
        $protectedSession = $sessionKeys[$lastSession];
        unset($sessions[$protectedSession]);            // Don't touch the current session

        foreach (array_keys($sessions) as $aid) {   // Destroy all other sessions
            $this->c['auth.storage']->killSession($aid);
        }
    }

}

// END UniqueSessionTrait.php File
/* End of file UniqueSessionTrait.php

/* Location: .Obullo/Addons/Authentication/UniqueSessionTrait.php */