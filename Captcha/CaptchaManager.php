<?php

namespace Obullo\Captcha;

use Obullo\Captcha\Provider\Image;
use Obullo\Captcha\Provider\ReCaptcha;
use Obullo\Container\ContainerInterface;

/**
 * CaptchaManager Class
 * 
 * @category  Manager
 * @package   CaptchaManager
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
class CaptchaManager
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Service Parameters
     * 
     * @var array
     */
    protected $params;

    /**
     * Create classes
     * 
     * @param object $c container
     * 
     * @return object
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Set parameters
     * 
     * @param array $params parameters
     *
     * @return void
     */
    public function setParameters(array $params)
    {
        $exp = explode("\\", $params['class']);
        $this->provider = strtolower(end($exp));
        $this->params = array_merge(
            $params,
            $this->c['config']->load('captcha/'.$this->provider)
        );
    }

    /**
     * Returns to selected queue handler object
     * 
     * @return object
     */
    public function getClass()
    {
        switch ($this->provider) {
        case 'image':
            return new Image(
                $this->c,
                $this->c['uri'],
                $this->c['request'],
                $this->c['session'],
                $this->c['translator'],
                $this->c['logger'],
                $this->params
            );
            break;
        case 'recaptcha':
            return new ReCaptcha(
                $this->c,
                $this->c['uri'],
                $this->c['request'],
                $this->c['translator'],
                $this->c['logger'],
                $this->params
            );
            break;
        }
    }
}