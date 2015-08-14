<?php

namespace Obullo\Captcha;

use RuntimeException;
use Obullo\Captcha\Result;
use Obullo\Captcha\CaptchaService;
use Obullo\Container\ContainerInterface;

/**
 * Captcha abstract class.
 * 
 * @category  Captcha
 * @package   AbstractAdapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/captcha
 */
abstract class AbstractAdapter
{
    /**
     * Result
     * 
     * @var array
     */
    public $result = array(
        'code' => '',
        'messages' => [],
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
     * @param object $c \Obullo\Container\ContainerInterface
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->session = $c['session'];
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
     * Print javascript link
     * 
     * @return string
     */
    abstract public function printJs();

    /**
     * Print captcha html element
     * 
     * @return string
     */
    abstract public function printHtml();

    /**
     * Check captcha code
     * 
     * @param string $code captcha code
     * 
     * @return boolean
     */
    abstract public function result($code);

    /**
     * Validation set
     * 
     * @return void
     */
    abstract protected function validationSet();

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