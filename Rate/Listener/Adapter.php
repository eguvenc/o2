<?php

namespace Obullo\Rate\Listener;

/**
 * Http_Request_Listener_Adapter
 * 
 * @category  Listener
 * @package   Adapter
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Adapter
{
    /**
     * Interval amount.
     * 
     * @var integer
     */
    protected $intervalAmount = 10;

    /**
     * Hourly amount.
     * 
     * @var integer
     */
    protected $hourlyAmount = 1;

    /**
     * Daily amount
     * 
     * @var integer
     */
    protected $dailyAmount = 1;

    /**
     * Interval request count.
     * 
     * @var integer
     */
    protected $intervalMaxRequest = 5;

    /**
     * Hourly request count.
     * 
     * @var integer
     */
    protected $hourlyMaxRequest = 50;

    /**
     * Daily request count.
     * 
     * @var integer
     */
    protected $dailyMaxRequest = 250;

    /**
     * Total request count.
     * 
     * @var integer
     */
    protected $totalRequest = -1;

    /**
     * Ban expiration time
     * 
     * @var integer
     */
    public $banExpiration = 100;

    /**
     * Is ban active
     * 
     * @var boolean
     */
    public $isBanActive = false;

    /**
     * Identifier
     * 
     * @var mixed
     */
    protected $identifier;

    /**
     * Enabled to service
     * 
     * @var boolean
     */
    protected $isEnable = true;

    /**
     * Constructor
     * 
     * @param object $c          container
     * @param string $identifier identifier
     * @param string $channel    channel
     * @param array  $params     parameters
     */
    public function __construct($c, $identifier, $channel, array $params = array())
    {
        $this->setID($identifier);
        $this->setChannel($channel);
        
        if (count($params) > 0) {
            $this->setIntervalLimit(
                $params['INTERVAL_LIMIT']['AMOUNT'],
                $params['INTERVAL_LIMIT']['LIMIT']
            );
            $this->setHourlyLimit(
                $params['HOURLY_LIMIT']['AMOUNT'],
                $params['HOURLY_LIMIT']['LIMIT']
            );
            $this->setDailyLimit(
                $params['DAILY_LIMIT']['AMOUNT'],
                $params['DAILY_LIMIT']['LIMIT']
            );
            $this->setBanStatus($params['BAN']['STATUS']);
            $this->setBanExpiration($params['BAN']['EXPIRATION']);
        }
    }

    /**
     * Set interval limit.
     * 
     * @param int $seconds      interval limit
     * @param int $requestCount request count
     * 
     * @return void
     */
    public function setIntervalLimit($seconds = 180, $requestCount = 2)
    {
        $this->intervalAmount     = (int)$seconds;
        $this->intervalMaxRequest = (int)$requestCount;
    }

    /**
     * Set hourly limit.
     * 
     * @param int  $hourlyAmount hourly limit
     * @param int  $requestCount request count
     * @param bool $min          minutes
     * 
     * @return void
     */
    public function setHourlyLimit($hourlyAmount = 1, $requestCount = 10, $min = false)
    {
        $this->hourlyAmount = (60 * 60) * $hourlyAmount; // 60 * 60 * 1 = 3600 second (1 hour)
        if ($min === true) {
            $this->hourlyAmount = (int)$hourlyAmount;
        }
        $this->hourlyMaxRequest = (int)$requestCount;
    }

    /**
     * Set daily limit.
     * 
     * @param int  $dailyAmount  daily amount limit
     * @param int  $requestCount request count
     * @param bool $min          minutes
     * 
     * @return void
     */
    public function setDailyLimit($dailyAmount = 1, $requestCount = 30, $min = false)
    {
        $this->dailyAmount = ((60 * 60) * 24) * $dailyAmount; // 60 * 60 * 24 * 1 = 86400 second (1 day)
        if ($min === true) {
            $this->dailyAmount = (int)$dailyAmount;
        }
        $this->dailyMaxRequest = (int)$requestCount;
    }

    /**
     * Reset limits
     * 
     * @return void
     */
    public function resetLimits()
    {
        $this->intervalAmount     = '';
        $this->intervalMaxRequest = '';
        $this->hourlyAmount       = '';
        $this->hourlyMaxRequest   = '';
        $this->dailyAmount        = '';
        $this->dailyMaxRequest    = '';
    }

    /**
     * Set ban time
     * 
     * @param int $expiration ban time
     * 
     * @return void
     */ 
    public function setBanExpiration($expiration)
    {
        $this->banExpiration = (int)$expiration;
    }

    /**
     * Set ban for enable
     * 
     * @param boolean $enable set enable
     * 
     * @return void
     */
    public function setBanStatus($enable = true)
    {
        $this->isBanActive = (boolean)$enable;
    }

    /**
     * Set channel
     * 
     * @param string $channel channel
     * 
     * @return void
     */
    public function setChannel($channel)
    {
        $this->channel = (string)$channel;
    }

    /**
     * Set total request count
     * 
     * @param int $totalRequest total request count
     * 
     * @return int
     */
    public function setTotalRequest($totalRequest)
    {
        return $this->totalRequest = (int)$totalRequest;
    }

    /**
     * Set identifier
     * 
     * @param mix $identifier identifier
     * 
     * @return void
     */
    public function setID($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Set enable
     * 
     * @param boolean $enable enable
     * 
     * @return void
     */
    public function setEnable($enable = true)
    {
        $this->isEnable = (boolean)$enable;
    }

    /**
     * Get enable
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isEnable;
    }

    /**
     * Get unique identifier
     * (ip, username, mobile phone)
     * 
     * @return string
     */
    public function getID()
    {
        return $this->identifier;
    }

    /**
     * Get ban status
     * 
     * @return integer
     */
    public function getBanStatus()
    {
        return $this->isBanActive;
    }

    /**
     * Get ban limit
     * 
     * @return integer
     */
    public function getBanLimit()
    {
        return $this->banExpiration;
    }

    /**
     * Get channel
     * 
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Get interval request count
     * 
     * @return int
     */
    public function getIntervalLimit()
    {
        return $this->intervalAmount;
    }

    /**
     * Get interval request count
     * 
     * @return int
     */
    public function getIntervalMaxRequest()
    {
        return $this->intervalMaxRequest;
    }

    /**
     * Get hourly limit
     * 
     * @return int
     */
    public function getHourlyLimit()
    {
        return $this->hourlyAmount;
    }

    /**
     * Get hourly request count
     * 
     * @return int
     */
    public function getHourlyMaxRequest()
    {
        return $this->hourlyMaxRequest;
    }

    /**
     * Get daily limit
     * 
     * @return int
     */
    public function getDailyLimit()
    {
        return $this->dailyAmount;
    }

    /**
     * Get daily request count
     * 
     * @return int
     */
    public function getDailyMaxRequest()
    {
        return $this->dailyMaxRequest;
    }

    /**
     * Get total request count
     * 
     * @return int
     */
    public function getTotalRequest()
    {
        return $this->totalRequest;
    }
}


// END Adapter Class

/* End of file Adapter.php */
/* Location: .Obullo/Rate/Listener/Adapter.php */