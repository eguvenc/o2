<?php

namespace Obullo\Captcha;

use Obullo\Captcha\Adapter\Image;

/**
 * Captcha Debug Class
 * 
 * @category  Captcha
 * @package   Debug
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/captcha
 */
Class Debug extends Image
{
    /**
     * Constructor
     */
    public function __construct()
    {
        global $c;
        parent::__construct($c);

        $this->clearImages(); // Delete all old images from temp folder.
    }

    /**
     * Do a quick test for each fonts.
     * 
     * @return array
     */
    public function font()
    {
        $images = '';
        $fonts  = $this->getFonts();
        asort($fonts);

        foreach ($fonts as $val) {

            $this->setDriver($driver = $this->drivers[array_rand($this->drivers)]);
            $this->setHeight('150');
            $this->setPool($pool = array_rand($this->config['characters']['pools']));
            $this->setFontSize('48');
            $this->setChar('10');
            $this->setWave($wave = array_rand(array(true, false)));
            $this->setColor('cyan');
            $this->setNoiseColor('cyan');
            $this->setFont($val);
            $this->create();
            
            $images .= 
            '<p><pre>'.
                print_r(
                    array(
                        'Font'   => $val,
                        'Driver' => $driver,
                        'Pool'   => $pool,
                        'Wave'   => $wave
                    ),
                    true
                ).'</pre>
                <img src="/app'.$this->getImageUrl().'">
            </p>';
        }
        return $images;
    }

    /**
     * Create image captcha ans save into
     * captcha
     *
     * @return void
     */
    public function create()
    {
        $this->generateCode();  // generate captcha code
        $fonts = $this->getFonts();

        if (sizeof($fonts) == 0) {
            throw new RuntimeException('Image CAPTCHA requires fonts.');
        }
        $this->imageCreate();
        $this->filledEllipse();

        if ($this->config['image']['wave']) {
            $this->waveImage();
        }
        $this->imageLine();
        $this->imageGenerate(); // The last function for image.
        
        $this->session->set(
            $this->config['input']['id'],
            array(
                'image_name' => $this->getImageId(),
                'code'       => $this->getCode(),
                'expiration' => time() + $this->config['image']['expiration']
            )
        );
        $this->init(); // Variables are reset.
    }

    /**
     * Image generate
     * 
     * @return void
     */
    protected function imageGenerate()
    {
        imagepng($this->image, $this->imgPath.$this->imgName);
        imagedestroy($this->image);
    }

    /**
     * Clear images
     * 
     * @return void
     */
    public function clearImages()
    {
        array_map('unlink', glob($this->imgPath .'*')); // First, we need to delete all the images from the captcha folder.
    }

}

// END Debug class

/* End of file Debug.php */
/* Location: .Obullo/Captcha/Debug.php */