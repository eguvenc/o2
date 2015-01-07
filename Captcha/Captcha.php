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
Class Captcha
{
    public $driver;                  // Driver type
    public $captchaId;               // captcha_id 
    public $colors;                  // Defined system colors
    public $defaultTextColor;        // Captcha text color
    public $defaultNoiseColor;       // Background noise property 
    public $waveImage;              // Font wave switch (bool)
    public $defaultFonts;           // Actual keys of the fonts
    public $fonts;                   // Actual fonts
    public $debugFlag = 'random';    // Font debug flag
    public $imgUrl;                 // URL for accessing images
    public $setPool;                // Pool
    public $charPools;              // Letters & numbers pool
    public $imagePath;               // Image dir
    public $imageType = 'png';       // Image suffix (including dot)
    public $width;                   // Image width
    public $height;                  // Image height
    public $font_size;               // Font size
    public $char;                    // Number of lines on image
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
     * @param array  $config parameters
     */
    public function __construct($c, $config = array())
    {
        $this->c = $c;
        $this->captcha = (count($config) > 0) ? $config : $c['config']->load('captcha');
        $this->session = $c->load('session');
        $this->logger = $c->load('service/logger');

        $this->init();
        $this->imgPath = ROOT . str_replace('/', DS, trim($this->captcha['img_path'], '/')) . DS;  // replace with DS
        $this->imgUrl = $c['uri']->getBaseUrl($this->captcha['img_path'] . DS); // add Directory Seperator ( DS )
        $this->userFontPath = ROOT . $this->captcha['user_font_path'] . DS;
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
        $this->driver            = $this->captcha['driver'];
        $this->captchaId         = $this->captcha['captcha_id'];
        $this->colors            = $this->captcha['colors'];
        $this->defaultTextColor  = $this->captcha['defaultTextColor'];
        $this->defaultNoiseColor = $this->captcha['defaultNoiseColor'];
        $this->defaultFonts      = array_keys($this->captcha['fonts']);
        $this->fonts             = $this->captcha['fonts'];
        $this->expiration        = $this->captcha['expiration'];
        $this->char              = $this->captcha['char'];
        $this->height            = $this->captcha['height'];
        $this->fontSize          = $this->captcha['font_size'];
        $this->setPool           = $this->captcha['set_pool'];
        $this->waveImage         = $this->captcha['wave_image'];
        $this->charPool          = $this->captcha['char_pool'];
        $this->imageType         = $this->captcha['image_type'];
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
    protected function setDefaults($variable, $defaultVariable, $values)
    {
        $array = array();
        if (is_string($values)) {
            $values = array($values);
        }
        foreach ($values as $val) {
            if (array_key_exists($val, $this->$variable)) {
                $array[$val] = $val;
            }
        }
        if ( ! empty($array)) {
            $this->{$defaultVariable} = $array;
        }
        unset($array);
    }

    /**
     * Set capthca id
     * 
     * @param string $captchaId captcha id
     * 
     * @return void
     */
    public function setCaptchaId($captchaId)
    {
        $this->captchaId = $captchaId;
    }

    /**
     * Set background noise color
     * 
     * @param mixed $values color
     * 
     * @return object
     */
    public function setNoiseColor($values = '')
    {
        if (empty($values)) {
            return $this;
        }
        $this->setDefaults('colors', 'defaultNoiseColor', $values);
        return $this;
    }

    /**
     * Set text color
     * 
     * @param mixed $values color
     * 
     * @return object
     */
    public function setColor($values)
    {
        if (empty($values)) {
            return $this;
        }
        $this->setDefaults('colors', 'defaultTextColor', $values);
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
        if (empty($values)) {
            return $this;
        }
        $this->setDefaults('fonts', 'default_fonts', $values);
        return $this;
    }

    /**
     * Set text font size
     * 
     * @param int $fontSize font size
     * 
     * @return object
     */
    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;
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
        $this->height = $height;
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
        if (array_key_exists($pool, $this->charPool)) {
            $this->setPool = $pool;
        }
    }

    /**
     * Set character
     * 
     * @param int $char character
     * 
     * @return object
     */
    public function setChar($char)
    {
        $this->char = $char;
    }

    /**
     * Set wave true or false
     * 
     * @param option $wave enable wave for font
     * 
     * @return object
     */
    public function setWave($wave)
    {
        $this->waveImage = $wave;
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
            $possible = $this->charPool[$this->setPool];
            $this->code = '';
            $i = 0;
            while ($i < $this->char) {
                $this->code.= mb_substr($possible, mt_rand(0, mb_strlen($possible, $this->captcha['charset']) - 1), 1, $this->captcha['charset']);
                $i++;
            }
        } elseif ($this->debugFlag == 'all') {
            $this->code = $this->charPool['random'];
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

        $keyRand  = array_rand($this->defaultFonts);
        $fontPath = $this->defaultFontPath . $this->fonts[$this->defaultFonts[$keyRand]];

        if (strpos($this->defaultFonts[$keyRand], '.ttf')) {
            $fontPath = $this->userFontPath . $this->fonts[$this->defaultFonts[$keyRand]];
        }
        $keyRand = array_rand($this->defaultTextColor);
        $defaultTextColor = $this->defaultTextColor[$keyRand];
        $keyRand = array_rand($this->defaultNoiseColor);
        $defaultNoiseColor = $this->defaultNoiseColor[$keyRand];
        $this->width = (($this->height / $this->fontSize) + $this->char) * 25;

        $this->image = imagecreate($this->width, $this->height) or die('Cannot initialize new GD image stream');
        imagecolorallocate($this->image, 255, 255, 255);

        $colorExplode = explode(',', $this->colors[$defaultTextColor]);
        $textColor = imagecolorallocate($this->image, $colorExplode['0'], $colorExplode['1'], $colorExplode['2']);
        $colorExplode = explode(',', $this->colors[$defaultNoiseColor]);
        $noiseColor = imagecolorallocate($this->image, $colorExplode['0'], $colorExplode['1'], $colorExplode['2']);

        if ($this->driver == 'secure') {  // Create Noises
            $this->createBackgroundNoises($noiseColor);
        }
        $textbox = imagettfbbox($this->fontSize, 0, $fontPath, $this->code) or die('Error in imagettfbbox function');
        $x = ($this->width - $textbox[4]) / 2;
        $y = ($this->height - $textbox[5]) / 2;

        $this->sessionKey = md5($this->session->get('session_id') . uniqid(time()));

        $imgName = $this->sessionKey . '.' . $this->imageType;
        $this->imageUrl = $this->imgUrl . $imgName;

        imagettftext($this->image, $this->fontSize, 0, $x, $y, $textColor, $fontPath, $this->code) or die('Error in imagettftext function');

        if ($this->waveImage) {
            $this->waveImage();
        }
        if ($this->driver == 'secure') {  // Create Lines
            $this->createBackgroundLines($noiseColor);
        }
        header('Content-Type: image/png');
        imagepng($this->image);
        imagedestroy($this->image);
        
        $this->session->set($this->captchaId, array('image_name' => $this->sessionKey, 'code' => $this->code));
    }
    
    /**
     * Create random noises for more strong images
     * 
     * @param string $color noise color
     * 
     * @return void
     */
    protected function createBackgroundNoises($color)
    {
        $whRate = $this->width / $this->height;
        $whRate = $this->height * $whRate;
        for ($i = 0; $i < $whRate; $i++) {
            imagefilledellipse($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), 1, 1, $color);
        }
    }

    /**
     * Create random lines for more strong images
     * 
     * @param string $color line color
     * 
     * @return void
     */
    protected function createBackgroundLines($color)
    {
        $whRate = $this->width / $this->height;
        $whRate = $whRate / 2;
        for ($i = 0; $i < $whRate; $i++) {
            imageline($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $color);
        }
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
        if ($this->session->get($this->captchaId)) {
            $captcha_value = $this->session->get($this->captchaId);
            if ($code == $captcha_value['code']) {
                $this->session->remove($this->captchaId);
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
        $this->defaultFonts = array_diff($this->defaultFonts, $values);
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

// END Captcha Class
/* End of file Captcha.php

/* Location: .Obullo/Captcha/Captcha.php */