<?php

namespace Service;

use Obullo\Service\ServiceInterface;
use Obullo\Container\ContainerInterface;
use Obullo\Captcha\Adapter\ReCaptcha as ReCaptchaClass;

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

            $captcha = new ReCaptchaClass($c);
            $captcha->setLang('en');
            return $captcha;
        };
    }
}