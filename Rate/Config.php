<?php

namespace Obullo\Rate;

/**
 * Rate Limiter Config
 * 
 * @category  Rate
 * @package   Config
 * @author    Ali İhsan Çağlayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Config
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
     * Enabled to service
     * 
     * @var boolean
     */
    protected $isEnable = true;

    /**
     * Ban expiration time
     * 
     * @var integer
     */
    protected $banExpiration = 100;

    /**
     * Is ban active
     * 
     * @var boolean
     */
    protected $isBanActive = false;

    /**
     * Limiter name (ip, username, email)
     * 
     * @var string
     */
    protected $limiter;

    /**
     * Cache service 
     * 
     * @var object
     */
    protected $cache;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->cache = $c->load('return service/cache');
    }

    /**
     * Initialize to config parameters
     * 
     * @param array $params parameters
     * 
     * @return void
     */
    public function init(array $params = array())
    {
        $ban = $params['ban'];
        $limit = $params['limit'];

        $this->setIntervalLimit($limit['interval']['amount'], $limit['interval']['maxRequest']);
        $this->setHourlyLimit($limit['hourly']['amount'], $limit['hourly']['maxRequest']);
        $this->setDailyLimit($limit['daily']['amount'], $limit['daily']['maxRequest']);

        $this->setBanStatus($ban['status']);
        $this->setBanExpiration($ban['expiration']);
    }

    /**
     * Set identifier name
     * 
     * @param string $identifier name
     *
     * @return void
     */
    public function setIdentifier($identifier = 'ip')
    {
        $this->identifier = $identifier;
    }

    /**
     * Get current identifier name
     * 
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Read config from cache
     *
     * @param string $channel name
     * 
     * @return If data empty return null
     */
    public function read($channel)
    {
        $config = $this->cache->get(Rate::RATE_LIMITER_CONFIG .':'. $channel .':'. $this->getIdentifier());

        if ($config == false) { // If not exist in the cache
            $config = $this->save($channel);
        }
        $limit = $config['limit'];

        $this->resetLimits();
        $this->setIntervalLimit($limit['interval']['amount'], $limit['interval']['maxRequest']);
        $this->setHourlyLimit($limit['hourly']['amount'], $limit['hourly']['maxRequest'], true);
        $this->setDailyLimit($limit['daily']['amount'], $limit['daily']['maxRequest'], true);

        return $config;
    }

    /**
     * Save config to cache
     *
     * @param string $channel name
     * 
     * @return void
     */
    public function save($channel)
    {
        $config = array(
            'limit' => array(
                'interval' => array('amount' => $this->getIntervalLimit(), 'maxRequest' => $this->getIntervalMaxRequest()),  // 300 seconds / 7 times
                'hourly' => array('amount' => $this->getHourlyLimit(), 'maxRequest' => $this->getHourlyMaxRequest()),     // 1 hour / 15 times
                'daily' => array('amount' => $this->getDailyLimit(), 'maxRequest' => $this->getDailyMaxRequest()),      // 1 day / 50 times
            ),
            'ban' => array(
                'status' => $this->getBanStatus(),          // If ban status disabled don't do ban
                'expiration' => $this->getBanExpiration(),  // If ban status enablead wait for this time
            ),
            'enabled' => $this->isEnabled()
        );
        $this->cache->set(Rate::RATE_LIMITER_CONFIG .':'. $channel .':'. $this->getIdentifier(), $config, Rate::CACHE_CONFIG_EXPIRATION);

        return $config;
    }

    /**
     * Set interval limit.
     * 
     * @param int $seconds    interval limit
     * @param int $maxRequest request count
     * 
     * @return void
     */
    public function setIntervalLimit($seconds = 180, $maxRequest = 2)
    {
        $this->intervalAmount     = (int)$seconds;
        $this->intervalMaxRequest = (int)$maxRequest;
    }

    /**
     * Set hourly limit.
     * 
     * @param int  $hourlyAmount hourly limit
     * @param int  $maxRequest   request count
     * @param bool $min          minutes
     * 
     * @return void
     */
    public function setHourlyLimit($hourlyAmount = 1, $maxRequest = 10, $min = false)
    {
        $this->hourlyAmount = (60 * 60) * $hourlyAmount; // 60 * 60 * 1 = 3600 second (1 hour)
        if ($min === true) {
            $this->hourlyAmount = (int)$hourlyAmount;
        }
        $this->hourlyMaxRequest = (int)$maxRequest;
    }

    /**
     * Set daily limit.
     * 
     * @param int  $dailyAmount daily amount limit
     * @param int  $maxRequest  request count
     * @param bool $min         minutes
     * 
     * @return void
     */
    public function setDailyLimit($dailyAmount = 1, $maxRequest = 30, $min = false)
    {
        $this->dailyAmount = ((60 * 60) * 24) * $dailyAmount; // 60 * 60 * 24 * 1 = 86400 second (1 day)
        if ($min === true) {
            $this->dailyAmount = (int)$dailyAmount;
        }
        $this->dailyMaxRequest = (int)$maxRequest;
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
     * Get ban status
     * 
     * @return integer
     */
    public function getBanStatus()
    {
        return $this->isBanActive;
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
     * Get ban expiration time
     * 
     * @return int
     */
    public function getBanExpiration()
    {
        return $this->banExpiration;
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


// END Config Class

/* End of file Config.php */
/* Location: .Obullo/Rate/Config.php */