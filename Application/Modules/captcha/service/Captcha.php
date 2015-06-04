<?php

namespace Service;

use Obullo\Captcha\Adapter\Image;
use Obullo\Service\ServiceInterface;
use Obullo\Container\ContainerInterface;

class Captcha implements ServiceInterface
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
        $c['captcha'] = function () use ($c) {

            $captcha = new Image($c);            
            $captcha->setMod('secure');     // set to "cool" for no background
            $captcha->setPool('alpha');     // "random", "numbers"
            $captcha->setChar(5);
            $captcha->setFont(array('NightSkK','AlphaSmoke','Popsf'));
            $captcha->setFontSize(20);
            $captcha->setHeight(36);
            $captcha->setWave(false);
            $captcha->setColor(['red', 'black']);
            $captcha->setTrueColor(false);
            $captcha->setNoiseColor(['red']);
            return $captcha;
        };
    }
}

// END Captcha service

/* End of file Captcha.php */
/* Location: .app/classes/Service/Captcha.php */