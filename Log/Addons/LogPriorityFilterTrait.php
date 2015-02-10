<?php

namespace Obullo\Log\Addons;

use Obullo\Log\Logger;

trait LogPriorityFilterTrait
{
    /**
     * Filter in array
     * 
     * @param array $record unformatted record data
     * 
     * @return array
     */
    public function filter(array $record)
    {
        $priority = Logger::$priorities[$record['level']];
        if (in_array($priority, $this->priorities)) {
            return $record;
        }
        return array();  // To remove the record we return to empty array.
    }

    /**
     * Filter "not" in array
     * 
     * @param array $record unformatted record data
     * 
     * @return array
     */
    public function notIn(array $record)
    {
        $priority = Logger::$priorities[$record['level']];
        if ( ! in_array($priority, $this->priorities)) {
            return $record;
        }
        return array();  // To remove the record we return to empty array.
    }
}

// END LogPriorityFilterTrait File
/* End of file LogPriorityFilterTrait.php

/* Location: .Obullo/Application/Addons/LogPriorityFilterTrait.php */