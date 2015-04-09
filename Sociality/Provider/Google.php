<?php

namespace Obullo\Sociality\Provider;

use SimpleXMLElement;
use Obullo\Sociality\Provider\ProviderInterface;

/**
 * Google Provider
 * 
 * @category  Provider
 * @package   Google
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
class Google extends AbstractProvider implements ProviderInterface
{
    const PREFIX = 'google';
    const TOKEN_REQUEST = 'code';

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * The scopes being requested.
     * 
     * The following scopes, with user consent,
     * provide access to otherwise restricted user data.
     * @link texthttps://developers.google.com/+/api/oauth#scopes
     * 
     * Here's the OAuth 2.0 scope information for the Google Contacts API:
     * @link https://developers.google.com/google-apps/contacts/v3/#authorizing_requests_to_the_api_name_service
     *
     * @var array
     */
    protected $scopes = [
        'https://www.google.com/m8/feeds/&response_type=code',
        'https://www.googleapis.com/auth/plus.me',
        'https://www.googleapis.com/auth/plus.login',
        'https://www.googleapis.com/auth/plus.circles.read',
        'https://www.googleapis.com/auth/plus.profile.emails.read',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
    ];

    /**
     * Initialize
     * 
     * @return void
     */
    protected function init()
    {
        $this->clientId      = $this->params['client']['id'];
        $this->clientSecret  = $this->params['client']['secret']; 
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
    protected function getTokenUrl()
    {
        return $this->params['oauth']['token'];
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param string $state state
     * 
     * @return array
     */
    protected function getCodeFields($state)
    {
        return [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->getRedirectUri(),
            'scope'         => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'state'         => $state,
            'response_type' => 'code',
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
        $querys = [
            'max-results' => $this->params['maxResults'],
            'oauth_token' => $token
        ];
        $response = $this->getHttpClient()
            ->setRequestUrl(
                'https://www.google.com/m8/feeds/contacts/default/full'
            )
            ->setFields($querys)
            ->setMethod('get')
            ->setHeaders(
                [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '. $token,
                ]
            )
            ->send();
            
        return $this->parseContactXml($response);
    }

    /**
     * Google+ List of Visible Peoples
     * 
     * [kind] => plus#peopleFeed
     * [etag] => "RqKWnRU4WW46-6W3rWhLR9iFZQM/quEIn5NpuNVOeqVU3NqJdxndqHE"
     * [title] => Google+ List of Visible People
     * [totalItems] => 18
     * [items] => Array
     * (
     *     [0] => Array
     *     (
     *         [kind] => plus#person
     *         [etag] => "RqKWnRU4WW46-6W3rWhLR9iFZQM/sAlvk04zWMyfFtRxownnP-74wR8"
     *         [objectType] => person
     *         [id] => 108486234400851501591
     *         [displayName] => Name Surname
     *         [url] => https://plus.google.com/108486234400851501592
     *         [image] => Array
     *         (
     *             [url] => https://lh6.googleusercontent.com/-NjwfYM_SNEA/AAAAAAAAAAI/AAAAAAAAAXg/8SDzA6mLen0/photo.jpg?sz=50
     *         )
     *     ) ... more elements
     * )
     * 
     * @return array
     */
    public function gPlusListOfVisiblePeoples()
    {
        $response = $this->getHttpClient()
            ->setRequestUrl(
                'https://www.googleapis.com/plus/v1/people/me/people/visible'
            )
            ->setFields('access_token='. $this->getAccessToken($this->getCode()))
            ->setMethod('get')
            ->setHeaders(
                [
                    'Accept' => 'application/json',
                ]
            )
            ->send();

        return $response;
    }

    /**
     * Parse xml element
     * 
     * @param string $response curl exec response
     * 
     * @return SimpleXMLElement
     */
    protected function parseContactXml($response)
    {
        $xml = new SimpleXMLElement($response);
        $xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');
        $outputArray = array();

        if (! empty($xml->error->code)) {
            throw new \LogicException($xml->error->internalReason);
        }
        foreach ($xml->entry as $entry) {
            // Initialize an array out here.
            $contacts = array();
            // Get the title and link attributes (link as an array)
            $contacts['name'] = (string)$entry->title;
            // If there are never more than 1 email, you don't need a loop here.
            foreach ($entry->xpath('gd:email') as $email) {
                $emailAddr = (string)$email->attributes()->address;
                if (filter_var($emailAddr, FILTER_VALIDATE_EMAIL) !== false) { // Get the valid email
                    $contacts['email'] = $emailAddr;
                }
            }
            // Append your array to the larger output
            if (isset($contacts['email'])) {
                $outputArray[] = $contacts;
            }
        }
        return $outputArray;
    }
}

// END Google.php File
/* End of file Google.php

/* Location: .Obullo/Sociality/Provider/Google.php */
