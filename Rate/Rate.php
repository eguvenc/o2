<?php

namespace Obullo\Rate;

use RuntimeException,
    Obullo\Container\Container;

/**
 * Rate Limiter
 * 
 * @category  Security
 * @package   Limiter
 * @author    Ali İhsan Çağlayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs
 */
Class Rate
{
    const CONFIG_EXPIRATION = 604800; // 1 Week (second).
    const DATA_EXPIRATION   = 604800; // 1 Week (second).

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
     * This period reduceLimit() function update the date.
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
     * Request data
     * 
     * @var array
     */
    protected $data;

    /**
     * Constructor
     * 
     * @param object $c          container
     * @param object $identifier limiter name
     * @param array  $params     config
     */
    public function __construct(Container $c, $identifier = 'ip', $params = array())
    {
        $this->c = $c;
        $this->cache = $this->c->load('service/cache');
        $this->logger = $this->c->load('logger');

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
        $this->settings = $this->config->read();  // Get configuration
        $this->data = $this->cache->get('Rate:Data:'.$channel.':'.$identifier); // Read request data
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
     * @param array $data request data
     * 
     * @return void
     */
    protected function reduceIntervalLimit($data)
    {
        if (strtotime(' - ' . $this->config->getIntervalLimit() . ' second ') < $data['period']['intervalDate']) {
            return $this->config->setIntervalLimit(
                $this->settings['limit']['interval']['amount'], 
                $data['intervalMaxRequest'] - 1
            );
        }
        $this->period['interval'] = array('date' => time(), 'update' => true);
        $this->config->setIntervalLimit(
            $this->settings['limit']['interval']['amount'], 
            $this->settings['limit']['interval']['maxRequest'] - 1
        );
    }

    /**
     * Reduce daily limit.
     * 
     * @param array $data request data
     * 
     * @return void
     */
    protected function reduceHourlyLimit($data)
    {
        if (strtotime(' - ' . $this->config->getHourlyLimit() . ' second ') < $data['period']['hourlyDate']) {
            return $this->config->setHourlyLimit(
                $this->settings['limit']['hourly']['amount'], 
                $data['hourlyMaxRequest'] - 1
            );
        }
        $this->period['hourly'] = array('date' => time(), 'update' => true);
        $this->config->setHourlyLimit(
            $this->settings['limit']['hourly']['amount'], 
            $this->settings['limit']['hourly']['maxRequest'] - 1
        );
    }

    /**
     * Reduce daily limit.
     * 
     * @param array $data request data
     * 
     * @return void
     */
    protected function reduceDailyLimit($data)
    {
        if (strtotime(' - ' . $this->config->getHourlyLimit() . ' second ') < $data['period']['dailyDate']) {
            return $this->config->setDailyLimit(
                $this->settings['limit']['daily']['amount'],
                $data['dailyMaxRequest'] - 1
            );
        }
        $this->period['daily'] = array('date' => time(), 'update' => true);
        $this->config->setDailyLimit(
            $this->settings['limit']['daily']['amount'],
            $this->settings['limit']['daily']['maxRequest'] - 1
        );
    }

    /**
     * Increase limit
     * 
     * @return void
     */
    public function increase()
    {
        $data = $this->getRequestData();
        $this->increaseIntervalLimit($data['intervalMaxRequest']);
        $this->increaseHourlyLimit($data['hourlyMaxRequest']);
        $this->increaseDailyLimit($data['dailyMaxRequest']);
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
        if ($this->config->isEnabled() == false) {
            return true;
        }
        if ($this->isBanned()) {
            $this->setError('User is banned.');
            $this->logger->notice(
                'User is banned.', 
                array('channel' => $this->getChannel(), 'identifier' => $this->getIdentifier())
            );
            return false;
        }
        $data = $this->getRequestData();
        if ( ! $this->checkDailyLimit($data)) {   // Check daily limit is reached
            return false;
        }
        if ( ! $this->checkHourlyLimit($data)) {
            return false;
        }
        if ( ! $this->checkIntervalLimit($data)) {
            return false;
        }
        return true;
    }

    /**
     * Check daily limit is reached
     * 
     * @param array $data request data
     * 
     * @return boolean
     */
    public function checkDailyLimit($data)
    {
        if (strtotime('- ' .$this->config->getDailyLimit().' second') >= $data['period']['lastDate']) {
            return true;
        }
        if ($data['dailyMaxRequest'] < 1) {
            $this->execBan('Daily');
            return false;
        }
    }

    /**
     * Check hourly limit is reached,
     * 
     * @param array $data request data
     * 
     * @return boolean
     */
    public function checkHourlyLimit($data)
    {
        if (strtotime('- '.$this->config->getHourlyLimit().' second') >= $data['period']['lastDate']) {
            return true;
        }
        if ($data['hourlyMaxRequest'] < 1) {
            $this->execBan('Hourly');
            return false;
        }
    }

    /**
     * Check interval limit is reached
     *
     * @param array $data request data
     * 
     * @return boolean
     */
    public function checkIntervalLimit($data)
    {
        if (strtotime('- '.$this->config->getIntervalLimit(). ' second') >= $data['period']['lastDate']) {
            return true;
        }
        if ($data['intervalMaxRequest'] < 1) {
            $this->execBan('Interval');
            return false;
        }
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
            $this->deleteRequestData();  // Remove request data after ban
            $this->logger->notice(
                ucfirst($type).' ban.', 
                array('channel' => $this->getChannel(), 'identifier' => $this->getIdentifier())
            );
            return;
        }
        $error = 'Maximum connection limit reached for '. $type .' period.';
        $this->setError($error);
        $this->logger->notice(
            $error, 
            array('channel' => $this->getChannel(), 'identifier' => $this->getIdentifier())
        );
    }

    /**
     * Removes request data of valid user
     * 
     * @return boolean
     */
    public function deleteRequestData()
    {
        return $this->cache->delete('Rate:Data:'.$this->getChannel().':'.$this->getIdentifier());
    }

    /**
     * Insert request.
     * 
     * @return void
     */
    protected function saveRequest()
    {
        $time = time();
        $period['period'] = array('lastDate' => $time);

        if ($this->data == false) {  // If request date not empty we update to old request date.
            $period['period']['intervalDate'] = $time;
            $period['period']['hourlyDate'] = $time;
            $period['period']['dailyDate'] = $time;
        } else {
            $period['period']['intervalDate'] = ($this->getPeriod('interval')) ? $time : $this->data['period']['intervalDate'];
            $period['period']['hourlyDate'] = ($this->getPeriod('hourly')) ? $time : $this->data['period']['hourlyDate'];
            $period['period']['dailyDate'] = ($this->getPeriod('daily')) ? $time : $this->data['period']['dailyDate'];
        }
        $data = array(
            'intervalMaxRequest' => $this->config->getIntervalMaxRequest(),
            'hourlyMaxRequest' => $this->config->getHourlyMaxRequest(),
            'dailyMaxRequest' => $this->config->getDailyMaxRequest(),
            'totalRequest'  => $this->config->getTotalRequest() + 1,
            'label' => $this->getLabel(),
        );
        $data = array_merge($data, $period);
        $this->cache->set(
            'Rate:Data:'.$this->getChannel().':'.$this->getIdentifier(),
            $data,
            $this->settings['ban']['expiration']
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
        if (empty($this->data)) {
            $this->data = $this->saveRequest();
        }
        $this->config->resetLimits();
        $this->config->setIntervalLimit(
            $this->settings['limit']['interval']['amount'], 
            $this->data['intervalMaxRequest']
        );
        $this->config->setHourlyLimit(
            $this->settings['limit']['hourly']['amount'], 
            $this->data['hourlyMaxRequest'],
            true
        );
        $this->config->setDailyLimit(
            $this->settings['limit']['daily']['amount'],
            $this->data['dailyMaxRequest'],
            true
        );
        $this->config->setBanStatus($this->settings['ban']['status']);
        $this->config->setEnable($this->settings['enabled']);
        $this->config->setTotalRequest($this->data['totalRequest']);

        return $this->data;
    }

    /**
     * Add Ban
     * 
     * @return void
     */
    public function addBan()
    {
        $this->cache->set(
            'Rate:Banlist:'.$this->getChannel().':'.$this->getIdentifier(), 
            time(), 
            $this->config->getBanExpiration()
        );
    }

    /**
     * Is banned
     * 
     * @return boolean
     */
    public function isBanned()
    {
        $banlist = $this->cache->get('Rate:Banlist:'. $this->getChannel().':'.$this->getIdentifier());
        if ( ! $banlist) {
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
        $this->cache->delete('Rate:Banlist:'.$this->getChannel().':'.$this->getIdentifier());
    }

}

// END Rate Class

/* End of file Rate.php */
/* Location: .Obullo/Rate/Rate.php */