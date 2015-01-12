<?php

namespace Obullo\Captcha\Adapter;

use Obullo\Captcha\CaptchaResult,
    Obullo\Captcha\AbstractAdapter,
    Obullo\Captcha\AdapterInterface;

/**
 * o2 Captcha - ReCaptcha
 * 
 * The new reCAPTCHA is here. A significant number of your users can now attest they are human without having to solve a CAPTCHA 
 * Insteadwith just a single click they’ll confirm they are not a robot. We’re calling it the No CAPTCHA reCAPTCHA experience.
 * You can follow label to "@see" for more details.
 *
 * @category  Captcha
 * @package   NoCaptcha
 * @author    Ali Ihsan CAGLAYAN <ihsancaglayan@gmail.com>
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/captcha
 * @see       https://www.google.com/recaptcha/intro/index.html
 */
Class ReCaptcha extends AbstractAdapter implements AdapterInterface
{
    /**
     * Api data
     */
    const CLIENT_API = 'https://www.google.com/recaptcha/api.js';
    const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Error codes constants
     */
    const FAILURE_MISSING_INPUT_SECRET   = 'missing-input-secret';
    const FAILURE_INVALID_INPUT_SECRET   = 'invalid-input-secret';
    const FAILURE_MISSING_INPUT_RESPONSE = 'missing-input-response';
    const FAILURE_INVALID_INPUT_RESPONSE = 'invalid-input-response';

    /**
     * The user's IP address. (optional)
     * 
     * @var string
     */
    protected $userIp = '';

    /**
     * Current language
     * 
     * @var string
     * @see https://developers.google.com/recaptcha/docs/language
     */
    protected $lang = '';

    /**
     * Error codes
     * 
     * @var array
     * @see https://developers.google.com/recaptcha/docs/verify
     */
    protected $errorCodes = array(
        self::FAILURE_MISSING_INPUT_SECRET   => 'The secret parameter is missing.',
        self::FAILURE_INVALID_INPUT_SECRET   => 'The secret parameter is invalid or malformed.',
        self::FAILURE_MISSING_INPUT_RESPONSE => 'The response parameter is missing.',
        self::FAILURE_INVALID_INPUT_RESPONSE => 'The response parameter is invalid or malformed.'
    );

    /**
     * Captcha html
     * 
     * @var string
     */
    protected $html = '';

    /**
     * Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct($c, array $params = array())
    {
        if (sizeof($params) == 0) {
            $params = $c['config']->load('captcha/recaptcha');
        }
        $this->c = $c;
        $this->config = $params;
        $this->tempConfig = $params;

        parent::__construct($c);
    }

    /**
     * Initialize
     * 
     * @return void
     */
    public function init()
    {
        if ($this->config['user']['autoSendIp']) {
            $this->setUserIp($this->c->load('request')->ip());
        }
        $this->buildHtml();
    }

    /**
     * Set site key
     * 
     * @param string $siteKey site key
     * 
     * @return self object
     */
    public function setSiteKey($siteKey)
    {
        $this->config['api']['key']['site'] = $siteKey;
        return $this;
    }

    /**
     * Set secret key
     * 
     * @param string $secretKey secret key
     * 
     * @return self object
     */
    public function setSecretKey($secretKey)
    {
        $this->config['api']['key']['secret'] = $secretKey;
        return $this;
    }

    /**
     * Set api language
     * 
     * @param string $lang language
     * 
     * @return self object
     */
    public function setLang($lang)
    {
        $this->config['locale']['lang'] = $lang;
        return $this;
    }

    /**
     * Set remote user ip address (optional)
     * 
     * @param string $ip user ip address
     * 
     * @return self object
     */
    public function setUserIp($ip)
    {
        $this->userIp = $ip;
        return $this;
    }

    /**
     * Get user ip
     * 
     * @return string
     */
    public function getUserIp()
    {
        return $this->userIp;
    }

    /**
     * Get site key
     * 
     * @return string
     */
    public function getSiteKey()
    {
        return $this->config['api']['key']['site'];
    }

    /**
     * Get secret key
     * 
     * @return string
     */
    public function getSecretKey()
    {
        return $this->config['api']['key']['secret'];
    }

    /**
     * Get api language
     * 
     * @return string
     */
    public function getLang()
    {
        return $this->config['locale']['lang'];
    }

    /**
     * Get javascript link
     * 
     * @return string
     */
    public function printJs()
    {
        $lang = $this->getLang();
        $link = static::CLIENT_API;

        if (empty($lang)) {
            $link = static::CLIENT_API .'?hl='. $lang;
        }
        print('<script src="'.$link.'" async defer></script>');
    }

    /**
     * Print captcha html
     * 
     * @return string html
     */
    public function printCaptcha()
    {
        print($this->html);
    }

    /**
     * Create captcha
     * 
     * @return void
     */
    public function create()
    {
        $this->buildHtml();
    }

    /**
     * Validation captcha
     * 
     * @param string $response response
     * 
     * @return bool
     */
    public function check($response = null)
    {
        if ($response == null) {
            $response = $this->c->load('request')->post($this->config['form']['input']['attributes']['name']);
        }
        $response = $this->sendVerifyRequest(
            array(
                'secret'   => $this->getSecretKey(),
                'response' => $response,
                'remoteip' => $this->getUserIp() // optional
            )
        );
        return $this->validateCaptcha($response);
    }

    /**
     * Validate captcha
     * 
     * @param array $response response
     * 
     * @return Captcha\CaptchaResult object
     */
    protected function validateCaptcha($response)
    {
        if (isset($response['success'])) {
            if ( ! $response['success']
                AND isset($response['error-codes'])
                AND sizeof($response['error-codes']) > 0
            ) {
                foreach ($response['error-codes'] as $err) {
                    if (isset($this->errorCodes[$err])) {
                        $this->result['code'] = CaptchaResult::FAILURE;
                        $this->result['messages'][$err] = $this->errorCodes[$err];
                    }
                }
                return $this->createResult();
            }
            if ($response['success'] === true) {
                $this->result['code'] = CaptchaResult::SUCCESS;
                $this->result['messages'][] = 'Captcha code has been entered successfully.';
                return $this->createResult();
            }
        }
        $this->result['code'] = CaptchaResult::FAILURE_CAPTCHA_NOT_FOUND;
        $this->result['messages'][] = 'The captcha response not found.';
        return $this->createResult();
    }

    /**
     * Send request verify
     * 
     * @param array $query http query
     * 
     * @return array
     */
    protected function sendVerifyRequest(array $query = array())
    {
        $link = static::VERIFY_URL .'?'. http_build_query($query);
        $response = file_get_contents($link);

        return json_decode($response, true);
    }

    /**
     * Build html
     * 
     * @return void
     */
    protected function buildHtml()
    {
        $attributes['data-sitekey'] = $this->getSitekey();
        $this->html = sprintf(
            '<div class="g-recaptcha" %s></div>',
            $this->buildAttributes($attributes)
        );
    }

    /**
     * Build attributes
     * 
     * @param array $attributes attributes
     * 
     * @return string
     */
    protected function buildAttributes(array $attributes)
    {
        $html = array();

        foreach ($attributes as $key => $value) {
            $html[] = $key.'="'.$value.'"';
        }
        return count($html) ? implode(' ', $html) : '';
    }

    /**
     * Generate code
     * 
     * @return void
     */
    public function generateCode()
    {
        return;
    }

    /**
     * Set validation
     * 
     * @return void
     */
    protected function validationSet()
    {
        return;
    }
}

// END ReCaptcha.php File
/* End of file ReCaptcha.php

/* Location: .Obullo/Captcha/Adapter/ReCaptcha.php */
