<?php

namespace Service;

use Obullo\Container\Container;
use Obullo\Captcha\Adapter\ReCaptcha as ReCaptchaClass;
use Obullo\Service\ServiceInterface;

class Recaptcha implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register(Container $c)
    {
        $c['recaptcha'] = function () use ($c) {

            $captcha = new ReCaptchaClass($c);
            $captcha->setLang('en');
            return $captcha;
        };
    }
}

// END Recaptcha service

/* End of file Recaptcha.php */
/* Location: .app/classes/Service/Recaptcha.php */