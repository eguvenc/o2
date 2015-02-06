<?php

namespace Obullo\Queue\Failed\Storage;

use Pdo;
use Container;
use SimpleXMLElement;
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
Class Database extends FailedJob implements StorageInterface
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
        // Json encode coult not encode the large arrays
        // Xml Encoding fix the issue.
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
        return $this->db->insert($this->table, $data);
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
        $this->db->prepare('SELECT id, failure_first_date FROM %s WHERE error_file = ? AND error_line = ? LIMIT 1', array($this->db->protect($this->table)));
        $this->db->bindValue(1, $file, PDO::PARAM_STR);
        $this->db->bindValue(2, $line, PDO::PARAM_INT);
        $this->db->execute();
        $row = $this->db->row();
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
        $this->db->prepare('UPDATE %s SET failure_repeat = failure_repeat + 1, failure_last_date = ? WHERE id = ?', array($this->db->protect($this->table)));
        $this->db->bindValue(1, time(), PDO::PARAM_INT);
        $this->db->bindValue(2, $id, PDO::PARAM_INT);
        $this->db->execute();
    }

}

// END Database class

/* End of file Database.php */
/* Location: .Obullo/Queue/Failed/Storage/Database.php */