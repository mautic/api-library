<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\Auth;

use Mautic\Exception\AuthorizationRequiredException;
use Mautic\Exception\IncorrectParametersReturnedException;

/**
 * OAuth Client modified from https://code.google.com/p/simple-php-oauth/.
 */
class OAuth extends AbstractAuth
{
    /**
     * Access token returned by OAuth server.
     *
     * @var string
     */
    protected $_access_token;

    /**
     * Access token secret returned by OAuth server.
     *
     * @var string
     */
    protected $_access_token_secret;

    /**
     * Set to true if a refresh token was used to update an access token.
     *
     * @var bool
     */
    protected $_access_token_updated = false;

    /**
     * Access token URL.
     *
     * @var string
     */
    protected $_access_token_url;

    /**
     * Authorize URL.
     *
     * @var string
     */
    protected $_authorize_url;

    /**
     * Callback or Redirect URL.
     *
     * @var string
     */
    protected $_callback;

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
     * @var bool
     */
    protected $_do_not_redirect = false;

    /**
     * Unix timestamp for when token expires.
     *
     * @var string
     */
    protected $_expires;

    /**
     * OAuth2 redirect type.
     *
     * @var string
     */
    protected $_redirect_type = 'code';

    /**
     * OAuth2 refresh token.
     *
     * @var string
     */
    protected $_refresh_token;

    /**
     * Request token URL for OAuth1.
     *
     * @var string
     */
    protected $_request_token_url;

    /**
     * OAuth2 scope.
     *
     * @var array
     */
    protected $_scope = [];

    /**
     * OAuth2 token type.
     *
     * @var string
     */
    protected $_token_type;

    /**
     * Check to see if the access token was updated from a refresh token.
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
        if ($this->isOauth1()) {
            return [
                'access_token'        => $this->_access_token,
                'access_token_secret' => $this->_access_token_secret,
                'expires'             => $this->_expires,
            ];
        }

        return [
            'access_token'  => $this->_access_token,
            'expires'       => $this->_expires,
            'token_type'    => $this->_token_type,
            'refresh_token' => $this->_refresh_token,
        ];
    }

    public function isAuthorized()
    {
        // Check for existing access token
        if (!empty($this->_request_token_url)) {
            if (strlen($this->_access_token) > 0 && strlen($this->_access_token_secret) > 0) {
                return true;
            }
        }

        // Check to see if token in session has expired
        if (!empty($this->_expires) && $this->_expires < time()) {
            return false;
        }

        if (strlen($this->_access_token) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Set an existing/already retrieved access token.
     *
     * @return $this
     */
    public function setAccessTokenDetails(array $accessTokenDetails)
    {
        $this->_access_token        = isset($accessTokenDetails['access_token']) ? $accessTokenDetails['access_token'] : null;
        $this->_access_token_secret = isset($accessTokenDetails['access_token_secret']) ? $accessTokenDetails['access_token_secret'] : null;
        $this->_expires             = isset($accessTokenDetails['expires']) ? $accessTokenDetails['expires'] : null;
        $this->_refresh_token       = isset($accessTokenDetails['refresh_token']) ? $accessTokenDetails['refresh_token'] : null;

        return $this;
    }

    /**
     * Set access token URL.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setAccessTokenUrl($url)
    {
        $this->_access_token_url = $url;

        return $this;
    }

    /**
     * Set authorization URL.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setAuthorizeUrl($url)
    {
        $this->_authorize_url = $url;

        return $this;
    }

    /**
     * Set redirect type for OAuth2.
     *
     * @param string $type
     *
     * @return $this
     */
    public function setRedirectType($type)
    {
        $this->_redirect_type = $type;

        return $this;
    }

