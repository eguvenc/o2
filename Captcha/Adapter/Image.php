<?php

namespace Obullo\Captcha\Adapter;

use RuntimeException,
    Obullo\Captcha\Result,
    Obullo\Captcha\CaptchaService,
    Obullo\Captcha\AbstractAdapter;

/**
 * Captcha image class.
 * 
 * @category  Adapter
 * @package   Image
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/captcha
 */
Class Image extends AbstractAdapter
{
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
     * Noise color
     * 
     * @var string
     */
    public $noiseColor;

    /**
     * Text color
     * 
     * @var string
     */
    public $textColor;

    /**
     * Image name
     * 
     * @var string
     */
    public $imgName;

    /**
     * Image unique id
     * 
     * @var string
     */
    protected $imageId = '';

    /**
     * Image captcha drivers
     * 
     * @var array
     */
    protected $drivers = array(
        'cool',
        'secure'
    );

    /**
     * Wave Y axis
     * 
     * @var integer
     */
    protected $yPeriod = 12;

    /**
     * Wave Y amplitude
     * 
     * @var integer
     */
    protected $yAmplitude = 14;

    /**
     * Wave X axis
     * 
     * @var integer
     */
    protected $xPeriod = 11;

    /**
     * Wave Y amplitude
     * 
     * @var integer
     */
    protected $xAmplitude = 5;

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
     * @param object $c       container
     * @param object $captcha captcha
     */
    public function __construct($c, CaptchaService $captcha)
    {
        parent::__construct($c, $captcha);
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
     * Set the code generated for CAPTCHA.
     * 
     * @param string $code generated code.
     * 
     * @return string
     */
    protected  function setCode($code)
    {
        $this->code = $code;
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
     * Get the code generated for CAPTCHA.
     * 
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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
        if ( ! in_array(strtolower($driver), $this->drivers)) {
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
     * Generate image code
     * 
     * @return void
     */
    protected function generateCode()
    {
        $code  = '';
        $defaultPool = $this->config['characters']['default']['pool'];
        $possible    = $this->config['characters']['pools'][$defaultPool];

        for ($i = 0; $i < $this->config['characters']['length']; $i++) {
            $code .= mb_substr(
                $possible,
                mt_rand(0, mb_strlen($possible, $this->config['locale']['charset']) - 1), 1, $this->config['locale']['charset']
            );
        }
        $this->setCode($code);
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
     * Create image.
     * 
     * @return void
     */
    protected function imageCreate()
    {
        $randTextColor  = $this->config['text']['colors']['text'][array_rand($this->config['text']['colors']['text'])];
        $randNoiseColor = $this->config['text']['colors']['noise'][array_rand($this->config['text']['colors']['noise'])];
        $this->calculateWidth();

        // PHP.net recommends imagecreatetruecolor(), but it isn't always available
        if (function_exists('imagecreatetruecolor') AND $this->config['image']['truecolor']) {
            $this->image = imagecreatetruecolor($this->width, $this->config['image']['height']);
        } else {
            $this->image = imagecreate($this->width, $this->config['image']['height']) or die('Cannot initialize new GD image stream');
        }
        imagecolorallocate($this->image, 255, 255, 255);

        $explodeColor     = explode(',', $this->config['colors'][$randTextColor]);
        $this->textColor  = imagecolorallocate($this->image, $explodeColor['0'], $explodeColor['1'], $explodeColor['2']);
        $explodeColor     = explode(',', $this->config['colors'][$randNoiseColor]);
        $this->noiseColor = imagecolorallocate($this->image, $explodeColor['0'], $explodeColor['1'], $explodeColor['2']);
    }

    /**
     * Set wave for captcha image
     * 
     * @return void
     */
    protected function waveImage()
    {
        $xp = $this->scale * $this->xPeriod * rand(1, 3);   // X-axis wave generation
        $k  = rand(0, 10);

        for ($i = 0; $i < ($this->width * $this->scale); $i++) {
            imagecopy($this->image, $this->image, $i - 1, sin($k + $i / $xp) * ($this->scale * $this->xAmplitude), $i, 0, 1, $this->config['image']['height'] * $this->scale);
        }

        $k  = rand(0, 10);                                   // Y-axis wave generation
        $yp = $this->scale * $this->yPeriod * rand(1, 2);

        for ($i = 0; $i < ($this->config['image']['height'] * $this->scale); $i++) {
            imagecopy($this->image, $this->image, sin($k + $i / $yp) * ($this->scale * $this->yAmplitude), $i - 1, 0, $i, $this->width * $this->scale, 1);
        }
    }

    /**
     * Calculator width
     * 
     * @return void
     */
    protected function calculateWidth()
    {
        $this->width = ($this->config['font']['size'] * $this->config['characters']['length']) + 40;
    }

    /**
     * Image filled ellipse
     * 
     * @return void
     */
    protected function filledEllipse()
    {
        $fonts = $this->getFonts();

        if (sizeof($fonts) == 0) {
            throw new RuntimeException('Image CAPTCHA requires fonts.');
        }

        $randFont = array_rand($fonts);
        $fontPath = $this->defaultFontPath . $this->config['fonts'][$fonts[$randFont]];

        if (strpos($fonts[$randFont], '.ttf')) {
            $fontPath = $this->configFontPath . $this->config['fonts'][$fonts[$randFont]];
        }
        
        if ($this->config['driver'] != 'cool') {
            $wHvalue = $this->width / $this->config['image']['height'];
            $wHvalue = $this->config['image']['height'] * $wHvalue;
            for ($i = 0; $i < $wHvalue; $i++) {
                imagefilledellipse(
                    $this->image,
                    mt_rand(0, $this->width),
                    mt_rand(0, $this->config['image']['height']),
                    1,
                    1,
                    $this->noiseColor
                );
            }
        }
        $textbox = imagettfbbox($this->config['font']['size'], 0, $fontPath, $this->getCode()) or die('Error in imagettfbbox function');
        $x = ($this->width - $textbox[4]) / 2;
        $y = ($this->config['image']['height'] - $textbox[5]) / 2;

        // Generate an unique image id using the session id, an unique id and time.
        $this->setImageId($imageUniqId = md5($this->session->get('session_id') . uniqid(time())));

        $this->imgName  = $imageUniqId . '.' . $this->config['image']['type'];
        $this->imageUrl = $this->imgRawUrl . $this->imgName;

        imagettftext($this->image, $this->config['font']['size'], 0, $x, $y, $this->textColor, $fontPath, $this->getCode()) or die('Error in imagettftext function');
    }

    /**
     * Image line
     * 
     * @return void
     */
    protected function imageLine()
    {
        if ($this->config['driver'] != 'cool') {

            $wHvalue = $this->width / $this->config['image']['height'];
            $wHvalue = $wHvalue / 2;

            for ($i = 0; $i < $wHvalue; $i++) {
                imageline(
                    $this->image,
                    mt_rand(0, $this->width),
                    mt_rand(0, $this->config['image']['height']),
                    mt_rand(0, $this->width),
                    mt_rand(0, $this->config['image']['height']),
                    $this->noiseColor
                );
            }
        }
    }

    /**
     * Image generate
     * 
     * @return void
     */
    protected function imageGenerate()
    {
        header('Content-Type: image/png');
        imagepng($this->image);
        imagedestroy($this->image);
    }

    /**
     * Validation captcha code
     * 
     * @param string $code captcha word
     * 
     * @return Captcha\Result object
     */
    public function check($code)
    {
        if ($data = $this->session->get($this->config['input']['id'])) {
            return $this->validateCode($data, $code);
        }
        $this->result = array(  // Last failure.
            'code' => Result::FAILURE_CAPTCHA_NOT_FOUND,
            'message' => 'The captcha code not found.'
        );
        return $this->createResult();
    }

    /**
     * Validate captcha code
     * 
     * @param array  $data captcha session data
     * @param string $code captcha code
     * 
     * @return Captcha\Result object
     */
    protected function validateCode($data, $code)
    {
        if ($data['expiration'] < time()) { // Expiration time of captcha ( second )
            // Remove captcha data from session.
            $this->session->remove($this->config['input']['id']);

            $this->result = array(
                'code'    => Result::FAILURE_HAS_EXPIRED,
                'message' => 'The captcha code has expired.'
            );
            return $this->createResult();
        }

        if ($code == $data['code']) { // Is the code correct?
            // Remove captcha data from session.
            $this->session->remove($this->config['input']['id']);

            $this->result = array(
                'code'    => Result::SUCCESS,
                'message' => 'Captcha code has been entered successfully.'
            );
            return $this->createResult();
        }
        $this->result = array(
            'code'    => Result::FAILURE_INVALID_CODE,
            'message' => 'Invalid captcha code.'
        );
        return $this->createResult();
    }
}

// END Image Class
/* End of file Image.php

/* Location: .Obullo/Captcha/Adapter/Image.php */