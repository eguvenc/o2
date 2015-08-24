<?php

namespace Service;

use Obullo\Captcha\CaptchaManager;
use Obullo\Service\ServiceInterface;
use Obullo\Container\ContainerInterface;

class Recaptcha implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register(ContainerInterface $c)
    {
        $c['recaptcha'] = function () use ($c) {

            $parameters = [
                'class' => '\Obullo\Captcha\Provider\ReCaptcha'
            ];
            $manager = new CaptchaManager($c);
            $manager->setParameters($parameters);
            $captcha = $manager->getClass();
            $captcha->setLang('en');
            return $captcha;
        };
    }
}