    /**
     * Set request token URL.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setRequestTokenUrl($url)
    {
        $this->_request_token_url = $url;

        return $this;
    }

    /**
     * Set OAuth2 scope.
     *
     * @param array|string $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        if (!is_array($scope)) {
            $this->_scope = explode(',', $scope);
        } else {
            $this->_scope = $scope;
        }

        return $this;
    }

    /**
     * @param string $baseUrl            URL of the Mautic instance
     * @param string $version            ['OAuth1a', ''OAuth2'']. 'OAuth2' is default value
     * @param string $clientKey
     * @param string $clientSecret
     * @param string $accessToken
     * @param string $accessTokenSecret
     * @param string $accessTokenExpires
     * @param string $callback
     * @param string $scope
     * @param string $refreshToken
     */
    public function setup(
        $baseUrl = null,
        $version = 'OAuth2',
        $clientKey = null,
        $clientSecret = null,
        $accessToken = null,
        $accessTokenSecret = null,
        $accessTokenExpires = null,
        $callback = null,
        $scope = null,
        $refreshToken = null,
    ) {
        $this->_client_id           = $clientKey;
        $this->_client_secret       = $clientSecret;
        $this->_access_token        = $accessToken;
        $this->_access_token_secret = $accessTokenSecret;
        $this->_callback            = $callback;

        if ($baseUrl) {
            if ('OAuth1a' == $version) {
                if (!$this->_access_token_url) {
                    $this->_access_token_url = $baseUrl.'/oauth/v1/access_token';
                }
                if (!$this->_request_token_url) {
                    $this->_request_token_url = $baseUrl.'/oauth/v1/request_token';
                }
                if (!$this->_authorize_url) {
                    $this->_authorize_url = $baseUrl.'/oauth/v1/authorize';
                }
            } else {
                if (!$this->_access_token_url) {
                    $this->_access_token_url = $baseUrl.'/oauth/v2/token';
                }
                if (!$this->_authorize_url) {
                    $this->_authorize_url = $baseUrl.'/oauth/v2/authorize';
                }
            }
        }

        if (!empty($scope)) {
            $this->setScope($scope);
        }

        if (!empty($accessToken)) {
            $this->setAccessTokenDetails(
                [
                    'access_token'        => $accessToken,
                    'access_token_secret' => $accessTokenSecret,
                    'expires'             => $accessTokenExpires,
                    'refresh_token'       => $refreshToken,
                ]
            );
        }
    }

    /**
     * Validate existing access token.
     *
     * @param bool $redirect
     *
     * @return bool
     *
     * @throws IncorrectParametersReturnedException
     */
    public function validateAccessToken($redirect = true)
    {
        $this->_do_not_redirect = !$redirect;
        $this->log('validateAccessToken()');

        // Check to see if token in session has expired
        if (!empty($this->_expires) && $this->_expires < time()) {
            $this->log('access token expired so reauthorize');

            if (strlen($this->_refresh_token) > 0) {
                // use a refresh token to get a new token
                return $this->requestAccessToken();
            }

            // Reauthorize
            $this->authorize($this->_scope);

            return false;
        }

        // Check for existing access token
        if ($this->_access_token) {
            $this->log('has access token');

            return true;
        }

        // Reauthorize if no token was found
        if (!$this->_access_token) {
            $this->log('access token empty so authorize');

            // OAuth flows
            if ($this->isOauth1()) {
                // OAuth 1.0
                $this->log('authorizing with OAuth1.0a spec');

                // Request token and authorize app
                if (!isset($_GET['oauth_token']) && !isset($_GET['oauth_verifier'])) {
                    $this->log('initializing authorization');

                    // Request token
                    $this->requestToken();
                    // Authorize token
                    $this->authorize();

                    return false;
                }

                // Request access token
                if ($_GET['oauth_token'] != $_SESSION['oauth']['token']) {
                    unset($_SESSION['oauth']['token'], $_SESSION['oauth']['token_secret']);

                    return false;
                }

                $this->requestAccessToken();
                unset($_SESSION['oauth']['token'], $_SESSION['oauth']['token_secret']);

                return true;
            }

            // OAuth 2.0
            $this->log('authorizing with OAuth2 spec');

            // Authorize app
            if (!isset($_GET['state']) && !isset($_GET['code'])) {
                $this->authorize($this->_scope);

                return false;
            }

            if ($this->_debug) {
                $_SESSION['oauth']['debug']['received_state'] = $_GET['state'];
            }

            // Request an access token
            if ($_GET['state'] != $_SESSION['oauth']['state']) {
                unset($_SESSION['oauth']['state']);

                return false;
            }

            unset($_SESSION['oauth']['state']);
            $this->requestAccessToken('POST', [], 'json');

            return true;
        }

        return false;
    }

