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
        'messages' => array(),
    );

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
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->session = $c->load('session');
        $this->logger  = $c['logger'];
        
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
     * Validation set
     * 
     * @return void
     */
    abstract protected function validationSet();

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
     * Print javascript link
     * 
     * @return string
     */
    abstract public function printJs();

    /**
     * Create result.
     * 
     * @return CaptchaResult object
     */
    protected function createResult()
    {
        return new CaptchaResult(
            $this->result['code'],
            $this->result['messages']
        );
    }
}

// END AbstractAdapter Class
/* End of file AbstractAdapter.php

/* Location: .Obullo/Captcha/AbstractAdapter.php */