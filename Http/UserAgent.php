<?php

namespace Obullo\Http;

use Obullo\Container\ContainerInterface;

/**
 * Identifies the platform, browser, robot, or mobile devise of the browsing agent
 *
 * This file modeled after Codeigniter User Agent class 
 * 
 * @category  UserAgent
 * @package   Agent
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/agent
 */
class UserAgent
{
    /**
     * Current user-agent
     *
     * @var string
     */
    public $agent = null;

    /**
     * Flag for if the user-agent is a robot
     *
     * @var bool
     */
    public $isRobot = false;

    /**
     * Flag for if the user-agent is a mobile browser
     *
     * @var bool
     */
    public $isMobile = false;

    /**
     * Flag for if the user-agent belongs to a browser
     *
     * @var bool
     */
    public $isBrowser = false;

    /**
     * Languages accepted by the current user agent
     *
     * @var array
     */
    public $languages = array();

    /**
     * Character sets accepted by the current user agent
     *
     * @var array
     */
    public $charsets = array();

    /**
     * List of platforms to compare against current user agent
     *
     * @var array
     */
    public $platforms = array(
        'key' => '',
        'val' => ''
    );

    /**
     * List of browsers to compare against current user agent
     *
     * @var array
     */
    public $browsers = array(
        'key' => '',
        'val' => ''
    );

    /**
     * List of mobile browsers to compare against current user agent
     *
     * @var array
     */
    public $mobiles = array(
        'key' => '',
        'val' => ''
    );

    /**
     * List of robots to compare against current user agent
     *
     * @var array
     */
    public $robots = array(
        'key' => '',
        'val' => ''
    );

    /**
     * Current user-agent platform
     *
     * @var string
     */
    public $platform = array(
        'key' => '',
        'val' => ''
    );

    /**
     * Current user-agent browser
     *
     * @var string
     */
    public $browser = array(
        'key' => '',
        'val' => ''
    );

    /**
     * Current user-agent mobile data
     *
     * @var string
     */
    public $mobile = array(
        'key' => '',
        'val' => ''
    );

    /**
     * Current user-agent robot data
     *
     * @var string
     */
    public $robot = array(
        'key' => '',
        'val' => ''
    );

    /**
     * Current user-agent version
     *
     * @var string
     */
    public $version = '';