    /**
     * Authorize app.
     *
     * @param string $scope_separator
     * @param null   $attach
     *
     * @throws AuthorizationRequiredException
     */
    protected function authorize(array $scope = [], $scope_separator = ',', $attach = null)
    {
        $authUrl = $this->_authorize_url;

        // Build authorization URL
        if ($this->isOauth1()) {
            // OAuth 1.0
            $authUrl .= '?oauth_token='.$_SESSION['oauth']['token'];

            if (!empty($this->_callback)) {
                $authUrl .= '&oauth_callback='.urlencode($this->_callback);
            }
        } else {
            // OAuth 2.0
            $authUrl .= '?client_id='.$this->_client_id.'&redirect_uri='.urlencode($this->_callback);
            $state                      = md5(time().mt_rand());
            $_SESSION['oauth']['state'] = $state;
            if ($this->_debug) {
                $_SESSION['oauth']['debug']['generated_state'] = $state;
            }

            $authUrl .= '&state='.$state.'&scope='.implode($scope_separator, $scope).$attach;
            $authUrl .= '&response_type='.$this->_redirect_type;
        }

        $this->log('redirecting to auth url '.$authUrl);

        // Redirect to authorization URL
        if (!$this->_do_not_redirect) {
            header('Location: '.$authUrl);
            exit;
        } else {
            throw new AuthorizationRequiredException($authUrl);
        }
    }

    /**
     * @param bool  $isPost
     * @param array $parameters
     *
     * @return array
     */
    protected function getQueryParameters($isPost, $parameters)
    {
        $query = parent::getQueryParameters($isPost, $parameters);

        if (isset($parameters['file'])) {
            // Mautic's OAuth2 server does not recognize multipart forms so we have to append the access token as part of the URL
            $query['access_token'] = $parameters['access_token'];
        }

        return $query;
    }

    /**
     * @return bool
     */
    protected function isOauth1()
    {
        return !empty($this->_request_token_url);
    }

    /**
     * @param string $url
     * @param string $method
     *
     * @return array
     */
    protected function prepareRequest($url, array $headers, array $parameters, $method, array $settings)
    {
        $includeCallback = (isset($settings['includeCallback'])) ? $settings['includeCallback'] : false;
        $includeVerifier = (isset($settings['includeVerifier'])) ? $settings['includeVerifier'] : false;

        // Set OAuth parameters/headers
        if ($this->isOauth1()) {
            // OAuth 1.0
            $this->log('making request using OAuth1.0a spec');

            // Get standard OAuth headers
            $oAuthHeaders = $this->getOauthHeaders($includeCallback);

            if ($includeVerifier && isset($_GET['oauth_verifier'])) {
                $oAuthHeaders['oauth_verifier'] = $_GET['oauth_verifier'];

                if ($this->_debug) {
                    $_SESSION['oauth']['debug']['oauth_verifier'] = $_GET['oauth_verifier'];
                }
            }

            // Add the parameters
            $oAuthHeaders                    = array_merge($oAuthHeaders, $parameters);
            $base_info                       = $this->buildBaseString($url, $method, $oAuthHeaders);
            $composite_key                   = $this->getCompositeKey();
            $oAuthHeaders['oauth_signature'] = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
            $headers[]                       = $this->buildAuthorizationHeader($oAuthHeaders);
            $headers[]                       = 'Expect:';

            if ($this->_debug) {
                $_SESSION['oauth']['debug']['basestring'] = $base_info;
                $_SESSION['oauth']['debug']['headers']    = $headers;
            }
        } else {
            // OAuth 2.0
            $this->log('making request using OAuth2 spec');

            $headers[] = 'Authorization: Bearer '.$this->_access_token;
        }

        return [$headers, $parameters];
    }

