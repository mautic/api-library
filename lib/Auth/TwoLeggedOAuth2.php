<?php

/*
 * @copyright   2021 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

/*
|--------------------------------------------------------------------------
| 2-legged OAuth2 using the grant_type  client_credentials
|--------------------------------------------------------------------------
|
| use Mautic\Auth\ApiAuth;
|
| // ApiAuth->newAuth() will accept an array of Auth settings
| $settings = array(
|     'AuthMethod'       => '2LeggedOAuth2' // Must be one of 'OAuth' or 'BasicAuth'
      'clientKey'        => '',       // Client/Consumer key from Mautic
      'clientSecret'     => '',       // Client/Consumer secret key from Mautic
|     'baseUrl'           => '',         // NOTE: Required for Unit tests; *must* contain a valid url
| );
|
| // Initiate the auth object
| $initAuth = new ApiAuth();
| $auth = $initAuth->newAuth($settings, $settings['AuthMethod']);
|
|--------------------------------------------------------------------------
| Basic API Usage
|--------------------------------------------------------------------------
|
| To use, just pass the auth object to the Api context you are creating.
|
| use Mautic\MauticApi;
|
| // Get a Contact context
| $api = new MauticApi();
| $contactApi = $api->newApi('contacts', $auth, $settings['apiUrl']);
|
| // Get Contact list
| $results = $contactApi->getList();
|
| Note: If the credentials are incorrect an error response will be returned:
| array('errors' => [[
|       'code'    => 403,
|       'message' => 'access_denied: OAuth2 authentication required' )
| ]]
|
*/

namespace Mautic\Auth;

use Mautic\Exception\RequiredParameterMissingException;

class TwoLeggedOAuth2 extends AbstractAuth
{
    /**
     * Password associated with Username.
     *
     * @var string
     */
    private $clientSecret;

    /**
     * Username or email, basically the Login Identifier.
     *
     * @var string
     */
    private $clientKey;

    /**
     * Access token returned by OAuth server.
     *
     * @var string
     */
    protected $_access_token;

    /**
     * @var string
     */
    private $baseurl;

    /**
     * @var string
     */
    private $_access_token_url;

    /**
     * {@inheritdoc}
     */
    public function isAuthorized()
    {
        return !empty($this->clientKey) && !empty($this->clientSecret);
    }

    /**
     * @param string $baseUrl
     * @param string $clientKey    The username to use for Authentication *Required*
     * @param string $clientSecret The Password to use                    *Required*
     *
     * @throws RequiredParameterMissingException
     */
    public function setup($baseUrl, $clientKey, $clientSecret)
    {
        // we MUST have the username and password. No Blanks allowed!
        //
        // remove blanks else Empty doesn't work
        $clientKey    = trim($clientKey);
        $clientSecret = trim($clientSecret);

        if (empty($clientKey) || empty($clientSecret)) {
            //Throw exception if the required parameters were not found
            $this->log('parameters did not include clientkey and/or clientSecret');
            throw new RequiredParameterMissingException(
                'One or more required parameters was not supplied. Both clientKey and clientSecret required!'
            );
        }

        $this->baseurl      = $baseUrl;
        $this->clientKey    = $clientKey;
        $this->clientSecret = $clientSecret;

        if (!$this->_access_token_url) {
            $this->_access_token_url = $baseUrl.'/oauth/v2/token';
        }
    }

    /**
     * @param $url
     * @param $method
     *
     * @return array
     */
    protected function prepareRequest($url, array $headers, array $parameters, $method, array $settings)
    {
        //Set Basic Auth parameters/headers
      //  $headers = array_merge($headers, ['Authorization: Bearer '.$this->_access_token]);

        return [$headers, $parameters];
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {

        $parameters = [
            'client_id'     => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'client_credentials',
        ];
        $params = $this->makeRequest($this->_access_token_url, $parameters, 'POST');
        print_r($params);
        die();
    }
}
