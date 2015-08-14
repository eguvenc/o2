<?php

namespace Obullo\Queue\Failed\Storage;

/**
 * Storage Handler Interface
 * 
 * @category  Queue
 * @package   StorageInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
interface StorageInterface
{
    /**
     * Insert failed event data to storage
     * 
     * @param array $event key value data
     * 
     * @return void
     */
    public function save(array $event);

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