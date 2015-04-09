<?php

namespace Obullo\Queue\Failed\Storage;

use Obullo\Container\Container;

/**
 * Storage Handler Interface
 * 
 * @category  Queue
 * @package   StorageInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
interface StorageInterface
{
    /**
     * Constuctor
     *
     * @param object $c container
     */
    public function __construct(Container $c);

    /**
     * Insert failde job data to table
     * 
     * @param array $data failed data
     * 
     * @return boolean
     */
    public function save($data);

    /**
     * Check same error exists
     *
     * @param string  $file error file
     * @param integer $line error line
      * 
     * @return void
     */
    public function dailyExists($file, $line);

    /**
     * Update failure repeats
     * 
     * @param integer $jobId queue delivery tag
     * 
     * @return void
     */
    public function updateRepeat($jobId);
}

// END StorageInterface class

/* End of file StorageInterface.php */
/* Location: .Obullo/Queue/Failed/Storage/StorageInterface.php */