<?php

/*
 * @copyright   2021 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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
    public function setup($baseUrl, $clientKey, $clientSecret, $accessToken = null)
    {
        // we MUST have the username and password. No Blanks allowed!
        //
        // remove blanks else Empty doesn't work
        $clientKey    = trim($clientKey);
        $clientSecret = trim($clientSecret);

        if (empty($clientKey) || empty($clientSecret)) {
            //Throw exception if the required parameters were not found
            $this->log('parameters did not include clientkey and/or clientSecret');
            throw new RequiredParameterMissingException('One or more required parameters was not supplied. Both clientKey and clientSecret required!');
        }

        $this->baseurl       = $baseUrl;
        $this->clientKey     = $clientKey;
        $this->clientSecret  = $clientSecret;
        $this->_access_token = $accessToken;

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
        if (null !== $this->_access_token) {
            $headers = array_merge($headers, ['Authorization: Bearer '.$this->_access_token]);
        }

        return [$headers, $parameters];
    }

    public function getAccessToken(): string
    {
        $parameters      = [
            'client_id'     => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'client_credentials',
        ];
        $accessTokenData = $this->makeRequest($this->_access_token_url, $parameters, 'POST');
        //store access token data however you want
        $this->_access_token = $accessTokenData['access_token'] ?? null;

        return $this->_access_token;
    }
}
