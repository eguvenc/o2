<?php

namespace Obullo\Sociality\Provider;

use SimpleXMLElement;

// class GoogleProvider extends AbstractProvider implements ProviderInterface
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
class Google extends AbstractProvider
{
    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * The scopes being requested.
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
        $this->clientId     = $this->params['client']['id'];
        $this->clientSecret = $this->params['client']['secret']; 
        // $this->setScopes($this->params['client']['scopes']); 
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
     * Get the access token for the given code.
     *
     * @param string $code token code
     * 
     * @return string
     */
    // public function getAccessToken($code)
    // {
    //     $response = $this->getHttpClient()
    //         ->setRequestUrl($this->getTokenUrl())
    //         ->setPostFields($this->getTokenFields($code))
    //         ->setHeaders(['Accept' => 'application/json'])
    //         ->post();

    //     return $this->parseAccessToken($response);
    // }

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code token code
     * 
     * @return array
     */
    protected function getTokenFields($code)
    {
        return $this->array_add(
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
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
                'https://www.google.com/m8/feeds/contacts/default/full?max-results='. $this->params['maxResults'] .'&oauth_token='. $token
            )
            ->setHeaders(
                [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '. $token,
                ]
            )
            ->get();

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
                'https://www.googleapis.com/plus/v1/people/me/people/visible?access_token='. $this->getAccessToken($this->getCode())
            )
            ->setHeaders(
                [
                    'Accept' => 'application/json',
                ]
            )
            ->get();

        return $response;
    }

    /**
     * Map user to object
     * 
     * @param array $user user data
     * 
     * @return array
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map(
            [
                // 'id'       => $user['id'],
                // 'nickname' => array_get($user, 'nickname'),
                // 'name'     => $user['displayName'],
                // 'email'    => $user['emails'][0]['value'],
                // 'avatar'   => array_get($user, 'image')['url'],
                'id'       => 1,
                'nickname' => 'nickname',
                'name'     => 'displayName',
                'email'    => 'emails',
                'avatar'   => 'image',
            ]
        );
    }
}
