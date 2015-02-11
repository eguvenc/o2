<?php

namespace Obullo\Authentication\Addons;

use RuntimeException,
    Obullo\Container\Container;

trait UniqueLoginTrait
{
     /**
     * On unique session addon terminates multiple login sessions.
     * 
     * @return void
     */
    public function uniqueLoginCheck()
    {
        if ($this->c['config']['auth']['activity']['uniqueLogin'] AND $this->c['auth.identity']->check()) {        // Unique Session is the property whereby a single action of activity
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

}

// END UniqueLoginTrait.php File
/* End of file UniqueLoginTrait.php

/* Location: .Obullo/Authentication/Addons/UniqueLoginTrait.php */