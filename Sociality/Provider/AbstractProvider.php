<?php

namespace Obullo\Sociality\Provider;

use Obullo\Utils\Curl\Client;
use InvalidArgumentException;

/**
 * Abstract Provider
 * 
 * @category  Provider
 * @package   Abstract
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
abstract class AbstractProvider
{
    /**
     * Redirect uri
     *
     * @var string
     */
    protected $redirectUri;

    /**
     * Utils\Curl instance.
     * 
     * @var null
     */
    protected $httpClient = null;

    /**
     * The client ID.
     *
     * @var string
     */
    protected $clientId = '';

    /**
     * The client secret.
     *
     * @var string
     */
    protected $clientSecret = '';

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ',';

    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738.
     */
    protected $encodingType = PHP_QUERY_RFC1738;

    /**
     * Access Token
     * 
     * @var null
     */
    protected $token = null;

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * Request Method
     * 
     * @var string
     */
    protected $requestMethod;

    /**
     * Create a new provider instance.
     *
     * @param object $storage storage class
     * @param array  $params  parameters
     * 
     * @return void
     */
    public function __construct($storage, $params)
    {
        $this->params  = $params;
        $this->storage = $storage;
        $this->init();
    }

    /**
     * Initializer
     * 
     * @return void
     */
    abstract protected function init();

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state state
     * 
     * @return string
     */
    abstract protected function getAuthUrl($state);

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    abstract protected function getTokenUrl();

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token access token
     * 
     * @return array
     */
    abstract protected function getContactsByToken($token);

    /**
     * Get the access token from the token response body.
     *
     * @param string $body response body
     * 
     * @return string
     */
    // abstract protected function parseAccessToken($body);

    /**
     * Get the GET parameters for the code request.
     * 
     * @param string $state Your client can insert state information that
     *                      will be appended to the redirect_uri upon success user authorization.
     *
     * @return array
     */
    abstract protected function getCodeFields($state);

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code token code
     * 
     * @return array
     */
    // abstract protected function getTokenFields($code);

    /**
     * Set access token
     * 
     * @param string $token accecss token
     * 
     * @return void
     */
    protected function setAccessToken($token)
    {
        $this->token = $token;
        $this->storage->set('access_token', $token);
    }

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @return string
     */
    public function redirect()
    {
        $this->storage->set(
            'state',
            $state = md5(time(). uniqid())
        );
        $this->removeToken();
        $this->c['url']->redirect($this->getAuthUrl($state));
    }

    /**
     * Redirect output; If you want to direct yourself.
     * 
     * @return string url
     */
    public function redirectOutput()
    {
        $this->storage->set(
            'state',
            $state = md5(time(). uniqid())
        );
        $this->removeToken();
        return $this->getAuthUrl($state);
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $url   url
     * @param string $state state
     * 
     * @return string
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        return $url .'?'. http_build_query($this->getCodeFields($state));
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param string $state state
     * 
     * @return array
     */
    // protected function getCodeFields($state)
    // {
    //     return [
    //         'client_id'     => $this->clientId,
    //         'redirect_uri'  => $this->getRedirectUri(),
    //         'scope'         => $this->formatScopes($this->scopes, $this->scopeSeparator),
    //         'state'         => $state,
    //         'response_type' => 'code',
    //     ];
    // }

    /**
     * Format the given scopes.
     *
     * @param array  $scopes         scopes
     * @param string $scopeSeparator scope separator
     * 
     * @return string
     */
    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }

    /**
     * Get all contacts
     * 
     * @return array
     */
    public function getAllContacts()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidArgumentException('Security state not correct');
        }
        return $this->getContactsByToken(
            $this->getAccessToken($this->getCode())
        );
    }

    /**
     * Determine if the current request / session has a mismatching "state".
     *
     * @return bool
     */
    protected function hasInvalidState()
    {
        return ! ($this->c['request']->all('state') === $this->storage->get('state'));
    }

    /**
     * Get the access token for the given code.
     *
     * @param string $code token code
     * 
     * @return string
     */
    public function getAccessToken($code)
    {
        if ($this->token || ($this->token = $this->storage->get('access_token'))) {
            return $this->token;
        }
        $method = $this->requestMethod;
        $data   = $this->getTokenFields($code);

        $response = $this->client()
            ->setUrl($this->getTokenUrl())
            ->setOpt(CURLOPT_USERPWD, $this->clientId .':'. $this->clientSecret)
            ->setHeader('Accept', 'application/json')
            // ->setHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->$method($data);
            
        return $this->parseAccessToken($response);
    }

    /**
     * Get the access token from the token response body.
     *
     * @param string $body response body
     * 
     * @return string
     */
    protected function parseAccessToken($body)
    {
        $response = $this->client()->jsonDecode($body, true);

        if (isset($response['access_token'])) {
            $this->setAccessToken($response['access_token']);
            return $response['access_token'];
        }
        throw new InvalidArgumentException('Missing parameter "access_token"');   
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code token code
     * 
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'code'          => $code,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->getRedirectUri(),
            'grant_type'    => 'authorization_code'
        ];
    }

    /**
     * Get the code from the request.
     *
     * @return string
     */
    protected function getCode()
    {
        return $this->c['request']->all(static::TOKEN_REQUEST);
    }

    /**
     * Get a instance of HTTP client.
     *
     * @return Http\Client
     */
    protected function client()
    {
        if ($this->httpClient == null) {
            return $this->httpClient = new Client;
        }
        return $this->httpClient;
    }

    /**
     * Get redirect uri
     * 
     * @return void
     */
    public function getRedirectUri()
    {
        if (empty($this->redirectUri)) {
            $uris = $this->params['redirect'];
            return $this->redirectUri = $uris[array_keys($uris)[0]];
        }
        return $this->redirectUri;
    }

    /**
     * Set redirect uri
     *
     * @param mix $key uri key
     * 
     * @return $this
     */
    public function setRedirectUri($key)
    {
        $this->redirectUri = $this->params['redirect'][$key];
        return $this;
    }

    /**
     * Remove access token
     * 
     * @return void
     */
    public function removeToken()
    {
        $this->token = null;
        $this->storage->remove('access_token');
    }
}
