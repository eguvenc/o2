<?php

namespace Obullo\Authentication\Middleware;

use Obullo\Container\Container;

trait UniqueLoginTrait
{
     /**
     * Terminates multiple sessions.
     * 
     * @return void
     */
    public function uniqueLoginCheck()
    {
        if ($this->c['config']['auth']['session']['unique']) {  // Unique Session is the property whereby a single action of activity

            $sessions = $this->c['auth.storage']->getUserSessions();

            if (empty($sessions) OR sizeof($sessions) == 1) {  // If user have more than one session continue to destroy old sessions.
                return;
            }
            $sessionKeys = array();  
            foreach ($sessions as $key => $val) {       // Keep the last session
                $sessionKeys[$val['__time']] = $key;
            }
            $lastSession = max(array_keys($sessionKeys));   // Get the highest integer time
            $protectedSession = $sessionKeys[$lastSession];
            unset($sessions[$protectedSession]);            // Don't touch the current session

            foreach (array_keys($sessions) as $loginID) {       // Destroy all other sessions
                $this->c['auth.identity']->killSignal($loginID);
            }
            $this->c['logger']->debug('Unique login middleware initialized, user session has been terminated.');
        }
    }

}

// END UniqueLoginTrait.php File
/* End of file UniqueLoginTrait.php

/* Location: .Obullo/Authentication/Middleware/UniqueLoginTrait.php */
