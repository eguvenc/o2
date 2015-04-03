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
     * Get request auth url
     * 
     * @return string
     */
    // private function _getRequestAuthUrl()
    // {
    //     $xoauth_request_auth_url = false;
    //     return $response = $this->getRequestToken();
    //     parse_str($response);

    //     if ($xoauth_request_auth_url) {
    //         $this->storage->set('tokenSecret', $oauth_token_secret);
    //         return $xoauth_request_auth_url;
    //     }
    //     $oauth_problem = ($oauth_problem == false) ? '' : '<p>Yahoo error code: '. $oauth_problem .'</p>';
    //     throw new LogicException(
    //         'Something went wrong, key not found. Missing Parameter: xoauth_request_auth_url'. $oauth_problem
    //     );
    // }

    /**
     * Get request token
     * 
     * @return string client response
     */
    // public function getRequestToken()
    // {
    //     $response = $this->getHttpClient()
    //         ->setRequestUrl(
    //             parent::buildAuthUrlFromBase('https://api.login.yahoo.com/oauth2/request_auth', null)
    //         )
    //         ->get();

    //     return $response;
    // }

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
        // $this->getHttpClient()->setOption(CURLOPT_USERPWD, $this->clientId .':'. $this->clientSecret);
        return [
            'client_id'     => $this->clientId,
            'redirect_uri'  => parent::getRedirectUri(),
            'response_type' => 'code',
            'state'         => $state
        ];
        // return [
        //     'oauth_timestamp'        => time() + 600,
        //     'oauth_nonce'            => mt_rand(),
        //     'oauth_consumer_key'     => $this->clientId,
        //     'oauth_signature_method' => 'PLAINTEXT',
        //     'oauth_signature'        => $this->clientSecret. '&',
        //     'oauth_version'          => 1.0,
        //     'xoauth_lang_pref'       => 'en-us',
        //     'oauth_callback'         => parent::getRedirectUri(),
        // ];
    }

    /**
     * Get token fields
     * 
     * @return array
     */
    // public function getTokenFields($code = '')
    // {
    //     // https://api.login.yahoo.com/oauth/v2/get_token?oauth_consumer_key=dj0yJmk9NG5USlVvTlZsZEpnJmQ9WVdrOVQwa
    //     // zFPRUozTkc4bWNHozlNVE13TXprM01UUTBNZy0tJnM9Y29uc3VtZXJzZWNyZXQmeD1kNg--
    //     // &oauth_signature_method=PLAINTEXT
    //     // &oauth_version=1.0
    //     // &oauth_verifier=svmhhd
    //     // &oauth_token=gugucz
    //     // &oauth_timestamp=1228169662
    //     // &oauth_nonce=8B9SpF
    //     // &oauth_signature=5f78507cf0acc38890cf5aa697210822e90c8b1c%261fa61b464613d0d32de80089fe099caf34c9dac5
    //     return [
    //         'oauth_consumer_key'     => $this->clientId,
    //         'oauth_signature_method' => 'PLAINTEXT',
    //         'oauth_verifier'         => $this->c['request']->all('oauth_verifier'),
    //         'oauth_token'            => $this->c['request']->all('oauth_token'),
    //         'oauth_version'          => 1.0,
    //         'oauth_timestamp'        => time() + 600,
    //         'oauth_nonce'            => mt_rand(),
    //         'oauth_signature'        => $this->clientSecret .'&'. $this->storage->get('tokenSecret'),
    //     ];
    // }

    /**
     * Get user by token
     * 
     * @param string $token token code
     * 
     * @return array
     */
    protected function getContactsByToken($token)
    {
        // $curl = curl_init('https://social.yahooapis.com/v1/user/me/contacts?format=json');
        // curl_setopt($curl, CURLOPT_POST, false);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($curl, CURLOPT_HEADER, false);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt(
        //     $curl,
        //     CURLOPT_HTTPHEADER,
        //     array(
        //         'Authorization: Bearer '.$token,
        //         'Accept: application/json',
        //         'Content-Type: application/json'
        //     )
        // );
        // $response = curl_exec($curl);
        // if (empty($response)) {
        //     // some kind of an error happened
        //     die(curl_error($curl));
        //     curl_close($curl); // close cURL handler
        // } else {
        //     $info = curl_getinfo($curl);
        //     echo "Time took: " . $info['total_time']*1000 . "ms\n";
        //     curl_close($curl); // close cURL handler
        //     if ($info['http_code'] != 200 && $info['http_code'] != 201 ) {
        //         echo "Received error: " . $info['http_code']. "\n";
        //         echo "Raw response:".$response."\n";
        //         die();
        //     }
        // }
        // echo '<pre>';
        // var_dump($response);
        // die('die');
        // Convert the result from JSON format to a PHP array
        // $jsonResponse = json_decode($response, TRUE);
        // return $jsonResponse;



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

        // $headers['format']                 = 'json';
        // $headers['view']                   = 'compact';
        // $headers['oauth_version']          = '1.0';
        // $headers['oauth_nonce']            = mt_rand();
        // $headers['oauth_timestamp']        = time();
        // $headers['oauth_consumer_key']     = $this->clientId;
        // $headers['oauth_token']            = $token;
        // $headers['oauth_signature_method'] = 'HMAC-SHA1';

        // $requestUrl   = sprintf('https://social.yahooapis.com/v1/user/%s/contacts', $this->storage->get('yahooGuid'));
        // $signatureKey = $this->clientSecret .'&'. $this->storage->get('tokenSecret');
        // $baseString   = urlencode('POST&'. $requestUrl .'&'. http_build_query($headers));
        // $signature    = urlencode(base64_encode(hash_hmac('sha1', $baseString, $signatureKey, true)));

        // $headers['oauth_signature'] = $signature;
        // $headers['realm']           = 'yahooapis.com';
        // $headers['Authorization']   = 'OAuth';
        // $headers['Content-Type']    = 'application/x-www-form-urlencoded';
        
        // // POST&https%3A%2F%2Fapi.twitter.com%2F1%2Fstatuses%2Fupdate.json&include_entities%3Dtrue%26oauth_consumer_key%3Dxvz1evFS4wEEPTGEFPHBog%26oauth_nonce%3DkYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1318622958%26oauth_token%3D370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb%26oauth_version%3D1.0%26status%3DHello%2520Ladies%2520%252B%2520Gentlemen%252C%2520a%2520signed%2520OAuth%2520request%2521
        // // 
        // $response = $this->getHttpClient()
        //     ->setRequestUrl($requestUrl)
        //     ->setHeaders($headers)
        //     // ->setOption(CURLOPT_PORT, 80)
        //     ->setOption(CURLOPT_HEADER, true)
        //     ->setOption(CURLINFO_HEADER_OUT, true)
        //     ->setOption(CURLOPT_VERBOSE, true)
        //     ->get();
        //     echo '<pre>';
        //     // print_r($headers);
        //     echo($response);
        //     die('die');
        // return $this->parseContactXml($response);
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