    /**
     * Constructor
     *
     * Sets the User Agent and runs the compilation routine
     * 
     * @param object $c container
     *
     * @return void
     */
    public function __construct(ContainerInterface $c)
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->agent = trim($_SERVER['HTTP_USER_AGENT']);
        }
        if (! is_null($this->agent)) {
            if ($this->loadAgentFile($c['config'])) {
                $this->compileData();
            }
        }
        $c['logger']->debug('Agent Class Initialized');
    }

    /**
     * Compile the User Agent Data
     *
     * @param object $config \Obullo\Config\Config
     * 
     * @return bool
     */
    protected function loadAgentFile($config)
    {
        $return = false;
        $userAgents = $config->load('agents');

        if (isset($userAgents['platforms'])) {
            $this->platforms = &$userAgents['platforms'];
            unset($userAgents['platforms']);
            $return = true;
        }

        if (isset($userAgents['browsers'])) {
            $this->browsers = &$userAgents['browsers'];
            unset($userAgents['browsers']);
            $return = true;
        }

        if (isset($userAgents['mobiles'])) {
            $this->mobiles = &$userAgents['mobiles'];
            unset($userAgents['mobiles']);
            $return = true;
        }

        if (isset($userAgents['robots'])) {
            $this->robots = &$userAgents['robots'];
            unset($userAgents['robots']);
            $return = true;
        }

        return $return;
    }

    /**
     * Compile the User Agent Data
     *
     * @return bool
     */
    protected function compileData()
    {
        foreach (array('setBrowser', 'setRobot', 'setMobile') as $function) {
            if ($this->$function() === true) {
                break;
            }
        }
        $this->setPlatform();
    }

    /**
     * Set the Platform
     *
     * @return mixed
     */
    protected function setPlatform()
    {
        $platform = ($this->isMobile()) ? 'mobile' : 'pc';

        if (is_array($this->platforms[$platform]) && count($this->platforms[$platform]) > 0) {
            foreach ($this->platforms[$platform] as $key => $val) {
                if (preg_match("|" . preg_quote($key) . "|i", $this->agent)) {
                    $this->platform['key'] = $key;
                    $this->platform['val'] = $val;
                    return true;
                }
            }
        }
        $this->platform['val'] = 'Unknown Platform';
    }

    /**
     * Set the Browser
     *
     * @return bool
     */
    protected function setBrowser()
    {
        if (is_array($this->browsers) && count($this->browsers) > 0) {
            foreach ($this->browsers as $key => $val) {
                if (preg_match("|" . preg_quote($key) . ".*?([0-9\.]+)|i", $this->agent, $match)) {
                    $this->isBrowser = true;
                    $this->version   = $match[1];
                    $this->browser['key'] = $key;
                    $this->browser['val'] = $val;
                    $this->setMobile();
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set the Robot
     *
     * @return bool
     */
    protected function setRobot()
    {
        if (is_array($this->robots) && count($this->robots) > 0) {
            foreach ($this->robots as $key => $val) {
                if (preg_match("|" . preg_quote($key) . "|i", $this->agent)) {
                    $this->isRobot = true;
                    $this->robot['key'] = $key;
                    $this->robot['val'] = $val;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set the Mobile Device
     *
     * @return bool
     */
    protected function setMobile()
    {
        if (is_array($this->mobiles) && count($this->mobiles) > 0) {
            foreach ($this->mobiles as $key => $val) {
                if (false !== (stripos($this->agent, $key))) {
                    $this->isMobile = true;
                    $this->mobile['key'] = $key;
                    $this->mobile['val'] = $val;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set the accepted languages
     *
     * @return void
     */
    protected function setLanguages()
    {
        if (empty($this->languages)
            && ! empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) 
            && is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'])
        ) {
            $this->languages = explode(',', preg_replace('/(;\s?q=[0-9\.]+)|\s/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE']))));
        }
        if (empty($this->languages)) {
            $this->languages = array('Undefined');
        }
    }

    /**
     * Set the accepted character sets
     *
     * @return void
     */
    protected function setCharsets()
    {
        if (empty($this->charsets) && ! empty($_SERVER['HTTP_ACCEPT_CHARSET'])
        ) {
            $this->charsets = explode(',', preg_replace('/(;\s?q=.+)|\s/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET']))));
        }
        if (empty($this->charsets)) {
            $this->charsets = array('Undefined');
        }
    }

    /**
     * Is Browser
     *
     * @return bool
     */
    public function isBrowser()
    {
        return $this->isBrowser;
    }

    /**
     * Is Robot
     *
     * @return bool
     */
    public function isRobot()
    {
        return $this->isRobot;
    }

    /**
     * Is Mobile
     *
     * @return bool
     */
    public function isMobile()
    {
        return $this->isMobile;
    }

    /**
     * Is this a referral from another site ?
     *
     * @return bool
     */
    public function isReferral()
    {
        return ( ! isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == '') ? false : true;
    }

    /**
     * Agent String
     *
     * @return string
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * Get Platform
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform['val'];
    }

    /**
     * Get Browser Name
     *
     * @return string
     */
    public function getBrowser()
    {
        return $this->browser['val'];
    }

    /**
     * Get the Browser Version
     *
     * @return string
     */
    public function getBrowserVersion()
    {
        return $this->version;
    }

    /**
     * Get The Robot Name
     *
     * @return string
     */
    public function getRobotName()
    {
        return $this->robot['val'];
    }

    /**
     * Get the Mobile Device
     *
     * @return string
     */
    public function getMobileDevice()
    {
        return $this->mobile['val'];
    }

    /**
     * Get the referrer
     *
     * @return bool
     */
    public function getReferrer()
    {
        return empty($_SERVER['HTTP_REFERER']) ? '' : trim($_SERVER['HTTP_REFERER']);
    }

    /**
     * Get the accepted languages
     *
     * @return array
     */
    public function getLanguages()
    {
        if (empty($this->languages)) {
            $this->setLanguages();
        }
        return $this->languages;
    }

    /**
     * Get the accepted Character Sets
     *
     * @return array
     */
    public function getCharsets()
    {
        if (empty($this->charsets)) {
            $this->setCharsets();
        }
        return $this->charsets;
    }

    /**
     * Test for a particular language
     * 
     * @param string $lang language
     *
     * @return bool
     */
    public function getAcceptLang($lang = 'en')
    {
        return (in_array(strtolower($lang), $this->getLanguages(), true)) ? true : false;
    }

    /**
     * Test for a particular character set
     * 
     * @param string $charset charset
     *
     * @return bool
     */
    public function getAcceptCharset($charset = 'utf-8')
    {
        return (in_array(strtolower($charset), $this->getCharsets(), true)) ? true : false;
    }

    /**
     * Get the configuration key of the provided method
     * 
     * @param string $name key name
     *
     * @return string
     */
    public function getConfigName($name = null)
    {
        $name = trim(strtolower($name));
        switch ($name) {
        case 'browser':
            return $this->browser['key'];
            break;
        case 'platform':
            return $this->platform['key'];
            break;
        case 'robot':
            return $this->robot['key'];
            break;
        case 'mobile':
            return $this->mobile['key'];
            break;
        }
    }

}

// END UserAgent.php File
/* End of file UserAgent.php

/* Location: .Obullo/Http/UserAgent.php */