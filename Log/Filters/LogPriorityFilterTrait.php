<?php

namespace Obullo\Log\Filters;

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
        $priorities = $this->c['logger']->getPriorities();
        
        $priority = $priorities[$record['level']];
        if (in_array($priority, $this->params)) {
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
        $priorities = $this->c['logger']->getPriorities();

        $priority = $priorities[$record['level']];
        if ( ! in_array($priority, $this->params)) {
            return $record;
        }
        return array();  // To remove the record we return to empty array.
    }
}

// END LogPriorityFilterTrait File
/* End of file LogPriorityFilterTrait.php

/* Location: .Obullo/Log/Filters/LogPriorityFilterTrait.php */