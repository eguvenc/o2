<?php

namespace Obullo\Sociality\Provider;

use LogicException;
use SimpleXMLElement;
use Obullo\Sociality\Provider\ProviderInterface;

/**
 * Yahoo Provider
 * 
 * @category  Provider
 * @package   Yahoo
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
class Yahoo extends AbstractProvider implements ProviderInterface
{
    const PREFIX = 'yahoo';
    const TOKEN_REQUEST = 'code';

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * Yahoo doesn't accept scope,
     * you need to set this link;
     * 
     * @link https://developer.apps.yahoo.com/projects
     * Select or create a new project.
     * After you've done it you can follow "Permissions"
     * 
     * @var array
     */
    protected $scopes = [];

    /**
     * Initialize
     * 
     * @return void
     */
    protected function init()
    {
        $this->clientId      = $this->params['consumer']['key'];
        $this->clientSecret  = $this->params['consumer']['secret'];
        $this->requestMethod = 'post';
    }

    /**
     * Get auth url
     * 
     * @param string $state state
     * 
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->params['oauth']['uri'], $state);
    }

    /**
     * Get token url
     * 
     * @return string
     */
    public function getTokenUrl()
    {
        $this->getHttpClient()->setOption(CURLOPT_USERPWD, $this->clientId .':'. $this->clientSecret);
        return $this->params['oauth']['token'];
    }

    /**
     * Get the GET parameters for the code request.
     * 
     * @param string $state Your client can insert state information that
     *                      will be appended to the redirect_uri upon success user authorization.
     *
     * @return array
     */
    protected function getCodeFields($state)
    {
        return [
            'client_id'     => $this->clientId,
            'redirect_uri'  => parent::getRedirectUri(),
            'response_type' => 'code',
            'state'         => $state,
            'provider'      => 'yahoo'
        ];
    }

    /**
     * Get user by token
     * 
     * @param string $token token code
     * 
     * @return array
     */
    protected function getContactsByToken($token)
    {
        $response = $this->getHttpClient()
            ->setRequestUrl(
                sprintf(
                    'https://social.yahooapis.com/v1/user/me/contacts', $this->storage->get('xoauth_yahoo_guid')
                )
            )
            ->setMethod('get')
            ->setFields('format=json')
            ->setHeaders(
                [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ]
            )
            ->send();

        return $this->parseContacts($response);
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
        $response = $this->getHttpClient()->jsonDecode($body, true);

        if (isset($response['access_token'])) {
            $this->storage->set($response);
            return $response['access_token'];
        }
        throw new InvalidArgumentException('Missing parameter "access_token"');
    }

    /**
     * Parse contacts
     * 
     * @param string $response curl exec response
     * 
     * @return array
     */
    protected function parseContacts($response)
    {
        if (($data = $this->getHttpClient()->jsonDecode($response, true)) && isset($data['contacts'])) {
            foreach ($data['contacts']['contact'] as $val) {
                $contacts = array();
                foreach ($val['fields'] as $user) {
                    // Initialize an array out here.
                    // Get the title and link attributes (link as an array)
                    if ($user['type'] == 'name') {
                        $contacts['name'] = (string)$user['value']['givenName'];
                    } elseif ($user['type'] == 'email') {
                        $emailAddress = (string)$user['value'];
                    }
                }
                if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL) !== false) { // Get the valid email
                    $contacts['email'] = $emailAddress;
                }
                // Append your array to the larger output
                if (isset($contacts['email'])) {
                    $outputArray[] = $contacts;
                }
            }
            return $outputArray;
        }
        if (isset($data['error'])) {
            throw new LogicException($data['error_description'] .' Error code: '. $data['error']);
        }
        return false;
    }
}

// END Yahoo.php File
/* End of file Yahoo.php

/* Location: .Obullo/Sociality/Provider/Yahoo.php */