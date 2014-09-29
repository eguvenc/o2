<?php

namespace Obullo\Rate;

/**
 * Rate Limiter
 * 
 * @category  Http
 * @package   Limiter
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Limiter
{
    const CACHE_CONFIG_EXPIRATION = 604800; // 1 Week (second).
    const CACHE_DATA_EXPIRATION   = 604800; // 1 Week (second).

    // Redis cache main constants
    const RATE_LIMITER_CONFIG = 'RATE_LIMITER_CONFIG';
    const RATE_LIMITER_REQUEST = 'RATE_LIMITER_REQUEST';
    const RATE_LIMITER_REQUEST_DATA = 'RATE_LIMITER_REQUEST_DATA';

    // Redis cache parent constants
    const TOTAL_REQUEST_COUNT  = 'totalRequest';
    const INTERVAL_LIMIT       = 'intervalLimit';
    const INTERVAL_MAX_REQUEST = 'intervalMaxRequest';
    const HOURLY_LIMIT         = 'hourlyLimit';
    const HOURLY_MAX_REQUEST   = 'hourlyMaxRequest';
    const DAILY_LIMIT          = 'dailyLimit';
    const DAILY_MAX_REQUEST    = 'dailyMaxRequest';
    const REQUEST_TYPE         = 'requestType';
    const PERIOD               = 'period';
    const LAST_DATE            = 'lastDate';
    const INTERVAL_DATE        = 'intervalDate';
    const HOURLY_DATE          = 'hourlyDate';
    const DAILY_DATE           = 'dailyDate';
    const BAN_LIMIT            = 'banLimit';
    const BAN_STATUS           = 'banStatus';
    const IS_ENABLED           = 'isEnabled';

    /**
     * Error message
     * 
     * @var string
     */
    public $error;

    /**
     * Request type
     * "call", "sms", "otp", "login"
     * 
     * @var string
     */
    public $requestType = '';

    /**
     * Cache object
     * 
     * @var object
     */
    public $cache = null;

    /**
     * Redis cache expiration time
     * 
     * @var integer
     */
    protected $expiration = 7200;

    /**
     * This period reduceLimit()
     * function update the date.
     * 
     * @var array
     */
    protected $period = array();

    /**
     * Is ban active
     * 
     * @var boolean
     */
    protected $isBanActive = false;

    /**
     * Redis cache keys.
     * 
     * @var array
     */
    protected $cacheKeys = array(
        self::RATE_LIMITER_CONFIG => self::RATE_LIMITER_CONFIG,
        self::RATE_LIMITER_REQUEST => self::RATE_LIMITER_REQUEST,
        self::RATE_LIMITER_REQUEST_DATA => self::RATE_LIMITER_REQUEST_DATA
    );

    /**
     * Constructor
     * 
     * @param object $c        container
     * @param object $listener listener
     * @param array  $params   parameters
     */
    public function __construct($c, $listener, $params = array())
    {
        $this->cache = $c->load('return service/cache');
        $this->listener = $listener;
        if (count($params['cacheKeys']) > 0) {
            $this->cacheKeys = $params['cacheKeys'];
        }
        $this->readConfig();
        $this->requestData = $this->cache->get($this->cacheKeys[self::RATE_LIMITER_REQUEST_DATA] . $this->listener->getChannel() . ':' . $this->listener->getID());
    }

    /**
     * Set request type
     * 
     * @param string $type type
     * 
     * @return void
     */
    public function setRequestType($type)
    {
        $this->requestType = $type;
    }

    /**
     * Get request type
     * 
     * @return void
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * Set identifier
     * 
     * @param int $id identifier
     * 
     * @return void
     */
    public function setID($id)
    {
        $this->listener->setID($id);
    }

    /**
     * Set error message.
     * 
     * @param string $error error message
     * 
     * @return void
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * Get error message.
     * 
     * @return void
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get period
     * 
     * @param string $period period name
     * 
     * @return boolean
     */
    public function getPeriod($period)
    {
        if ( ! isset($this->period[$period]['update']) OR $this->period[$period]['update'] == false) {
            return false;
        }
        return true;
    }

    /**
     * Reset period
     * 
     * @return void
     */
    protected function resetPeriod()
    {
        $this->period = array(
            'interval' => array(
                'date'   => '',
                'update' => false,
            ),
            'hourly' => array(
                'date'   => '',
                'update' => false,
            ),
            'daily' => array(
                'date'   => '',
                'update' => false,
            ),
        );
    }

    /**
     * Increase interval limit.,
     * 
     * @param int $intervalAmount interval amount limit
     * 
     * @return void
     */
    private function _increaseIntervalLimit($intervalAmount)
    {
        $this->listener->setIntervalLimit($this->configData[static::INTERVAL_LIMIT], $intervalAmount + 1);
    }

    /**
     * Increase daily limit.
     * 
     * @param int $hourlyAmount hourly amount limit
     * 
     * @return void
     */
    private function _increaseHourlyLimit($hourlyAmount)
    {
        $this->listener->setHourlyLimit($this->configData[static::HOURLY_LIMIT], $hourlyAmount + 1);
    }

    /**
     * Increase daily limit.
     * 
     * @param int $dailyAmount daily amount limit
     * 
     * @return void
     */
    private function _increaseDailyLimit($dailyAmount)
    {
        $this->listener->setDailyLimit($this->configData[static::DAILY_LIMIT], $dailyAmount + 1);
    }

    /**
     * Reduce interval limit.,
     * 
     * @param array $requestData request data
     * 
     * @return void
     */
    private function _reduceIntervalLimit($requestData)
    {
        if (strtotime(' - ' . $this->listener->getIntervalLimit() . ' second ') < $requestData[static::PERIOD][static::INTERVAL_DATE]) {
            return $this->listener->setIntervalLimit($this->configData[static::INTERVAL_LIMIT], $requestData[static::INTERVAL_MAX_REQUEST] - 1);
        }
        $this->period['interval'] = array('date' => time(), 'update' => true);
        $this->listener->setIntervalLimit($this->configData[static::INTERVAL_LIMIT], $this->configData[static::INTERVAL_MAX_REQUEST] - 1);
    }

    /**
     * Reduce daily limit.
     * 
     * @param array $requestData request data
     * 
     * @return void
     */
    private function _reduceHourlyLimit($requestData)
    {
        if (strtotime(' - ' . $this->listener->getHourlyLimit() . ' second ') < $requestData[static::PERIOD][static::HOURLY_DATE]) {
            return $this->listener->setHourlyLimit($this->configData[static::HOURLY_LIMIT], $requestData[static::HOURLY_MAX_REQUEST] - 1);
        }
        $this->period['hourly'] = array('date' => time(), 'update' => true);
        $this->listener->setHourlyLimit($this->configData[static::HOURLY_LIMIT], $this->configData[static::HOURLY_MAX_REQUEST] - 1);
    }

    /**
     * Reduce daily limit.
     * 
     * @param array $requestData request data
     * 
     * @return void
     */
    private function _reduceDailyLimit($requestData)
    {
        if (strtotime(' - ' . $this->listener->getHourlyLimit() . ' second ') < $requestData[static::PERIOD][static::DAILY_DATE]) {
            return $this->listener->setDailyLimit($this->configData[static::DAILY_LIMIT], $requestData[static::DAILY_MAX_REQUEST] - 1);
        }
        $this->period['daily'] = array('date' => time(), 'update' => true);
        $this->listener->setDailyLimit($this->configData[static::DAILY_LIMIT], $this->configData[static::DAILY_MAX_REQUEST] - 1);
    }

    /**
     * Increase limit
     * 
     * @return void
     */
    public function increaseLimit()
    {
        $data = $this->getRequestData();
        $this->_increaseIntervalLimit($data[static::INTERVAL_MAX_REQUEST]);
        $this->_increaseHourlyLimit($data[static::HOURLY_MAX_REQUEST]);
        $this->_increaseDailyLimit($data[static::DAILY_MAX_REQUEST]);
        $this->saveRequest();
    }

    /**
     * Reduce limit.
     * 
     * @return void
     */
    public function reduceLimit()
    {
        $data = $this->getRequestData();
        $this->_reduceIntervalLimit($data);
        $this->_reduceHourlyLimit($data);
        $this->_reduceDailyLimit($data);
        $this->saveRequest();
    }

    /**
     * Is allowed.
     * 
     * @return boolean
     */
    public function isAllowed()
    {
        $data = $this->getRequestData();
        /**
         * If disabled the service request,
         * always returns true.
         */
        if ($this->listener->isEnabled() == false) {
            return true;
        }
        if ($this->isBanned()) {
            $this->setError('User is banned.');
            return false;
        }
        /**
         * Daily Limit
         */
        if (strtotime('- ' . $this->listener->getDailyLimit() . ' second') >= $data[static::PERIOD][static::LAST_DATE]) {
            return true;
        }
        if ($data[static::DAILY_MAX_REQUEST] < 1) {
            $this->execBan('Daily');
            return false;
        }
        /**
         * Hourly limit
         */
        if (strtotime('- ' . $this->listener->getHourlyLimit() . ' second') >= $data[static::PERIOD][static::LAST_DATE]) {
            return true;
        }
        if ($data[static::HOURLY_MAX_REQUEST] < 1) {
            $this->execBan('Hourly');
            return false;
        }
        /**
         * Interval limit
         */
        if (strtotime('- ' . $this->listener->getIntervalLimit() . ' second') >= $data[static::PERIOD][static::LAST_DATE]) {
            return true;
        }
        if ($data[static::INTERVAL_MAX_REQUEST] < 1) {
            $this->execBan('Interval');
            return false;
        }
        return true;
    }

    /**
     * Exec Ban
     * 
     * @param string $type type
     * 
     * @return boolean false
     */
    protected function execBan($type)
    {
        if ($this->listener->getBanStatus() == true) {
            $this->addBan();
            $this->setError(ucfirst($type).' banned.');
            return;
        }
        $this->setError('Maximum connection limit reached for '. $type .' request.');
    }

    /**
     * Insert request.
     * 
     * @return void
     */
    protected function saveRequest()
    {
        
        $time = time();
        $period[static::PERIOD] = array(static::LAST_DATE => $time);

        /**
         * If request date not empty
         * we update to old request date.
         * 
         * @var mixed
         */
        if ($this->requestData == false) {
            $period[static::PERIOD][static::INTERVAL_DATE] = $time;
            $period[static::PERIOD][static::HOURLY_DATE]   = $time;
            $period[static::PERIOD][static::DAILY_DATE]    = $time;
        } else {
            /**
             * Single an example.
             * 
             * $this->period = array(
             *     'interval' => array(
             *         'date'   => 1400167967, // time()
             *         'update' => true,
             *     ),
             * );
             * 
             * If update key equal to true then we understand
             * that we have a database update.
             */
            $period[static::PERIOD][static::INTERVAL_DATE] = ($this->getPeriod('interval')) ? $time : $this->requestData[static::PERIOD][static::INTERVAL_DATE];
            $period[static::PERIOD][static::HOURLY_DATE]   = ($this->getPeriod('hourly')) ? $time : $this->requestData[static::PERIOD][static::HOURLY_DATE];
            $period[static::PERIOD][static::DAILY_DATE]    = ($this->getPeriod('daily')) ? $time : $this->requestData[static::PERIOD][static::DAILY_DATE];;
        }
        $data = array(
            static::INTERVAL_MAX_REQUEST => $this->listener->getIntervalMaxRequest(),
            static::HOURLY_MAX_REQUEST   => $this->listener->getHourlyMaxRequest(),
            static::DAILY_MAX_REQUEST    => $this->listener->getDailyMaxRequest(),
            static::TOTAL_REQUEST_COUNT  => $this->listener->getTotalRequest() + 1,
            static::REQUEST_TYPE         => $this->getRequestType(),
        );
        $data = array_merge($data, $period);
        $this->cache->set(
            $this->cacheKeys[self::RATE_LIMITER_REQUEST_DATA] . $this->listener->getChannel() . ':' . $this->listener->getID(),
            $data,
            static::CACHE_DATA_EXPIRATION
        );
        // $this->getRequestData(); // set new data
        $this->resetPeriod();    // period reset
        return $data;
    }

    /**
     * Save config cache
     * 
     * @return void
     */
    protected function saveConfig()
    {
        $data = array(
            static::INTERVAL_LIMIT       => $this->listener->getIntervalLimit(),
            static::INTERVAL_MAX_REQUEST => $this->listener->getIntervalMaxRequest(),
            static::HOURLY_LIMIT         => $this->listener->getHourlyLimit(),
            static::HOURLY_MAX_REQUEST   => $this->listener->getHourlyMaxRequest(),
            static::DAILY_LIMIT          => $this->listener->getDailyLimit(),
            static::DAILY_MAX_REQUEST    => $this->listener->getDailyMaxRequest(),
            static::BAN_LIMIT            => $this->listener->getBanLimit(),
            static::BAN_STATUS           => $this->listener->getBanStatus(),
            static::IS_ENABLED           => $this->listener->isEnabled()
        );
        $key = $this->cacheKeys[self::RATE_LIMITER_CONFIG] . $this->listener->getChannel() . ':' . $this->listener->getListener();
        $this->cache->set($key, $data, static::CACHE_CONFIG_EXPIRATION);
        return $data;
    }

    /**
     * Read config
     * 
     * @return If data empty return null
     */
    protected function readConfig()
    {
        $key    = $this->cacheKeys[self::RATE_LIMITER_CONFIG] . $this->listener->getChannel() . ':' . $this->listener->getListener();
        $result = $this->cache->get($key);
        if ($result == false) { // If not exist in the cache
            $result = $this->saveConfig();
        }
        /**
         * Config data
         * 
         * @var array
         */
        $this->configData = $result;
        $this->listener->resetLimits();
        $this->listener->setIntervalLimit($result[static::INTERVAL_LIMIT], $result[static::INTERVAL_MAX_REQUEST]);
        $this->listener->setHourlyLimit($result[static::HOURLY_LIMIT], $result[static::HOURLY_MAX_REQUEST], true);
        $this->listener->setDailyLimit($result[static::DAILY_LIMIT], $result[static::DAILY_MAX_REQUEST], true);
    }

    /**
     * Get request data
     * 
     * @return array
     */
    public function getRequestData()
    {
        if (empty($this->requestData)) {
            $this->requestData = $this->saveRequest();
        }
        $this->listener->resetLimits();
        $this->listener->setIntervalLimit($this->configData[static::INTERVAL_LIMIT], $this->requestData[static::INTERVAL_MAX_REQUEST]);
        $this->listener->setHourlyLimit($this->configData[static::HOURLY_LIMIT], $this->requestData[static::HOURLY_MAX_REQUEST], true);
        $this->listener->setDailyLimit($this->configData[static::DAILY_LIMIT], $this->requestData[static::DAILY_MAX_REQUEST], true);
        $this->listener->setBanStatus($this->configData[static::BAN_STATUS]);
        $this->listener->setEnable($this->configData[static::IS_ENABLED]);
        $this->listener->setTotalRequest($this->requestData[static::TOTAL_REQUEST_COUNT]);
        return $this->requestData;
    }

    /**
     * Add Ban
     * 
     * @return void
     */
    protected function addBan()
    {
        $this->cache->set($this->cacheKeys[self::RATE_LIMITER_REQUEST] . $this->listener->getChannel() . ':Banned:' . $this->listener->getID(), time(), $this->listener->banExpiration);
    }

    /**
     * Is banned
     * 
     * @return boolean
     */
    public function isBanned()
    {
        if ( ! $this->cache->get($this->cacheKeys[self::RATE_LIMITER_REQUEST] . $this->listener->getChannel() . ':Banned:' . $this->listener->getID())) {
            return false;
        }
        return true;
    }

    /**
     * Remove ban
     * 
     * @return void
     */
    public function removeBan()
    {
        $this->cache->delete($this->cacheKeys[self::RATE_LIMITER_REQUEST] . $this->listener->getChannel() . ':' . $this->listener->getID());
    }

    /**
     * Delete cache
     * 
     * @param string $key cache key
     * 
     * @return void
     */
    public function deleteCache($key)
    {
        $this->cache->delete($key);
    }
}


// END Limiter Class

/* End of file Limiter.php */
/* Location: .Obullo/Rate/Limiter.php */