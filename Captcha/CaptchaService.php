<?php

namespace Obullo\Captcha;

/**
 * Captcha security class.
 * 
 * @category  Security
 * @package   Captcha
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/captcha
 */
Class CaptchaService
{
    public $driver;                  // Driver type
    public $captcha_id;              // captcha_id 
    public $colors;                  // Defined system colors
    public $default_text_color;      // Captcha text color
    public $default_noise_color;     // Background noise property 
    public $wave_image;              // Font wave switch (bool)
    public $default_fonts;           // Actual keys of the fonts
    public $fonts;                   // Actual fonts
    public $debugFlag = 'random';    // Font debug flag
    public $img_url;                 // URL for accessing images
    public $set_pool;                // Pool
    public $char_pools;              // Letters & numbers pool
    public $img_path;                // Image dir
    public $image_type = 'png';      // Image suffix (including dot)
    public $width;                   // Image width
    public $height;                  // Image height
    public $font_size;               // Font size
    public $char;                    // Number of lines on image
    public $del_rand = 10;           // Random delete number frequency
    public $expiration;              // How long to keep generated images
    public $sessionKey;              // Random session key for saving captcha code.
    public $imageUrl;                // Captcha image display url with base url

    protected $Yperiod    = 12;      // Wave Y axis
    protected $Yamplitude = 14;      // Wave Y amplitude
    protected $Xperiod    = 11;      // Wave X axis
    protected $Xamplitude = 5;       // Wave Y amplitude
    protected $scale      = 2;       // Wave default scale
    protected $image;                // Gd image content
    protected $code;                 // Generated image code
    protected $c;                    // Container

    /**
     * Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct($c, $params = array())
    {
        $this->c = $c;
        $this->session = $this->c->load('session');
        $this->logger  = $this->c->load('service/logger');
        $this->config  = $params;

        // $this->init();
        $this->imgPath         = ROOT . str_replace('/', DS, trim($this->config['image']['path'], '/')) . DS;  // replace with DS
        $this->imgUrl          = $c['uri']->getBaseUrl($this->config['img_path'] . DS); // add Directory Seperator ( DS )
        $this->fontPath        = ROOT . $this->config['user_font_path'] . DS;
        $this->defaultFontPath = OBULLO . 'Captcha' . DS . 'fonts' . DS;

        $this->logger->debug('Captcha Class Initialized');
    }

    /**
     * Initialize to Default Settings 
     * 
     * @return void
     */
    protected function init()
    {
        // $this->driver              = $this->config['driver'];
        // $this->captcha_id          = $this->config['captcha_id'];
        // $this->colors              = $this->config['colors'];
        // $this->default_text_color  = $this->config['default_text_color'];
        // $this->default_noise_color = $this->config['default_noise_color'];
        // $this->default_fonts       = array_keys($this->config['fonts']);
        // $this->fonts               = $this->config['fonts'];
        // $this->expiration          = $this->config['expiration'];
        // $this->char                = $this->config['char'];
        // $this->height              = $this->config['height'];
        // $this->font_size           = $this->config['font_size'];
        // $this->set_pool            = $this->config['set_pool'];
        // $this->wave_image          = $this->config['wave_image'];
        // $this->char_pool           = $this->config['char_pool'];
        // $this->image_type          = $this->config['image_type'];
    }

    /**
     * Set driver type
     * 
     * @param string $driver string
     * 
     * @return object
     */
    public function setDriver($driver = 'cool')
    {
        if ($driver == 'secure' OR $driver == 'cool') {
            $this->driver = $driver;
        }
        return $this;
    }

    /**
     * Set default variables
     * 
     * @param string $variable        variable name
     * @param string $defaultVariable default variable name
     * @param mixed  $values          string or array
     *
     * @return void
     */
    // protected function setDefaults($variable, $defaultVariable, $values)
    // {
    //     $array = array();
    //     if (is_string($values)) {
    //         $values = array($values);
    //     }
    //     foreach ($values as $val) {
    //         if (array_key_exists($val, $this->$variable)) {
    //             $array[$val] = $val;
    //         }
    //     }
    //     if ( ! empty($array)) {
    //         $this->{$defaultVariable} = $array;
    //     }
    //     unset($array);
    // }

    /**
     * Set capthca id
     * 
     * @param string $captchaId captcha id
     * 
     * @return void
     */
    public function setCaptchaId($captchaId)
    {
        $this->config['input']['id'] = $captchaId;
        return $this;
    }

    /**
     * Set background noise color
     * 
     * @param mixed $color color
     * 
     * @return object
     */
    public function setNoiseColor($color)
    {
        $this->config['text']['colors']['noise'] = $color;
        return $this;
    }

    /**
     * Set text color
     * 
     * @param mixed $color color
     * 
     * @return object
     */
    public function setColor($color)
    {
        $this->config['text']['colors']['text'] = $color;
        return $this;
    }

    /**
     * Set font type
     * 
     * @param mixed $values color
     * 
     * @return object
     */
    public function setFont($values)
    {
        $this->config['fonts'] = $font;
        return $this;
    }

    /**
     * Set text font size
     * 
     * @param int $size font size
     * 
     * @return object
     */
    public function setFontSize($size)
    {
        $this->config['font']['size'] = $size;
        return $this;
    }

    /**
     * Set image height
     * 
     * @param int $height font height
     * 
     * @return object
     */
    public function setHeight($height)
    {
        $this->config['image']['height'] = $height;
        return $this;
    }

    /**
     * Set pool
     * 
     * @param string $pool character pool
     * 
     * @return object
     */
    public function setPool($pool)
    {
        if (isset($this->config['characters']['pools'][$pool])) {
            $this->config['characters']['default']['pool'] = $pool;
        }
        return $this;
    }

    /**
     * Set character length
     * 
     * @param int $length character length
     * 
     * @return object
     */
    public function setChar($length)
    {
        $this->config['characters']['length'] = $length;
        return $this;
    }

    /**
     * Set wave TRUE or FALSE
     * 
     * @param boolean $wave enable wave for font
     * 
     * @return object
     */
    public function setWave($wave)
    {
        $this->config['image']['wave'] = (boolean)$wave;
        return $this;
    }

    /**
     * Clear variables
     * 
     * @return void
     */
    public function clear()
    {
        $this->init();
        return $this;
    }

    /**
     * Generate image code
     * 
     * @return void
     */
    protected function generateCode()
    {
        if ($this->debugFlag == 'random') {
            $possible = $this->char_pool[$this->set_pool];
            $this->code = '';
            $i = 0;
            while ($i < $this->char) {
                $this->code.= mb_substr($possible, mt_rand(0, mb_strlen($possible, $this->config['charset']) - 1), 1, $this->config['charset']);
                $i++;
            }
        } elseif ($this->debugFlag == 'all') {
            $this->code = $this->char_pool['random'];
        }
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

        $key_rand  = array_rand($this->default_fonts);
        $font_path = $this->default_font_path . $this->fonts[$this->default_fonts[$key_rand]];

        if (strpos($this->default_fonts[$key_rand], '.ttf')) {
            $font_path = $this->user_font_path . $this->fonts[$this->default_fonts[$key_rand]];
        }
        $key_rand = array_rand($this->default_text_color);
        $default_text_color = $this->default_text_color[$key_rand];
        $key_rand = array_rand($this->default_noise_color);
        $default_noise_color = $this->default_noise_color[$key_rand];
        $this->width = (($this->height / $this->font_size) + $this->char) * 25;

        $this->image = imagecreate($this->width, $this->height) or die('Cannot initialize new GD image stream');
        imagecolorallocate($this->image, 255, 255, 255);

        $color_explode = explode(',', $this->colors[$default_text_color]);
        $text_color = imagecolorallocate($this->image, $color_explode['0'], $color_explode['1'], $color_explode['2']);
        $color_explode = explode(',', $this->colors[$default_noise_color]);
        $noise_color = imagecolorallocate($this->image, $color_explode['0'], $color_explode['1'], $color_explode['2']);

        if ($this->driver != 'cool') {
            $w_h_value = $this->width / $this->height;
            $w_h_value = $this->height * $w_h_value;
            for ($i = 0; $i < $w_h_value; $i++) {
                imagefilledellipse($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), 1, 1, $noise_color);
            }
        }
        $textbox = imagettfbbox($this->font_size, 0, $font_path, $this->code) or die('Error in imagettfbbox function');
        $x = ($this->width - $textbox[4]) / 2;
        $y = ($this->height - $textbox[5]) / 2;

        $this->sessionKey = md5($this->session->get('session_id') . uniqid(time()));

        $imgName = $this->sessionKey . '.' . $this->image_type;
        $this->imageUrl = $this->img_url . $imgName;

        imagettftext($this->image, $this->font_size, 0, $x, $y, $text_color, $font_path, $this->code) or die('Error in imagettftext function');

        if ($this->wave_image) {
            $this->waveImage();
        }
        if ($this->driver != 'cool') {
            $w_h_value = $this->width / $this->height;
            $w_h_value = $w_h_value / 2;
            for ($i = 0; $i < $w_h_value; $i++) {
                imageline($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $noise_color);
            }
        }
        header('Content-Type: image/png');
        imagepng($this->image);
        imagedestroy($this->image);
        
        $this->session->set($this->captcha_id, array('image_name' => $this->sessionKey, 'code' => $this->code));
    }

    /**
     * Get captcha image url
     * 
     * @return string image asset url
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Get captcha Image UniqId
     * 
     * @return string 
     */
    public function getImageId()
    {
        return $this->sessionKey;
    }

    /**
     * Set wave for captcha image
     * 
     * @return void
     */
    protected function waveImage()
    {
        $xp = $this->scale * $this->Xperiod * rand(1, 3); // X-axis wave generation
        $k = rand(0, 10);
        for ($i = 0; $i < ($this->width * $this->scale); $i++) {
            imagecopy($this->image, $this->image, $i - 1, sin($k + $i / $xp) * ($this->scale * $this->Xamplitude), $i, 0, 1, $this->height * $this->scale);
        }

        $k = rand(0, 10);              // Y-axis wave generation
        $yp = $this->scale * $this->Yperiod * rand(1, 2);
        for ($i = 0; $i < ($this->height * $this->scale); $i++) {
            imagecopy($this->image, $this->image, sin($k + $i / $yp) * ($this->scale * $this->Yamplitude), $i - 1, 0, $i, $this->width * $this->scale, 1);
        }
    }

    /**
     * Validate captcha code
     * 
     * @param string $code captcha word
     * 
     * @return boolean
     */
    public function check($code)
    {
        if ($this->session->get($this->captcha_id)) {
            $captcha_value = $this->session->get($this->captcha_id);
            if ($code == $captcha_value['code']) {
                $this->session->remove($this->captcha_id);
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * Exclude fonts you don't want
     * 
     * @param mixed $values fonts
     * 
     * @return object
     */
    public function excludeFont($values)
    {
        if ( ! is_array($values)) {
            $values = array($values);
        }
        $this->default_fonts = array_diff($this->default_fonts, $values);
        return $this;
    }

    /**
     * Do test for all fonts
     * 
     * @return string Html output
     */
    public function fontTest()
    {
        $debug = new CaptchaDebug;
        return $debug->fontTest();
    }

    /**
     * Do test all variables
     * 
     * @return string Html output
     */
    public function varTest()
    {
        $debug = new CaptchaDebug;
        return $debug->variableTest();
    }

}

// END CaptchaService Class
/* End of file CaptchaService.php

/* Location: .Obullo/Captcha/CaptchaService.php */