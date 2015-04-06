<?php

namespace Obullo\Sociality\Provider;

use LogicException;
use SimpleXMLElement;
use Obullo\Sociality\Provider\ProviderInterface;

/**
 * Live Provider
 * 
 * @category  Provider
 * @package   Live
 * @author    Ali İhsan ÇAĞLAYAN <ihsancaglayan@gmail.com>
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/sociality
 * @api       https://msdn.microsoft.com/en-us/library/hh826528.aspx
 */
class Live extends AbstractProvider implements ProviderInterface
{
    const PREFIX = 'live';
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
     * This "contacts_emails" I found it the answer.
     * You can see stackoverflow link.
     * @link http://stackoverflow.com/a/15414222/2866158
     * 
     * You can also find all scopes on the microsoft link.
     * @link https://msdn.microsoft.com/en-us/library/hh243646.aspx
     * 
     * @var  array
     */
    protected $scopes = [
        'wl.basic',
        'wl.signin',
        'wl.contacts_emails',
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
        $this->requestMethod = 'get';
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
        $response = $this->getHttpClient()
            ->setRequestUrl(
                'https://apis.live.net/v5.0/me/contacts?access_token='. $token
            )
            ->setMethod('get')
            ->setHeaders(
                [
                    'Accept' => 'application/json',
                    // 'Authorization' => 'Bearer '. $token,
                ]
            )
            ->send();
            
        return $this->parseContacts($response);
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
        if (($data = $this->getHttpClient()->jsonDecode($response, true)) && isset($data['data'])) {
            foreach ($data['data'] as $val) {
                // Initialize an array out here.
                $contacts = array();
                // Get the title and link attributes (link as an array)
                $contacts['name'] = (string)$val['name'];
                $emailAddress     = (string)$val['emails']['preferred'];

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

// END Live.php File
/* End of file Live.php

/* Location: .Obullo/Sociality/Provider/Live.php */
