<?php

namespace Mautic\Auth;

use Mautic\Exception\IncorrectParametersReturnedException;
use Mautic\Exception\RequiredParameterMissingException;

/**
 * OAuth Client modified from https://code.google.com/p/simple-php-oauth/.
 */
class TwoLeggedOAuth2 extends AbstractAuth
{
    /**
     * Access token URL.
     *
     * @var string
     */
    protected $_access_token_url;

    /**
     * Access token returned by OAuth server.
     *
     * @var string
     */
    protected $_access_token;

    /**
     * Consumer or client key.
     *
     * @var string
     */
    protected $_client_id;

    /**
     * Consumer or client secret.
     *
     * @var string
     */
    protected $_client_secret;

    /**
     * Unix timestamp for when token expires.
     *
     * @var string
     */
    protected $_expires;

    /**
     * OAuth2 token type.
     *
     * @var string
     */
    protected $_token_type = 'bearer';

    /**
     * Set to true if the access token was updated.
     *
     * @var bool
     */
    protected $_access_token_updated = false;

    /**
     * @param string $baseUrl            URL of the Mautic instance
     * @param string $clientKey
     * @param string $clientSecret
     * @param string $accessToken
     * @param string $accessTokenExpires
     */
    public function setup(
    $baseUrl = null,
    $clientKey = null,
    $clientSecret = null,
    $accessToken = null,
    $accessTokenExpires = null
  ) {
        if (empty($clientKey) || empty($clientSecret)) {
            //Throw exception if the required parameters were not found
            $this->log('parameters did not include clientkey and/or clientSecret');
            throw new RequiredParameterMissingException('One or more required parameters was not supplied. Both clientKey and clientSecret required!');
        }

        if (empty($baseUrl)) {
            //Throw exception if the required parameters were not found
            $this->log('parameters did not include baseUrl');
            throw new RequiredParameterMissingException('One or more required parameters was not supplied. baseUrl required!');
        }

        $this->_client_id        = $clientKey;
        $this->_client_secret    = $clientSecret;
        $this->_access_token     = $accessToken;
        $this->_access_token_url = $baseUrl.'/oauth/v2/token';

        if (!empty($accessToken)) {
            $this->setAccessTokenDetails([
              'access_token' => $accessToken,
              'expires'      => $accessTokenExpires,
            ]);
        }
    }

    /**
     * Check to see if the access token was updated.
     *
     * @return bool
     */
    public function accessTokenUpdated()
    {
        return $this->_access_token_updated;
    }

    /**
     * Returns access token data.
     *
     * @return array
     */
    public function getAccessTokenData()
    {
        return [
          'access_token' => $this->_access_token,
          'expires'      => $this->_expires,
          'token_type'   => $this->_token_type,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthorized()
    {
        $this->log('isAuthorized()');

        return $this->validateAccessToken();
    }

    /**
     * Set an existing/already retrieved access token.
     *
     * @return $this
     */
    public function setAccessTokenDetails(array $accessTokenDetails)
    {
        $this->_access_token = $accessTokenDetails['access_token'] ?? null;
        $this->_expires      = $accessTokenDetails['expires'] ?? null;

        return $this;
    }

    /**
     * Validate existing access token.
     *
     * @return bool
     */
    public function validateAccessToken()
    {
        $this->log('validateAccessToken()');

        //Check to see if token in session has expired
        if (strlen($this->_access_token) > 0 && !empty($this->_expires) && $this->_expires < (time() + 10)) {
            $this->log('access token expired');

            return false;
        }

        //Check for existing access token
        if (strlen($this->_access_token) > 0) {
            $this->log('has valid access token');

            return true;
        }

        //If there is no existing access token, it can't be valid
        return false;
    }

    /**
     * @param $isPost
     * @param $parameters
     *
     * @return array
     */
    protected function getQueryParameters($isPost, $parameters)
    {
        $query = parent::getQueryParameters($isPost, $parameters);

        if (isset($parameters['file'])) {
            //Mautic's OAuth2 server does not recognize multipart forms so we have to append the access token as part of the URL
            $query['access_token'] = $parameters['access_token'];
        }

        return $query;
    }

    /**
     * @param       $url
     * @param array $method
     *
     * @return array
     */
    protected function prepareRequest($url, array $headers, array $parameters, $method, array $settings)
    {
        if ($this->isAuthorized()) {
            $headers = array_merge($headers, ['Authorization: Bearer '.$this->_access_token]);
        }

        return [$headers, $parameters];
    }

    /**
     * Request access token.
     *
     * @return bool
     *
     * @throws IncorrectParametersReturnedException|\Mautic\Exception\UnexpectedResponseFormatException
     */
    public function requestAccessToken()
    {
        $this->log('requestAccessToken()');

        $parameters = [
          'client_id'     => $this->_client_id,
          'client_secret' => $this->_client_secret,
          'grant_type'    => 'client_credentials',
        ];

        //Make the request
        $params = $this->makeRequest($this->_access_token_url, $parameters, 'POST');

        //Add the token to session
        if (is_array($params)) {
            if (isset($params['access_token']) && isset($params['expires_in'])) {
                $this->log('access token set as '.$params['access_token']);

                $this->_access_token         = $params['access_token'];
                $this->_expires              = time() + $params['expires_in'];
                $this->_token_type           = (isset($params['token_type'])) ? $params['token_type'] : null;
                $this->_access_token_updated = true;

                if ($this->_debug) {
                    $_SESSION['oauth']['debug']['tokens']['access_token'] = $params['access_token'];
                    $_SESSION['oauth']['debug']['tokens']['expires_in']   = $params['expires_in'];
                    $_SESSION['oauth']['debug']['tokens']['token_type']   = $params['token_type'];
                }

                return true;
            }
        }

        $this->log('response did not have an access token');

        if ($this->_debug) {
            $_SESSION['oauth']['debug']['response'] = $params;
        }

        if (is_array($params)) {
            if (isset($params['errors'])) {
                $errors = [];
                foreach ($params['errors'] as $error) {
                    $errors[] = $error['message'];
                }
                $response = implode('; ', $errors);
            } else {
                $response = print_r($params, true);
            }
        } else {
            $response = $params;
        }

        throw new IncorrectParametersReturnedException('Incorrect access token parameters returned: '.$response);
    }
}
