<?php

namespace Obullo\Session\MetaData;

/**
 * MetaData NullMetaData Class
 * 
 * @category  Session
 * @package   MetaData
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
class NullMetaData
{
    /**
     * NullMetaData isValid function always true
     * 
     * @return boolean true
     */
    public function isValid()
    {
        return true;
    }

    /**
     * Stores meta data into $this->meta variable.
     * 
     * @return void
     */
    public function build()
    {
        return;
    }

    /**
     * Create meta data
     * 
     * @return void
     */
    public function create()
    {
        return;
    }

    /**
     * Update meta data
     * 
     * @return void
     */
    public function update()
    {
        return;
    }

    /**
     * Remove meta data
     * 
     * @return void
     */
    public function remove()
    {
        return;
    }
    
    /**
     * Read metadata from session
     * 
     * @return array
     */
    public function read()
    {
        return array();
    }
}

// END NullMetaData.php File
/* End of file NullMetaData.php

/* Location: .Obullo/Session/MetaData/NullMetaData.php */