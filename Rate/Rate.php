<?php

namespace Obullo\Rate;

use RuntimeException;

/**
 * Rate Limiter
 * 
 * @category  Security
 * @package   Limiter
 * @author    Ali İhsan Çağlayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Rate
{
    const CACHE_CONFIG_EXPIRATION = 604800; // 1 Week (second).
    const CACHE_DATA_EXPIRATION   = 604800; // 1 Week (second).

    // Cache main constants
    const RATE_LIMITER_DATA = 'Rate_Limiter_Data';
    const RATE_LIMITER_CONFIG = 'Rate_Limiter_Config';
    const RATE_LIMITER_BANNED_USERS = 'Rate_Limiter_Banned_Users';

    // Cache parent constants
    const TOTAL_REQUEST_COUNT  = 'totalRequest';
    const INTERVAL_LIMIT       = 'intervalLimit';
    const INTERVAL_MAX_REQUEST = 'intervalMaxRequest';
    const HOURLY_LIMIT         = 'hourlyLimit';
    const HOURLY_MAX_REQUEST   = 'hourlyMaxRequest';
    const DAILY_LIMIT          = 'dailyLimit';
    const DAILY_MAX_REQUEST    = 'dailyMaxRequest';
    const LABEL                = 'label';
    const PERIOD               = 'period';
    const LAST_DATE            = 'lastDate';
    const INTERVAL_DATE        = 'intervalDate';
    const HOURLY_DATE          = 'hourlyDate';
    const DAILY_DATE           = 'dailyDate';

    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Error message
     * 
     * @var string
     */
    protected $error;

    /**
     * Request name "call", "sms", "otp", "login"
     * 
     * @var string
     */
    protected $label = 'none';

    /**
     * Cache object
     * 
     * @var object
     */
    protected $cache = null;

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
     * Configurations from cache
     * 
     * @var array
     */
    protected $settings = array();

    /**
     * Identifier of user
     * 
     * @var string
     */
    protected $identifier = null;

    /**
     * Channel name of the request
     * 
     * @var string
     */
    protected $channel = null;

    /**
     * Logger service
     *
     * @var object
     */
    protected $logger;

    /**
     * Constructor
     * 
     * @param object $c          container
     * @param object $identifier limiter name
     * @param array  $params     config
     */
    public function __construct($c, $identifier = 'ip', $params = array())
    {
        $this->c = $c;
        $this->cache = $c->load('return service/cache');
        $this->logger = $c->load('service/logger');

        $this->config = new Config($c);
        $this->config->identifier($identifier);
        $this->config->init($params[$identifier]);
    }

    /**
     * Read and run rate limiter configuration
     * 
     * @return void
     */
    public function run()
    {
        $channel = $this->getChannel();
        $identifier = $this->getIdentifier();

        if (empty($channel) OR empty($identifier)) {
            throw new RuntimeException("Identifier or channel can't be empty.");
        }
        $this->config->channel($channel);
        /**
         * Read the configuration of current channel
         */
        $this->settings = $this->config->read();

        /**
         * Read request data of current identifier
         */
        $this->requestData = $this->cache->get(static::RATE_LIMITER_DATA .':'. $channel . ':' . $identifier);
    }

    /**
     * Set request label
     * 
     * @param string $name request name
     * 
     * @return void
     */
    public function label($name)
    {
        $this->label = $name;
    }

    /**
     * Get request label
     * 
     * @return void
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set identifier
     * 
     * @param mix $identifier identifier
     * 
     * @return void
     */
    public function identifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Get unique identifier (ip, username, mobile phone)
     * 
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set channel
     * 
     * @param string $channel channel
     * 
     * @return void
     */
    public function channel($channel)
    {
        $this->channel = (string)$channel;
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
    protected function increaseIntervalLimit($intervalAmount)
    {
        $this->config->setIntervalLimit($this->settings['limit']['interval']['amount'], $intervalAmount + 1);
    }

    /**
     * Increase daily limit.
     * 
     * @param int $hourlyAmount hourly amount limit
     * 
     * @return void
     */
    protected function increaseHourlyLimit($hourlyAmount)
    {
        $this->config->setHourlyLimit($this->settings['limit']['hourly']['amount'], $hourlyAmount + 1);
    }

    /**
     * Increase daily limit.
     * 
     * @param int $dailyAmount daily amount limit
     * 
     * @return void
     */
    protected function increaseDailyLimit($dailyAmount)
    {
        $this->config->setDailyLimit($this->settings['limit']['daily']['amount'], $dailyAmount + 1);
    }

    /**
     * Reduce interval limit.,
     * 
     * @param array $requestData request data
     * 
     * @return void
     */
    protected function reduceIntervalLimit($requestData)
    {
        if (strtotime(' - ' . $this->config->getIntervalLimit() . ' second ') < $requestData[static::PERIOD][static::INTERVAL_DATE]) {
            return $this->config->setIntervalLimit($this->settings['limit']['interval']['amount'], $requestData[static::INTERVAL_MAX_REQUEST] - 1);
        }
        $this->period['interval'] = array('date' => time(), 'update' => true);
        $this->config->setIntervalLimit($this->settings['limit']['interval']['amount'], $this->settings['limit']['interval']['maxRequest'] - 1);
    }

    /**
     * Reduce daily limit.
     * 
     * @param array $requestData request data
     * 
     * @return void
     */
    protected function reduceHourlyLimit($requestData)
    {
        if (strtotime(' - ' . $this->config->getHourlyLimit() . ' second ') < $requestData[static::PERIOD][static::HOURLY_DATE]) {
            return $this->config->setHourlyLimit($this->settings['limit']['hourly']['amount'], $requestData[static::HOURLY_MAX_REQUEST] - 1);
        }
        $this->period['hourly'] = array('date' => time(), 'update' => true);
        $this->config->setHourlyLimit($this->settings['limit']['hourly']['amount'], $this->settings['limit']['hourly']['maxRequest'] - 1);
    }

    /**
     * Reduce daily limit.
     * 
     * @param array $requestData request data
     * 
     * @return void
     */
    protected function reduceDailyLimit($requestData)
    {
        if (strtotime(' - ' . $this->config->getHourlyLimit() . ' second ') < $requestData[static::PERIOD][static::DAILY_DATE]) {
            return $this->config->setDailyLimit($this->settings['limit']['daily']['amount'], $requestData[static::DAILY_MAX_REQUEST] - 1);
        }
        $this->period['daily'] = array('date' => time(), 'update' => true);
        $this->config->setDailyLimit($this->settings['limit']['daily']['amount'], $this->settings['limit']['daily']['maxRequest'] - 1);
    }

    /**
     * Increase limit
     * 
     * @return void
     */
    public function increase()
    {
        $data = $this->getRequestData();
        $this->increaseIntervalLimit($data[static::INTERVAL_MAX_REQUEST]);
        $this->increaseHourlyLimit($data[static::HOURLY_MAX_REQUEST]);
        $this->increaseDailyLimit($data[static::DAILY_MAX_REQUEST]);
        $this->saveRequest();
    }

    /**
     * Reduce limit.
     * 
     * @return void
     */
    public function reduce()
    {
        $data = $this->getRequestData();
        $this->reduceIntervalLimit($data);
        $this->reduceHourlyLimit($data);
        $this->reduceDailyLimit($data);
        $this->saveRequest();
    }

    /**
     * Is allowed.
     * 
     * @return boolean
     */
    public function isAllowed()
    {
        $this->run();  // Read configuration from cache

        $data = $this->getRequestData();
        /**
         * If the service request disabled it always returns to true.
         */
        if ($this->config->isEnabled() == false) {
            return true;
        }
        if ($this->isBanned()) {
            $this->setError('User is banned.');
            $this->logger->notice('User is banned.', array('channel' => $this->getChannel(), 'identifier' => $this->getIdentifier()));
            return false;
        }
        /**
         * Daily Limit
         */
        if (strtotime('- ' . $this->config->getDailyLimit() . ' second') >= $data[static::PERIOD][static::LAST_DATE]) {
            return true;
        }
        if ($data[static::DAILY_MAX_REQUEST] < 1) {
            $this->execBan('Daily');
            return false;
        }
        /**
         * Hourly limit
         */
        if (strtotime('- ' . $this->config->getHourlyLimit() . ' second') >= $data[static::PERIOD][static::LAST_DATE]) {
            return true;
        }
        if ($data[static::HOURLY_MAX_REQUEST] < 1) {
            $this->execBan('Hourly');
            return false;
        }
        /**
         * Interval limit
         */
        if (strtotime('- ' . $this->config->getIntervalLimit() . ' second') >= $data[static::PERIOD][static::LAST_DATE]) {
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
        if ($this->config->getBanStatus() == true) {
            $this->addBan();
            $this->logger->notice(ucfirst($type).' ban.', array('channel' => $this->getChannel(), 'identifier' => $this->getIdentifier()));
            return;
        }
        $error = 'Maximum connection limit reached for '. $type .' period.';
        $this->setError($error);
        $this->logger->notice($error, array('channel' => $this->getChannel(), 'identifier' => $this->getIdentifier()));
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
         * If request date not empty we update to old request date.
         */
        if ($this->requestData == false) {

            $period[static::PERIOD][static::INTERVAL_DATE] = $time;
            $period[static::PERIOD][static::HOURLY_DATE]   = $time;
            $period[static::PERIOD][static::DAILY_DATE]    = $time;

        } else {
            /**
             * An example.
             * 
             * $this->period = array(
             *     'interval' => array(
             *         'date'   => 1400167967, // time()
             *         'update' => true,
             *     ),
             * );
             * 
             * If update key equal to "true" then we understand that we need to update time.
             */
            $period[static::PERIOD][static::INTERVAL_DATE] = ($this->getPeriod('interval')) ? $time : $this->requestData[static::PERIOD][static::INTERVAL_DATE];
            $period[static::PERIOD][static::HOURLY_DATE]   = ($this->getPeriod('hourly')) ? $time : $this->requestData[static::PERIOD][static::HOURLY_DATE];
            $period[static::PERIOD][static::DAILY_DATE]    = ($this->getPeriod('daily')) ? $time : $this->requestData[static::PERIOD][static::DAILY_DATE];;
        }
        $data = array(
            static::INTERVAL_MAX_REQUEST => $this->config->getIntervalMaxRequest(),
            static::HOURLY_MAX_REQUEST   => $this->config->getHourlyMaxRequest(),
            static::DAILY_MAX_REQUEST    => $this->config->getDailyMaxRequest(),
            static::TOTAL_REQUEST_COUNT  => $this->config->getTotalRequest() + 1,
            static::LABEL                => $this->getLabel(),
        );
        $data = array_merge($data, $period);
        $this->cache->set(
            static::RATE_LIMITER_DATA .':'. $this->getChannel() . ':' . $this->getIdentifier(),
            $data,
            static::CACHE_DATA_EXPIRATION
        );
        $this->resetPeriod();    // Period reset

        return $data;
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
        $this->config->resetLimits();
        $this->config->setIntervalLimit($this->settings['limit']['interval']['amount'], $this->requestData[static::INTERVAL_MAX_REQUEST]);
        $this->config->setHourlyLimit($this->settings['limit']['hourly']['amount'], $this->requestData[static::HOURLY_MAX_REQUEST], true);
        $this->config->setDailyLimit($this->settings['limit']['daily']['amount'], $this->requestData[static::DAILY_MAX_REQUEST], true);
        $this->config->setBanStatus($this->settings['ban']['status']);
        $this->config->setEnable($this->settings['enabled']);
        $this->config->setTotalRequest($this->requestData[static::TOTAL_REQUEST_COUNT]);

        return $this->requestData;
    }

    /**
     * Add Ban
     * 
     * @return void
     */
    public function addBan()
    {
        $this->cache->set(static::RATE_LIMITER_BANNED_USERS .':'. $this->getChannel() . ':' . $this->getIdentifier(), time(), $this->config->getBanExpiration());
    }

    /**
     * Is banned
     * 
     * @return boolean
     */
    public function isBanned()
    {
        if ( ! $this->cache->get(static::RATE_LIMITER_BANNED_USERS.':'. $this->getChannel() . ':' . $this->getIdentifier())) {
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
        $this->cache->delete(static::RATE_LIMITER_BANNED_USERS .':'. $this->getChannel() . ':' . $this->getIdentifier());
    }

}

// END Rate Class

/* End of file Rate.php */
/* Location: .Obullo/Rate/Rate.php */