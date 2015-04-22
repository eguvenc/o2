<?php

namespace Obullo\Captcha\Adapter;

use Obullo\Container\Container;
use Obullo\Captcha\CaptchaResult;
use Obullo\Captcha\AbstractAdapter;
use Obullo\Captcha\AdapterInterface;

/**
 * Captcha ReCaptcha Adapter
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
class ReCaptcha extends AbstractAdapter implements AdapterInterface
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
    protected $errorCodes = array();

    /**
     * Captcha html
     * 
     * @var string
     */
    protected $html = '';

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $c['config']->load('recaptcha/recaptcha');

        $this->c['translator']->load('captcha');

        $this->errorCodes = array(
            self::FAILURE_MISSING_INPUT_SECRET   => $this->c['translator']['OBULLO:RECAPTCHA:MISSING_INPUT_SECRET'],
            self::FAILURE_INVALID_INPUT_SECRET   => $this->c['translator']['OBULLO:RECAPTCHA:INVALID_INPUT_SECRET'],
            self::FAILURE_MISSING_INPUT_RESPONSE => $this->c['translator']['OBULLO:RECAPTCHA:MISSING_INPUT_RESPONSE'],
            self::FAILURE_INVALID_INPUT_RESPONSE => $this->c['translator']['OBULLO:RECAPTCHA:INVALID_INPUT_RESPONSE']
        );
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
            $this->setUserIp($this->c['request']->getIpAddress());
        }
        $this->validationSet();
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
     * Get captcha input name
     * 
     * @return string name
     */
    public function getInputName()
    {
        return $this->config['form']['input']['attributes']['name'];
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

        if ( ! empty($lang)) {
            $link = static::CLIENT_API .'?hl='. $lang;
        }
        return '<script src="'.$link.'" async defer></script>';
    }

    /**
     * Print captcha html
     * 
     * @return string html
     */
    public function printHtml()
    {
        return $this->html;
    }

    /**
     * Validation captcha
     * 
     * @param string $response response
     * 
     * @return bool
     */
    public function result($response = null)
    {
        if ($response == null) {
            $response = $this->c['request']->post('g-recaptcha-response');
        }
        $response = $this->sendVerifyRequest(
            array(
                'secret'   => $this->getSecretKey(),
                'response' => $response,
                'remoteip' => $this->getUserIp() // optional
            )
        );
        return $this->validateCode($response);
    }

    /**
     * Validate captcha
     * 
     * @param array $response response
     * 
     * @return Captcha\CaptchaResult object
     */
    protected function validateCode($response)
    {
        if (isset($response['success'])) {
            if ( ! $response['success']
                AND isset($response['error-codes'])
                AND sizeof($response['error-codes']) > 0
            ) {
                foreach ($response['error-codes'] as $err) {
                    if (isset($this->errorCodes[$err])) {
                        $this->result['code'] = $err;
                        $this->result['messages'][] = $this->errorCodes[$err];
                    }
                }
                return $this->createResult();
            }
            if ($response['success'] === true) {
                $this->result['code'] = CaptchaResult::SUCCESS;
                $this->result['messages'][] = $this->c['translator']['OBULLO:CAPTCHA:SUCCESS'];
                return $this->createResult();
            }
        }
        $this->result['code'] = CaptchaResult::FAILURE_CAPTCHA_NOT_FOUND;
        $this->result['messages'][] = $this->c['translator']['OBULLO:CAPTCHA:NOT_FOUND'];
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
        unset($this->config['form']['validation']);
        foreach ($this->config['form'] as $key => $val) {
            $this->html .= vsprintf(
                '<%s %s/>',
                array(
                    $key,
                    $this->buildAttributes($val['attributes'])
                )
            );
        }
        $attributes['data-sitekey'] = $this->getSitekey();
        $this->html.= sprintf(
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
     * Set validation
     * 
     * @return void
     */
    protected function validationSet()
    {
        if ( ! $this->config['form']['validation']['enabled']) {
            return;
        }
        $label = $this->c['translator']['OBULLO:CAPTCHA:LABEL'];
        $rules = 'required';
        $post  = $this->c['request']->isPost();

        if ($this->config['form']['validation']['callback'] AND $post) {  // Add callback if we have http post

            $rules.= '|callback_captcha';  // Add callback validation rule

            $self = $this;
            $this->c['validator']->func(
                'callback_captcha',
                function () use ($self, $label) {
                    if ($self->result()->isValid() == false) {
                        $this->setMessage('callback_captcha', $this->c['translator']->get('OBULLO:CAPTCHA:VALIDATION', $label));
                        return false;
                    }
                    return true;
                }
            );
        }
        if ($post) {
            $this->c['validator']->setRules(
                $this->config['form']['input']['attributes']['name'],
                $label,
                $rules
            );
        }
    }
}

// END ReCaptcha.php File
/* End of file ReCaptcha.php

/* Location: .Obullo/Captcha/Adapter/ReCaptcha.php */