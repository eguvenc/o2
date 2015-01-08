<?php

namespace Obullo\Captcha;

use RuntimeException,
    Obullo\Captcha\Debug;

/**
 * Captcha security class.
 * 
 * @category  Captcha
 * @package   Captcha
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/captcha
 */
Class Captcha
{
    /**
     * Successful process.
     */
    const SUCCESS = 1;

    /**
     * Has been expired the captcha.
     */
    const FAILURE_HAS_EXPIRED = -1;

    /**
     * Invalid captcha code.
     */
    const FAILURE_INVALID_CODE = -2;

    /**
     * Captcha data not found.
     */
    const FAILURE_CAPTCHA_NOT_FOUND = -3;

    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Config data
     * 
     * @var array
     */
    public $config = array();

    /**
     * Session instance
     * 
     * @var object
     */
    public $session;

    /**
     * Logger instance
     * 
     * @var object
     */
    public $logger;

    /**
     * Actual fonts
     * 
     * @var array
     */
    public $fonts;

    /**
     * Driver type
     * 
     * @var string
     */
    public $driver;

    /**
     * Image dir
     * 
     * @var string
     */
    public $imgPath;

    /**
     * Captcha image display
     * url with base url
     * 
     * @var string
     */
    public $imageUrl;

    /**
     * Image raw url
     * 
     * @var string
     */
    public $imgRawUrl;

    /**
     * Image width
     * 
     * @var integer
     */
    public $width;

    /**
     * Font path
     * 
     * @var string
     */
    public $configFontPath;

    /**
     * Default font path
     * 
     * @var string
     */
    public $defaultFontPath;

    /**
     * Image unique id
     * 
     * @var string
     */
    protected $imageId = '';

    /**
     * Drivers
     * 
     * @var array
     */
    protected $drivers = array('cool', 'secure');

    /**
     * Wave Y axis
     * 
     * @var integer
     */
    protected $Yperiod = 12;

    /**
     * Wave Y amplitude
     * 
     * @var integer
     */
    protected $Yamplitude = 14;

    /**
     * Wave X axis
     * 
     * @var integer
     */
    protected $Xperiod = 11;

    /**
     * Wave Y amplitude
     * 
     * @var integer
     */
    protected $Xamplitude = 5;

    /**
     * Wave default scale
     * 
     * @var integer
     */
    protected $scale = 2;

    /**
     * Gd image content
     * 
     * @var string
     */
    protected $image;

    /**
     * Generated image code
     * 
     * @var string
     */
    protected $code;

    /**
     * Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct($c, $params = array())
    {
        $this->c       = $c;
        $this->params  = $params;
        $this->config  = $params;
        $this->session = $this->c->load('session');
        $this->logger  = $this->c->load('service/logger');

        $this->init();

        $this->logger->debug('Captcha Class Initialized');
    }

    /**
     * Initialize
     * 
     * @return void
     */
    public function init()
    {
        $this->config          = $this->params;
        $this->fonts           = array_keys($this->config['fonts']);
        $this->imgPath         = ASSETS . str_replace('/', DS, trim($this->config['image']['path'], '/')) . DS;  // replace with DS
        $this->imgRawUrl       = $this->c['uri']->getBaseUrl($this->config['image']['path'] . DS); // add Directory Seperator ( DS )
        $this->configFontPath  = ROOT . $this->config['font']['path'] . DS;
        $this->defaultFontPath = OBULLO . 'Captcha' . DS . 'fonts' . DS;
    }

    /**
     * Set driver type
     * 
     * Types: "secure" or "cool"
     * 
     * @param string $driver string
     * 
     * @return object
     */
    public function setDriver($driver)
    {
        if ( ! $this->isAllowedDriver($driver)) {
            throw new RuntimeException(
                sprintf(
                    'You can not use an unsupported driver. It must be chosen from the following drivers "%s".',
                    implode(',', $this->drivers)
                )
            );
        }
        $this->config['driver'] = $driver;
        return $this;
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
        $this->config['input']['id'] = $captchaId;
        return $this;
    }

    /**
     * Set background noise color
     * 
     * @param mixed $color color
     * 
     * @return object
     * @throws RuntimeException If you set unsupported color then throw exception.
     */
    public function setNoiseColor($color)
    {
        if (($color = $this->isValidColors($color)) === true) {
            $this->config['text']['colors']['noise'] = $color;
        }
        return $this;
    }

    /**
     * Set text color
     * 
     * @param array $color color
     * 
     * @return object
     * @throws RuntimeException If you set unsupported color then throw exception.
     */
    public function setColor($color)
    {
        if (($color = $this->isValidColors($color)) === true) {
            $this->config['text']['colors']['text'] = $color;
        }
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
        $this->config['font']['size'] = (int)$size;
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
        $this->config['image']['height'] = (int)$height;
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
        $this->config['characters']['length'] = (int)$length;
        return $this;
    }

    /**
     * Set wave 
     * 
     * @param boolean $wave enable wave for font
     * 
     * @return object
     */
    public function setWave($wave)
    {
        $this->config['image']['wave'] = (bool)$wave;
        return $this;
    }

    /**
     * Set font
     * 
     * @param mixed $font font name
     * 
     * @return object
     */
    public function setFont($font)
    {
        if ( ! is_array($font)) {
            $str  = str_replace('.ttf', '', $font); // Remove the .ttf extension.
            $font = array($str => $str);
        }
        $this->fonts = $font;
        return $this;
    }

    /**
     * Append font
     * 
     * @param string $font font name
     * 
     * @return object
     */
    public function appendFont($font)
    {
        $this->fonts[] = str_replace('.ttf', '', $font); // Remove the .ttf extension.

        return $this;
    }

    /**
     * Get fonts
     * 
     * @return array
     */
    public function getFonts()
    {
        return $this->fonts;
    }

    /**
     * Is permitted driver
     * 
     * @param string $driver driver
     * 
     * @return boolean
     */
    public function isAllowedDriver($driver)
    {
        if ( ! in_array($driver, $this->drivers)) {
            return false;
        }
        return true;
    }

    /**
     * Colors validation
     * 
     * @param mix $colors colors
     * 
     * @return If supported colors returns array otherwise get the exception.
     */
    public function isValidColors($colors)
    {
        if ( ! is_array($colors)) {
            $colors = array($colors);
        }

        foreach ($colors as $val) {
            if ( ! isset($this->config['colors'][$val])) {
                $invalidColors[] = $val;
            }
        }

        if (isset($invalidColors)) {
            throw new RuntimeException(
                sprintf(
                    'You can not use an unsupported "%s" color(s). It must be chosen from the following colors "%s".',
                    implode(',', $invalidColors),
                    implode(',', array_keys($this->config['colors']))
                )
            );
        }
        return $colors;
    }

    /**
     * Set image unique id
     * 
     * @param string $uniqId unique id
     * 
     * @return void
     */
    protected function setImageId($uniqId)
    {
        $this->imageId = $uniqId;
    }

    /**
     * Generate image code
     * 
     * @return void
     */
    protected function generateCode()
    {
        $this->code  = '';
        $defaultPool = $this->config['characters']['default']['pool'];
        $possible    = $this->config['characters']['pools'][$defaultPool];

        for ($i = 0; $i < $this->config['characters']['length']; $i++) {
            $this->code .= mb_substr(
                $possible,
                mt_rand(0, mb_strlen($possible, $this->config['locale']['charset']) - 1), 1, $this->config['locale']['charset']
            );
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
        header('Content-Type: image/png');
        imagepng($this->image);
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
        return $this->imageId;
    }

    /**
     * Set wave for captcha image
     * 
     * @return void
     */
    protected function waveImage()
    {
        $xp = $this->scale * $this->Xperiod * rand(1, 3);   // X-axis wave generation
        $k  = rand(0, 10);

        for ($i = 0; $i < ($this->width * $this->scale); $i++) {
            imagecopy($this->image, $this->image, $i - 1, sin($k + $i / $xp) * ($this->scale * $this->Xamplitude), $i, 0, 1, $this->config['image']['height'] * $this->scale);
        }

        $k  = rand(0, 10);                                   // Y-axis wave generation
        $yp = $this->scale * $this->Yperiod * rand(1, 2);

        for ($i = 0; $i < ($this->config['image']['height'] * $this->scale); $i++) {
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
        if ($data = $this->session->get($this->config['input']['id'])) {

            if ($data['expiration'] < time()) {

                $result = array(
                    'code' => static::FAILURE_HAS_EXPIRED,
                    'message' => 'The captcha code has expired.'
                );
                $this->setResult($result);

                $this->session->remove($this->config['input']['id']);
                return false;
            }

            if ($code == $data['code']) { // Is the code correct?

                $result = array(
                    'code' => static::SUCCESS,
                    'message' => 'Captcha code has been entered successfully.'
                );
                $this->setResult($result);

                $this->session->remove($this->config['input']['id']);
                return true;
            }

            $result = array(
                'code' => static::FAILURE_INVALID_CODE,
                'message' => 'Invalid captcha code.'
            );
            $this->setResult($result);
            return false;
        }

        $result = array(
            'code' => static::FAILURE_CAPTCHA_NOT_FOUND,
            'message' => 'The captcha code not found.'
        );
        $this->setResult($result);
        return false;
    }

    /**
     * Set result.
     * 
     * @param array $result result
     * 
     * @return void
     */
    protected function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * Get result
     * 
     * @return array
     */
    public function result()
    {
        return $this->result;
    }

    /**
     * Exclude font you don't want
     * 
     * @param mixed $font font
     * 
     * @return object
     */
    public function excludeFont($font)
    {
        if ( ! is_array($font)) {
            $font = array($font);
        }
        $this->setFont(array_diff($this->getFonts(), $font));

        return $this;
    }
}

// END Captcha Class
/* End of file Captcha.php

/* Location: .Obullo/Captcha/Captcha.php */