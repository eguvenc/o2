<?php

namespace Obullo\Captcha;

use Obullo\Captcha\Captcha;

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
Class Debug extends Captcha
{
    /**
     * Constructor
     */
    public function __construct()
    {
        global $c;
        parent::__construct($c, $c['config']->load('captcha'));

        $this->clearImages();
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
                <img src="/assets'.$this->getImageUrl().'">
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

        $fonts    = $this->getFonts();
        $randFont = array_rand($fonts);
        $fontPath = $this->defaultFontPath . $this->config['fonts'][$fonts[$randFont]];

        if (strpos($fonts[$randFont], '.ttf')) {
            $fontPath = $this->configFontPath . $this->config['fonts'][$fonts[$randFont]];
        }

        $randTextColor  = $this->config['text']['colors']['text'][array_rand($this->config['text']['colors']['text'])];
        $randNoiseColor = $this->config['text']['colors']['noise'][array_rand($this->config['text']['colors']['noise'])];
        $this->width    = ($this->config['font']['size'] * $this->config['characters']['length']) + 40;
        // Create image
        // PHP.net recommends imagecreatetruecolor(), but it isn't always available
        if (function_exists('imagecreatetruecolor') AND $this->config['image']['truecolor']) {
            $this->image = imagecreatetruecolor($this->width, $this->config['image']['height']);
        } else {
            $this->image = imagecreate($this->width, $this->config['image']['height']) or die('Cannot initialize new GD image stream');
        }
        imagecolorallocate($this->image, 255, 255, 255);

        $explodeColor = explode(',', $this->config['colors'][$randTextColor]);
        $textColor    = imagecolorallocate($this->image, $explodeColor['0'], $explodeColor['1'], $explodeColor['2']);
        $explodeColor = explode(',', $this->config['colors'][$randNoiseColor]);
        $noiseColor   = imagecolorallocate($this->image, $explodeColor['0'], $explodeColor['1'], $explodeColor['2']);
        if ($this->config['driver'] != 'cool') {
            $wHvalue = $this->width / $this->config['image']['height'];
            $wHvalue = $this->config['image']['height'] * $wHvalue;
            for ($i = 0; $i < $wHvalue; $i++) {
                imagefilledellipse($this->image, mt_rand(0, $this->width), mt_rand(0, $this->config['image']['height']), 1, 1, $noiseColor);
            }
        }
        $textbox = imagettfbbox($this->config['font']['size'], 0, $fontPath, $this->code) or die('Error in imagettfbbox function');
        $x = ($this->width - $textbox[4]) / 2;
        $y = ($this->config['image']['height'] - $textbox[5]) / 2;

        // Generate an unique image id using the session id, an unique id and time.
        $this->setImageId($imageUniqId = md5($this->session->get('session_id') . uniqid(time())));

        $imgName        = $imageUniqId . '.' . $this->config['image']['type'];
        $this->imageUrl = $this->imgRawUrl . $imgName;

        imagettftext($this->image, $this->config['font']['size'], 0, $x, $y, $textColor, $fontPath, $this->code) or die('Error in imagettftext function');

        if ($this->config['image']['wave']) {
            $this->waveImage();
        }

        if ($this->config['driver'] != 'cool') {

            $wHvalue = $this->width / $this->config['image']['height'];
            $wHvalue = $wHvalue / 2;

            for ($i = 0; $i < $wHvalue; $i++) {
                imageline($this->image, mt_rand(0, $this->width), mt_rand(0, $this->config['image']['height']), mt_rand(0, $this->width), mt_rand(0, $this->config['image']['height']), $noiseColor);
            }
        }
        imagepng($this->image, $this->imgPath.$imgName);
        imagedestroy($this->image);

        $this->session->set(
            $this->config['input']['id'],
            array(
                'image_name' => $imageUniqId,
                'code'       => $this->code,
                'expiration' => time() + $this->config['image']['expiration']
            )
        );

        $this->init();
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