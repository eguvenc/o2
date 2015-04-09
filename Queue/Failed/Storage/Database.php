<?php

namespace Obullo\Queue\Failed\Storage;

use Pdo;
use SimpleXMLElement;
use Obullo\Container\Container;
use Obullo\Queue\Failed\FailedJob;

/**
 * FailedJob Database Handler
 * 
 * @category  Queue
 * @package   Failed
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 */
class Database extends FailedJob implements StorageInterface
{
    /**
     * Constuctor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        parent::__construct($c);
    }

    /**
     * Insert job data to storage
     * 
     * @param array $data key value data
     * 
     * @return void
     */
    public function save($data)
    {
        if ($id = $this->dailyExists($data['error_file'], $data['error_line'])) {
            $this->updateRepeat($id);
            return true;
        }
        // Json encode coult not encode the large data
        // Xml Encoding fix the issue, if you see any problem please open an issue from github.
        if ( ! empty($data['error_trace'])) {
            $xml = new SimpleXMLElement('<root/>');
            array_walk_recursive($data['error_trace'], array($xml, 'addChild'));
            $data['error_trace'] = $xml->asXML();
        }
        if ( ! empty($data['error_xdebug'])) {
            $xml = new SimpleXMLElement('<root/>');
            $xml->addChild('xdebug', $data['error_xdebug']);
            $data['error_xdebug'] = $xml->asXML();
        }
        $data['failure_first_date'] = time();

        $e = $this->db->transaction(
            function () use ($data) {
                $this->db->insert($this->table, $data);
            }
        );
        return ($e === true) ? true : false;
    }

    /**
     * Check same error is daily exists
     *
     * @param string  $file error file
     * @param integer $line error line
      * 
     * @return void
     */
    public function dailyExists($file, $line)
    {
        $this->db->select('id, failure_first_date');
        $this->db->where('error_file', $file);
        $this->db->where('error_line', $line);
        $this->db->limit(1);
        $row = $this->db->get($this->table)->row();

        if ($row == false) {
            return false;
        }
        if (date('Y-m-d') == date('Y-m-d', $row->failure_first_date)) {
            return $row->id;
        }
        return $row->id;
    }

    /**
     * Update repeats
     * 
     * @param integer $id queue failure id
     * 
     * @return void
     */
    public function updateRepeat($id)
    {
        $e = $this->db->transaction(
            function () use ($id) {
                $this->db->where('id', $id);
                $this->db->set('failure_last_date', time(), false);
                $this->db->set('failure_repeat', 'failure_repeat + 1', false);
                $this->db->update($this->table);
            }
        );
        return ($e === true) ? true : false;
    }

}

// END Database class

/* End of file Database.php */
/* Location: .Obullo/Queue/Failed/Storage/Database.php */