<?php

namespace Obullo\Captcha;

use RuntimeException,
    Obullo\Captcha\Result,
    Obullo\Captcha\CaptchaService;

/**
 * Captcha abstract class.
 * 
 * @category  Captcha
 * @package   AbstractAdapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/captcha
 */
abstract Class AbstractAdapter
{
    /**
     * Result
     * 
     * @var array
     */
    public $result = array(
        'code' => '',
        'message' => '',
    );

    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Config data
     * 
     * @var array
     */
    protected $config = array();

    /**
     * Session instance
     * 
     * @var object
     */
    protected $session;

    /**
     * Logger instance
     * 
     * @var object
     */
    protected $logger;

    /**
     * Captcha Service
     * 
     * @var object
     */
    protected $captcha;

    /**
     * Constructor
     *
     * @param object $c       container
     * @param object $captcha captcha service
     */
    public function __construct($c, CaptchaService $captcha)
    {
        $this->c       = $c;
        $this->params  = $captcha->config;
        $this->config  = $captcha->config;
        $this->session = $this->c->load('session');
        $this->logger  = $this->c->load('service/logger');

        $this->init();

        $this->logger->debug('Captcha Class Initialized');
    }

    /**
     * Initialize
     * 
     * @return void
     */
    abstract public function init();

    /**
     * Generate code
     * 
     * @return void
     */
    abstract protected function generateCode();

    /**
     * Create captcha and save into captcha
     *
     * @return void
     */
    abstract public function create();

    /**
     * Check captcha code
     * 
     * @param string $code captcha code
     * 
     * @return boolean
     */
    abstract public function check($code);

    /**
     * Create result.
     * 
     * @return Result object
     */
    protected function createResult()
    {
        return new Result(
            $this->result['code'],
            $this->result['message']
        );
    }
}

// END AbstractAdapter Class
/* End of file AbstractAdapter.php

/* Location: .Obullo/Captcha/AbstractAdapter.php */