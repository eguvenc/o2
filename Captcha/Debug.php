<?php

namespace Obullo\Captcha;

/**
 * Captcha Quicktest Class
 *
 * @package       packages
 * @subpackage    captcha
 * @category      test
 * @link
 */
Class Debug
{

    /**
     * Do a quick test for each fonts.
     * 
     * @return array
     */
    public function font()
    {
        global $c;

        $images = '';
        $config = $c['config']->load('captcha');
        $this->captcha = $c->load('captcha');

        asort($this->captcha->fonts);

        foreach ($this->captcha->fonts as $key => $value) {
            $value = null;
            $this->captcha->setHeight('50');
            $this->captcha->setFontSize('25');
            $this->captcha->setChar(mb_strlen($this->captcha->char_pool['random'], $config['charset']));
            $this->captcha->setColor('cyan');
            $this->captcha->setNoiseColor('cyan');
            $this->captcha->setFont($key);
            $this->captcha->create();
            $images.= '<p>'.$key.'<img src="'.$this->captcha->getImageUrl().'"></p>';
        }

        return $images;
    }

    /**
     * Do a quick test for each variables.
     * 
     * @return string
     */
    public function variables()
    {   
        $output = '';

        $this->captcha->clear();
        $this->captcha->setDriver('secure');
        $this->captcha->create();

        $output.= '<p>setDriver: Secure </p> <img src="'.$this->captcha->getImageUrl().'">';

        $this->captcha->clear();
        $this->captcha->setDriver('cool"');
        $this->captcha->create();
        
        $output.= '<p>setDriver: Cool </p> <img src="'.$this->captcha->getImageUrl().'">';
        
        $this->captcha->clear();
        $this->captcha->setDriver('cool');
        $this->captcha->setPool('alpha');
        $this->captcha->create();

        $output.= '<p>setPool: alpha </p> <img src="'.$this->captcha->getImageUrl().'">';
       
        $this->captcha->clear();
        $this->captcha->setDriver('cool');
        $this->captcha->setPool('numbers');
        $this->captcha->create();

        $output.= '<p>setPool: numbers </p> <img src="'.$this->captcha->getImageUrl().'">';
       
        $this->captcha->clear();
        $this->captcha->setDriver('cool');
        $this->captcha->setPool('random');
        $this->captcha->create();

        $output.= '<p>setPool: random </p> <img src="'.$this->captcha->getImageUrl().'">';
       
        $this->captcha->clear();
        $this->captcha->setDriver('cool');
        $this->captcha->setChar(5);
        $this->captcha->create();

        $output.= '<p>setChar: 5 </p> <img src="'.$this->captcha->getImageUrl().'">';
         
        $this->captcha->clear();
        $this->captcha->setDriver('cool');
        $this->captcha->setFontSize(30);
        $this->captcha->setHeight(80);
        $this->captcha->create();

        $output.= '<p>setFontSize: 30 </p> <img src="'.$this->captcha->getImageUrl().'">';
       
        $this->captcha->clear();
        $this->captcha->setDriver('cool');
        $this->captcha->setFontSize(30);
        $this->captcha->setHeight(100);
        $this->captcha->create();

        $output.= '<p>setHeight: 80 </p> <img src="'.$this->captcha->getImageUrl().'">';
       
        $this->captcha->clear();
        $this->captcha->setDriver('cool');
        $this->captcha->setWave(false);
        $this->captcha->create();

        $output.= '<p>setWave: false </p> <img src="'.$this->captcha->getImageUrl().'">';

        $this->captcha->clear();
        $this->captcha->setDriver('cool');
        $this->captcha->setColor(array('red','black','blue'));
        $this->captcha->create();
        
        $output.= '<p>setColor: red-black-blue <p> <img src="'.$this->captcha->getImageUrl().'">';
        
        $this->captcha->clear();
        $this->captcha->setDriver('secure');
        $this->captcha->setNoiseColor(array('red','black','blue'));
        $this->captcha->create();

        $output.= '<p>setNoiseColor: red-black-blue </p> <img src="'.$this->captcha->getImageUrl().'">';
    
        $this->captcha->clear();
        $this->captcha->setDriver('cool');
        $this->captcha->setFont(array('AlphaSmoke','Bknuckss'));
        $this->captcha->create();
        
        $output.= '<p>setFont: AlphaSmoke,Bknuckss </p> <img src="'.$this->captcha->getImageUrl().'">';

        $this->captcha->clear();
        $this->captcha->setDriver('cool');
        $this->captcha->excludeFont(array('Bknuckss'));
        $this->captcha->create();

        $output.= '<p>excludeFont: Bknuckss </p> <img src="'.$this->captcha->getImageUrl().'">';
        return $output;
    }

}

// END Debug class

/* End of file Debug.php */
/* Location: .Obullo/Captcha/Debug.php */