    /**
     * Request access token.
     *
     * @param string $method
     * @param string $responseType
     *
     * @return bool
     *
     * @throws IncorrectParametersReturnedException
     */
    protected function requestAccessToken($method = 'POST', array $params = [], $responseType = 'flat')
    {
        $this->log('requestAccessToken()');

        // Set OAuth flow parameters
        if ($this->isOauth1()) {
            // OAuth 1.0
            $this->log('using OAuth1.0a spec');

            $parameters = ['oauth_verifier' => $_GET['oauth_verifier']];
            $parameters = array_merge($parameters, $params);
        } else {
            // OAuth 2.0
            $this->log('using OAuth2 spec');

            $parameters = [
                'client_id'     => $this->_client_id,
                'redirect_uri'  => $this->_callback,
                'client_secret' => $this->_client_secret,
                'grant_type'    => 'authorization_code',
            ];

            if (isset($_GET['code'])) {
                $parameters['code'] = $_GET['code'];
            }

            if ($this->_refresh_token) {
                $this->log('Using refresh token');
                $parameters['grant_type']    = 'refresh_token';
                $parameters['refresh_token'] = $this->_refresh_token;
            }

            $parameters = array_merge($parameters, $params);
        }

        // Make the request
        $settings = [
            'responseType'    => $responseType,
            'includeCallback' => true,
            'includeVerifier' => true,
        ];

        $params = $this->makeRequest($this->_access_token_url, $parameters, $method, $settings);

        // Add the token and secret to session
        if (is_array($params)) {
            if ($this->isOauth1()) {
                // OAuth 1.0a
                if (isset($params['oauth_token']) && isset($params['oauth_token_secret'])) {
                    $this->log('access token set as '.$params['oauth_token']);

                    $this->_access_token         = $params['oauth_token'];
                    $this->_access_token_secret  = $params['oauth_token_secret'];
                    $this->_access_token_updated = true;

                    if ($this->_debug) {
                        $_SESSION['oauth']['debug']['tokens']['access_token']        = $params['oauth_token'];
                        $_SESSION['oauth']['debug']['tokens']['access_token_secret'] = $params['oauth_token_secret'];
                    }

                    return true;
                }
            } else {
                // OAuth 2.0
                if (isset($params['access_token']) && isset($params['expires_in'])) {
                    $this->log('access token set as '.$params['access_token']);

                    $this->_access_token         = $params['access_token'];
                    $this->_expires              = time() + $params['expires_in'];
                    $this->_token_type           = (isset($params['token_type'])) ? $params['token_type'] : null;
                    $this->_refresh_token        = (isset($params['refresh_token'])) ? $params['refresh_token'] : null;
                    $this->_access_token_updated = true;

                    if ($this->_debug) {
                        $_SESSION['oauth']['debug']['tokens']['access_token']  = $params['access_token'];
                        $_SESSION['oauth']['debug']['tokens']['expires_in']    = $params['expires_in'];
                        $_SESSION['oauth']['debug']['tokens']['token_type']    = $params['token_type'];
                        $_SESSION['oauth']['debug']['tokens']['refresh_token'] = $params['refresh_token'];
                    }

                    return true;
                }
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

    /**
     * Request token for OAuth1.
     *
     * @param string $responseType
     *
     * @throws IncorrectParametersReturnedException
     */
    protected function requestToken($responseType = 'flat')
    {
        $this->log('requestToken()');

        // Make the request
        $settings = [
            'responseType'    => $responseType,
            'includeCallback' => true,
            'includeVerifier' => false,
        ];
        $params = $this->makeRequest($this->_request_token_url, [], 'POST', $settings);

        // Add token and secret to the session
        if (is_array($params) && isset($params['oauth_token']) && isset($params['oauth_token_secret'])) {
            $this->log('token set as '.$params['oauth_token']);

            $_SESSION['oauth']['token']        = $params['oauth_token'];
            $_SESSION['oauth']['token_secret'] = $params['oauth_token_secret'];

            if ($this->_debug) {
                $_SESSION['oauth']['debug']['token']        = $params['oauth_token'];
                $_SESSION['oauth']['debug']['token_secret'] = $params['oauth_token_secret'];
            }
        } else {
            // Throw exception if the required parameters were not found
            $this->log('request did not return oauth tokens');

            if ($this->_debug) {
                $_SESSION['oauth']['debug']['response'] = $params;
            }

            if (is_array($params)) {
                if (isset($params['errors'])) {
                    $response = $params['errors'];
                } else {
                    $response = '???';
                }
            } else {
                $response = $params;
            }

            throw new IncorrectParametersReturnedException('Incorrect access token parameters returned: '.$response);
        }
    }

    /**
     * Build header for OAuth 1 authorization.
     *
     * @param array $oauth
     *
     * @return string
     */
    private function buildAuthorizationHeader($oauth)
    {
        $r      = 'Authorization: OAuth ';
        $values = $this->normalizeParameters($oauth);
        $r .= str_replace('&', ', ', $values);

        return $r;
    }

    /**
     * Build base string for OAuth 1 signature signing.
     *
     * @param string $baseURI
     * @param string $method
     * @param array  $params
     *
     * @return string
     */
    private function buildBaseString($baseURI, $method, $params)
    {
        $r = $this->normalizeParameters($params);

        return $method.'&'.$this->encode($baseURI).'&'.$this->encode($r);
    }

    /**
     * Returns an encoded string according to the RFC3986.
     *
     * @param string $string
     *
     * @return string
     */
    private function encode($string)
    {
        return str_replace('%7E', '~', rawurlencode($string));
    }

    /**
     * OAuth1.0 nonce generator.
     *
     * @param int $bits
     *
     * @return string
     */
    private function generateNonce($bits = 64)
    {
        $result          = '';
        $accumulatedBits = 0;
        $random          = mt_getrandmax();

        for ($totalBits = 0; 0 != $random; $random >>= 1) {
            ++$totalBits;
        }

        $usableBits = intval($totalBits / 8) * 8;

        while ($accumulatedBits < $bits) {
            $bitsToAdd = min($totalBits - $usableBits, $bits - $accumulatedBits);
            if (0 != $bitsToAdd % 4) {
                // add bits in whole increments of 4
                $bitsToAdd += 4 - $bitsToAdd % 4;
            }

            // isolate leftmost $bits_to_add from mt_rand() result
            $moreBits = mt_rand() & ((1 << $bitsToAdd) - 1);

            // format as hex (this will be safe)
            $format_string = '%0'.($bitsToAdd / 4).'x';
            $result .= sprintf($format_string, $moreBits);
            $accumulatedBits += $bitsToAdd;
        }

        return $result;
    }

    /**
     * Get composite key for OAuth 1 signature signing.
     *
     * @return string
     */
    private function getCompositeKey()
    {
        if (isset($this->_access_token_secret) && strlen($this->_access_token_secret) > 0) {
            $composite_key = $this->encode($this->_client_secret).'&'.$this->encode($this->_access_token_secret);
        } elseif (isset($_SESSION['oauth']['token_secret'])) {
            $composite_key = $this->encode($this->_client_secret).'&'.$this->encode($_SESSION['oauth']['token_secret']);
        } else {
            $composite_key = $this->encode($this->_client_secret).'&';
        }

        return $composite_key;
    }

    /**
     * Get OAuth 1.0 Headers.
     *
     * @param bool $includeCallback
     *
     * @return array
     */
    private function getOauthHeaders($includeCallback = false)
    {
        $oauth = [
            'oauth_consumer_key'     => $this->_client_id,
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_version'          => '1.0',
        ];

        if (isset($this->_access_token)) {
            $oauth['oauth_token'] = $this->_access_token;
        } elseif (isset($_SESSION['oauth']['token'])) {
            $oauth['oauth_token'] = $_SESSION['oauth']['token'];
        }

        if ($includeCallback) {
            $oauth['oauth_callback'] = $this->_callback;
        }

        return $oauth;
    }

    /**
     * Normalize parameters.
     *
     * @param array  $parameters
     * @param string $parentKey
     *
     * @return string
     */
    private function normalizeParameters($parameters, $parentKey = '')
    {
        if (empty($parameters)) {
            return '';
        }

        $normalized = [];
        foreach ($parameters as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if ('' !== $parentKey) {
                // Multidimensional array; using foo=bar&foo=baz rather than foo[bar]=baz&foo[baz]=bar
                $key = $parentKey;
            }

            if (is_array($value)) {
                $normalized[] = $this->normalizeParameters($value, $key);
            } else {
                $normalized[] = $this->encode($key).'='.$this->encode($value);
            }
        }

        $normalized = array_filter($normalized);
        sort($normalized, SORT_STRING);

        return implode('&', $normalized);
    }
}
