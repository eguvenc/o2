<?php

namespace Obullo\User;

/**
 * Generate random id
 * 
 * @category  User
 * @package   User
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/user
 */
Class Uid
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Identifier
     * 
     * @var mixed
     */
    public $id;

    /**
     * Server hostname
     * 
     * @var string
     */
    public $hostname;

    /**
     * Ethernet mac address
     * 
     * @var string
     */
    public $macAddress;

    /**
     * User ip with ip2long();
     * 
     * @var integer
     */
    public $ip;

    /**
     * User agent
     * 
     * @var string
     */
    public $agent;

    /**
     * Separator
     * 
     * @var string
     */
    public $separator;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Add separator
     * 
     * @param string $separator separate strings
     * 
     * @return object
     */
    public function separator($separator = '-')
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Add username, email any string
     * 
     * @param string $identifier user name
     *
     * @return object
     */
    public function addId($identifier = '')
    {
        $this->id = $identifier;

        return $this;
    }

    /**
     * Add mac address - cache exec() output to locale memory
     * 
     * @return object
     */
    public function addMacAddress()
    {
        $this->memory = $this->c->load('memory'); // Use machine locale memory.

        if ($this->memory->exists('__macAddress')) {
            $this->macAddress = $this->memory->get('__macAddress');
            return $this;
        } 
        $this->macAddress = '{'.exec("ifconfig eth0 | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}'").'}';
        $this->memory->set('__macAddress', $this->macAddress);  // Store output to locale machine memory
        return $this;
    }

    /**
     * Add user ip
     * 
     * @return object
     */
    public function addIp()
    {
        $this->ip = sprintf("%u", ip2long($this->c['request']->ip()));

        return $this;
    }

    /**
     * Add hostname of server
     * 
     * @return object
     */
    public function addHostname()
    {
        $this->hostname = gethostname();

        return $this;
    }

    /**
     * Add user agent
     *
     * @return object
     */
    public function addAgent()
    {
        $this->agent = md5($_SERVER['HTTP_USER_AGENT']);

        return $this;
    }

    /**
     * Generate random unique "integer"
     * 
     * @param boolean $moreEntropy force to more unique
     * 
     * @return integer
     */
    public function generateNumbers($moreEntropy = false)
    {
        $randomInt = sprintf("%u", crc32(uniqid(mt_rand(), $moreEntropy)));

        $prefix = '';
        if ( ! empty($this->id)) {
            $prefix.= 'id:'.sprintf("%u", crc32($this->id)).$this->separator;
        }
        if ( ! empty($this->ip)) {
            $prefix.= 'i:'.sprintf("%u", crc32($this->ip)).$this->separator;
        }
        if ( ! empty($this->agent)) {
            $prefix.= 'a:'.sprintf("%u", crc32($this->agent)).$this->separator;
        }
        if ( ! empty($this->macAddress)) {
            $prefix.= 'm:'.sprintf("%u", crc32($this->macAddress)).$this->separator;
        }
        if ( ! empty($this->hostname)) {
            $prefix.= 'h:'.sprintf("%u", crc32($this->hostname)).$this->separator;
        }
        return $prefix.(int)$randomInt;
    }

    /**
     * Generate random unique "string"
     * 
     * @param boolean $moreEntropy force to more unique
     * 
     * @return integer
     */
    public function generateString($moreEntropy = false)
    {
        $randomStr = sprintf("%u", crc32(uniqid(mt_rand(), $moreEntropy)));

        $prefix = '';
        if ( ! empty($this->id)) {
            $prefix.= 'id:'.$this->id.$this->separator;
        }
        if ( ! empty($this->ip)) {
            $prefix.= 'i:'.$this->ip.$this->separator;
        }
        if ( ! empty($this->agent)) {
            $prefix.= 'a:'.$this->agent.$this->separator;
        }
        if ( ! empty($this->macAddress)) {
            $prefix.= 'm:'.$this->macAddress.$this->separator;
        }
        if ( ! empty($this->hostname)) {
            $prefix.= 'h:'.$this->hostname.$this->separator;
        }
        return (string)$prefix.$randomStr;
    }

}

// END Uid Class

/* End of file Uid.php */
/* Location: .Obullo/User/Uid.php */