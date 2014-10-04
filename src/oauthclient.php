<?php

namespace Mautic\API;

//Ensure a session has been started
if(session_id() == '') {
    session_start();
}

/**
 * OAuth Client modified from https://code.google.com/p/simple-php-oauth/
 *
 * Class Oauth
 *
 */
class Oauth
{

    /**
     * @var string Consumer or client key
     */
    protected $_client_id;

    /**
     * @var string Consumer or client secret
     */
    protected $_client_secret;

    /**
     * @var string Callback or Redirect URL
     */
    protected $_callback;

    /**
     * @var string Access token returned by OAuth server
     */
    protected $_access_token;

    /**
     * @var string Access token secret returned by OAuth server
     */
    protected $_access_token_secret;

    /**
     * @var string Unix timestamp for when token expires
     */
    protected $_expires;

    /**
     * @var string OAuth2 redirect type
     */
    protected $_redirect_type = 'code';

    /**
     * @var string OAuth2 scope
     */
    protected $_scope = array();

    /**
     * @var string $_SESSION prefix to identify server being communicated with
     */
    protected $_prefix;

    /**
     * @var string Authorize URL
     */
    protected $_authorize_url;

    /**
     * @var string Access token URL
     */
    protected $_access_token_url;

    /**
     * @var string Request token URL for OAuth1
     */
    protected $_request_token_url;

    /**
     * If set to true, $_SESSION[$this->_prefix] will not ever be wiped, and populated with debug information for review
     *
     * @var bool
     */
    protected $_debug = false;

    /**
     *
     *
     * @param $client_id        string  Consumer key for OAuth1 or Client key for OAuth2
     * @param $client_secret    string  Consumer secret for OAuth1 or Client key for OAuth2
     * @param $callback         string  Callback URL for OAuth1 or Redirect URL for OAuth2
     */
    public function __construct ($client_id, $client_secret, $callback, $prefix = 'oauth')
    {
        $this->_client_id     = $client_id;
        $this->_client_secret = $client_secret;
        $this->_callback      = $callback;
        $this->_prefix        = $prefix;
    }

    /**
     * Set authorization URL
     *
     * @param $url
     */
    public function setAuthorizeUrl ($url)
    {
        $this->_authorize_url = $url;
    }

    /**
     * Set request token URL
     *
     * @param $url
     */
    public function setRequestTokenUrl ($url)
    {
        $this->_request_token_url = $url;
    }

    /**
     * Set access token URL
     *
     * @param $url
     */
    public function setAccessTokenUrl ($url)
    {
        $this->_access_token_url = $url;
    }

    /**
     * Set redirect type for OAuth2
     *
     * @param $type
     */
    public function setRedirectType ($type)
    {
        $this->_redirect_type = $type;
    }

    /**
     * Set OAuth2 scope
     *
     * @param array|string $scope
     */
    public function setScope ($scope)
    {
        if (!is_array($scope)) {
            $this->_scope = explode(',', $scope);
        } else {
            $this->_scope = $scope;
        }
    }

    /**
     * Set an existing/already retrieved access token
     *
     * @param      $access_token
     * @param null $access_token_secret
     * @param null $expires
     */
    public function setAccessToken ($access_token, $access_token_secret = null, $expires = null)
    {
        $this->_access_token        = $access_token;
        $this->_access_token_secret = $access_token_secret;
        $this->_expires             = $expires;
    }

    /**
     * Returns access token.  $_SESSION[$this->_prefix] will be unset unless debug mode is enabled
     *
     * @return array($accessToken, $accessTokenSecret, $expiration)
     */
    public function getAccessToken()
    {
        if (!$this->_debug) {
            //unset the $_SESSION and allow the app to reset the auth token if required
            unset($_SESSION[$this->_prefix]);
        }
        return array($this->_access_token, $this->_access_token_secret, $this->_expires);
    }

    /**
     * Enables debug mode
     */
    public function enableDebugMode()
    {
        $this->_debug = true;
    }

    /**
     * Returns $_SESSION[$this->_prefix] if $this->_debug = true
     *
     * @return array
     */
    public function getDebugInfo()
    {
        return ($this->_debug && !empty($_SESSION[$this->_prefix])) ? $_SESSION[$this->_prefix] : array();
    }

    /**
     * Validate existing access token
     *
     * @return bool
     * @throws \Exception
     */
    public function validateAccessToken ()
    {
        //Check to see if token in session has expired
        if (isset($_SESSION[$this->_prefix]['expires']) && $_SESSION[$this->_prefix]['expires'] < time()) {
            if ($this->_debug) {
                $previous = $_SESSION[$this->_prefix];
            }

            unset($_SESSION[$this->_prefix]);

            if ($this->_debug) {
                $_SESSION[$this->_prefix]['previous'] = $previous;
            }

            //Reauthorize
            $this->authorize($this->_scope);

            return false;
        }

        //Check for existing access token
        if (isset($_SESSION[$this->_prefix]['access_token']) || (isset($this->_access_token) && strlen($this->_access_token) > 0)) {
            $this->_access_token = $_SESSION[$this->_prefix]['access_token'];
            if (isset($_SESSION[$this->_prefix]['access_token_secret'])) {
                $this->_access_token_secret = $_SESSION[$this->_prefix]['access_token_secret'];
            }
            if (isset($_SESSION[$this->_prefix]['expires'])) {
                $this->_expires = $_SESSION[$this->_prefix]['expires'];
            }

            return true;
        }

        //Reauthorize if no token was found
        if (!isset($this->_access_token) || strlen($this->_access_token) == 0) {

            //OAuth flows
            if (isset($this->_request_token_url) && strlen($this->_request_token_url) > 0) {
                //OAuth 1.0

                //Request token and authorize app
                if (!isset($_GET['oauth_token']) && !isset($_GET['oauth_verifier'])) {
                    //Request token
                    $this->requestToken();
                    //Authorize token
                    $this->authorize();

                    return false;
                } else {
                    //Request access token
                    if ($_GET['oauth_token'] != $_SESSION[$this->_prefix]['token']) {
                        if ($this->_debug) {
                            $_SESSION[$this->_prefix]['previous']['token'] = $_SESSION[$this->_prefix]['token'];
                            $_SESSION[$this->_prefix]['previous']['token_secret'] = $_SESSION[$this->_prefix]['token_secret'];
                        }

                        unset($_SESSION[$this->_prefix]['token'], $_SESSION[$this->_prefix]['token_secret']);

                        return false;
                    } else {
                        $this->requestAccessToken();

                        if ($this->_debug) {
                            $_SESSION[$this->_prefix]['previous']['token'] = $_SESSION[$this->_prefix]['token'];
                            $_SESSION[$this->_prefix]['previous']['token_secret'] = $_SESSION[$this->_prefix]['token_secret'];
                        }

                        unset($_SESSION[$this->_prefix]['token'], $_SESSION[$this->_prefix]['token_secret']);

                        return true;
                    }
                }
            } else {
                //OAuth 2.0

                //Authorize app
                if (!isset($_GET['state']) && !isset($_GET['code'])) {
                    $this->authorize($this->_scope);

                    return false;
                } else {
                    //Request an access token
                    if ($_GET['state'] != $_SESSION[$this->_prefix]['state']) {
                        if ($this->_debug) {
                            $_SESSION[$this->_prefix]['previous']['state'] = $_SESSION[$this->_prefix]['state'];
                        }
                        unset($_SESSION[$this->_prefix]['state']);
                        return false;
                    } else {
                        if ($this->_debug) {
                            $_SESSION[$this->_prefix]['previous']['state'] = $_SESSION[$this->_prefix]['state'];
                        }
                        unset($_SESSION[$this->_prefix]['state']);
                        $this->requestAccessToken('GET', array(), 'json', array('access_token', 'expires_in'));

                        return true;
                    }
                }
            }
        }
    }

    /**
     * Request token for OAuth1
     *
     * @param string $returnType
     * @param array  $values
     *
     * @throws \Exception
     */
    protected function requestToken ($returnType = 'flat', array $values = array('oauth_token', 'oauth_token_secret'))
    {
        //Make the request
        $response = $this->makeRequest($this->_request_token_url, 'POST', array(), $returnType, true);

        //Get the parameters returned by request
        $params = $this->getParameters($response, $returnType);

        //Add token and secret to the session
        if (is_array($params) && isset($params[$values[0]]) && isset($params[$values[1]])) {
            $_SESSION[$this->_prefix]['token']        = $params[$values[0]];
            $_SESSION[$this->_prefix]['token_secret'] = $params[$values[1]];
        } else {
            //Throw exception if the required parameters were not found

            $s = array();
            if (is_array($params)) {
                foreach ($params as $k => $v) {
                    $s[] = $k . '=' . $v;
                }
                $response = implode('&', $s);
            } else {
                $response = $params;
            }
            throw new \Exception('incorrect access token parameters returned: ' . $response);
        }
    }

    /**
     * Request access token
     *
     * @param string $method
     * @param array  $params
     * @param string $returnType
     * @param array  $values
     *
     * @throws \Exception
     */
    protected function requestAccessToken ($method = 'GET', array $params = array(), $returnType = 'flat', array $values = array('oauth_token', 'oauth_token_secret'))
    {
        //Set OAuth flow parameters
        if (isset($this->_request_token_url) && strlen($this->_request_token_url) > 0) {
            //OAuth 1.0
            $parameters = array('oauth_verifier' => $_GET['oauth_verifier']);
            $parameters = array_merge($parameters, $params);
        } else {
            //OAuth 2.0
            $parameters = array(
                'client_id'     => $this->_client_id,
                'redirect_uri'  => $this->_callback,
                'client_secret' => $this->_client_secret,
                'code'          => $_GET['code'],
                'grant_type'    => 'authorization_code'
            );
            $parameters = array_merge($parameters, $params);
        }

        //Make the request
        $response = $this->makeRequest($this->_access_token_url, $method, $parameters, $returnType, true, true);

        //Get parameters from response
        $params = $this->getParameters($response, $returnType);

        //Add the token and secret to session
        if (is_array($params) && isset($params[$values[0]]) && isset($params[$values[1]])) {
            if (isset($this->_request_token_url) && strlen($this->_request_token_url) > 0) {
                $_SESSION[$this->_prefix]['access_token']        = $params[$values[0]];
                $_SESSION[$this->_prefix]['access_token_secret'] = $params[$values[1]];
            } else {
                $_SESSION[$this->_prefix]['access_token'] = $params[$values[0]];
                $_SESSION[$this->_prefix]['expires']      = time() + $params[$values[1]];
            }
        } else {
            //Throw exception if required parameters were not found
            $s = array();
            if (is_array($params)) {
                foreach ($params as $k => $v) {
                    $s[] = $k . '=' . $v;
                }
                $response = implode('&', $s);
            } else {
                $response = $params;
            }
            throw new \Exception('incorrect access token parameters returned: ' . $response);
        }
    }

    /**
     * Authorize app
     *
     * @param array  $scope
     * @param string $scope_separator
     * @param null   $attach
     */
    protected function authorize (array $scope = array(), $scope_seperator = ',', $attach = null)
    {
        //Build authorization URL
        if (isset($this->_request_token_url) && strlen($this->_request_token_url) > 0) {
            //OAuth 1.0
            $this->_authorize_url .= '?oauth_token=' . $_SESSION[$this->_prefix]['token'];
        } else {
            //OAuth 2.0
            $this->_authorize_url .= '?client_id=' . $this->_client_id . '&redirect_uri=' . $this->_callback;
            $state                             = md5(time() . mt_rand());
            $_SESSION[$this->_prefix]['state'] = $state;
            $this->_authorize_url .= '&state=' . $state . '&scope=' . implode($scope_seperator, $scope) . $attach;
            $this->_authorize_url .= '&response_type=' . $this->_redirect_type;
        }

        //Redirect to authorization URL
        header('Location: ' . $this->_authorize_url);
        exit;
    }


    /**
     * Make a request to API/OAuth server
     *
     * @param        $url
     * @param string $method
     * @param array  $parameters
     * @param string $returnType
     * @param bool   $includeCallback
     * @param bool   $includeVerifier
     *
     * @return mixed
     * @throws \Exception
     */
    public function makeRequest ($url, $method = 'GET', array $parameters = array(), $returnType = 'json', $includeCallback = false, $includeVerifier = false)
    {
        //make sure $method is capitalized for congruency
        $method = strtoupper($method);

        //Set OAuth parameters/headers
        if (isset($this->_request_token_url) && strlen($this->_request_token_url) > 0) {
            //OAuth 1.0

            //Get standard OAuth headers
            $headers = $this->getOauthHeaders($includeCallback);

            if ($includeVerifier && isset($_GET['oauth_verifier'])) {
                $headers['oauth_verifier'] = $_GET['oauth_verifier'];
            }

            //Add the parameters
            $headers                                = array_merge($headers, $parameters);
            $base_info                              = $this->buildBaseString($url, $method, $headers);
            $composite_key                          = $this->getCompositeKey();
            $headers['oauth_signature']             = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
            $_SESSION[$this->_prefix]['basestring'] = $base_info;
            $_SESSION[$this->_prefix]['headers']    = $headers;
            $header                                 = array($this->buildAuthorizationHeader($headers), 'Expect:');
        } else {
            //OAuth 2.0
            if (isset($_SESSION[$this->_prefix]['access_token'])) {
                $parameters['access_token'] = $_SESSION[$this->_prefix]['access_token'];
            }
        }

        //Create a querystring for GET/DELETE requests
        if (count($parameters) > 0 && in_array($method, array('GET', 'DELETE')) && strpos($url, '?') === false) {
            $url = $url . '?' . http_build_query($parameters);
        }

        //Set default CURL options
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true
        );

        //Set CURL headers for oauth 1.0 requests
        if (isset($this->_request_token_url) && strlen($this->_request_token_url) > 0) {
            $options[CURLOPT_HTTPHEADER] = $header;
        }

        //Set custom REST method if not GET or POST
        if (!in_array($method, array('GET', 'POST'))) {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }

        //Set post fields for POST/PUT/PATCH requests
        if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
            $options[CURLOPT_POST]       = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
        }

        //Make CURL request
        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $response      = curl_exec($curl);
        $responseArray = explode("\r\n\r\n", $response);
        $body          = array_pop($responseArray);
        $header        = implode("\r\n\r\n", $responseArray);

        if ($this->_debug) {
            $info = curl_getinfo($curl);
        }

        curl_close($curl);

        if ($this->_debug) {
            $_SESSION[$this->_prefix]['info']            = $info;
            $_SESSION[$this->_prefix]['returnedHeaders'] = $header;
            $_SESSION[$this->_prefix]['returnedBody']    = $body;
        }

        //Show error when http_code is not appropriate
        if (!in_array($info['http_code'], array(200, 201))) {
            if (!$this->_debug) {
                unset($_SESSION[$this->_prefix]);
            }

            //Check to see if the response is JSON
            $parsed = json_decode($body, true);

            if ($parsed) {
                return $parsed;
            } else {
                throw new \Exception($body);
            }
        }

        //Return json decoded array
        if ($returnType == 'json') {
            $parsed = json_decode($body, true);

            return (!$parsed) ? $body : $parsed;
        } elseif ($returnType == 'xml') {
            $parsed = json_decode(json_encode(simplexml_load_string($body)), true);

            return (!$parsed) ? $body : $parsed;
        } else {
            return $body;
        }
    }

    /**
     * Parses parameters from request response
     *
     * @param $response
     * @param $returnType
     *
     * @return array
     */
    private function getParameters ($response, $returnType)
    {
        if ($returnType != 'json' && strpos($response, 'length=') === false && strpos($response, 'xdebug-var-dump') === false) {
            $r      = explode('&', $response);
            $params = array();
            foreach ($r as $v) {
                $param             = explode('=', $v);
                $params[$param[0]] = $param[1];
            }
        } else {
            $params = $response;
        }

        return $params;
    }

    /**
     * Get composite key for OAuth 1 signature signing
     *
     * @return string
     */
    private function getCompositeKey ()
    {
        if (isset($this->_access_token_secret) && strlen($this->_access_token_secret) > 0) {
            $composite_key = $this->encode($this->_client_secret) . '&' . $this->encode($this->_access_token_secret);
        } else if (isset($_SESSION[$this->_prefix]['token_secret'])) {
            $composite_key = $this->encode($this->_client_secret) . '&' . $this->encode($_SESSION[$this->_prefix]['token_secret']);
        } else {
            $composite_key = $this->encode($this->_client_secret) . '&';
        }

        return $composite_key;
    }

    /**
     * Get OAuth 1.0 Headers
     *
     * @param bool $includeCallback
     *
     * @return array
     */
    private function getOauthHeaders ($includeCallback = false)
    {
        $oauth = array(
            'oauth_consumer_key'     => $this->_client_id,
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_version'          => '1.0'
        );
        if (isset($this->_access_token)) {
            $oauth['oauth_token'] = $this->_access_token;
        } else if (isset($_SESSION[$this->_prefix]['token'])) {
            $oauth['oauth_token'] = $_SESSION[$this->_prefix]['token'];
        }
        if ($includeCallback) {
            $oauth['oauth_callback'] = $this->_callback;
        }

        return $oauth;
    }

    /**
     * Build base string for OAuth 1 signature signing
     *
     * @param $baseURI
     * @param $method
     * @param $params
     *
     * @return string
     */
    private function buildBaseString ($baseURI, $method, $params)
    {
        $r = $this->normalizeParameters($params);
        return $method . '&' . $this->encode($baseURI) . '&' . $this->encode($r);
    }

    /**
     * Build header for OAuth 1 authorization
     *
     * @param $oauth
     *
     * @return string
     */
    private function buildAuthorizationHeader ($oauth)
    {
        $r      = 'Authorization: OAuth ';
        $values = $this->normalizeParameters($oauth, true, true);
        $r .= implode(', ', $values);

        return $r;
    }

    /**
     * Normalize parameters
     *
     * @param      $parameters
     * @param bool $encode
     * @param bool $returnarray
     *
     * @return string
     */
    private function normalizeParameters($parameters, $encode = false, $returnarray = false, $normalized = array(), $key = '')
    {
        //Sort by key
        ksort($parameters);

        foreach ($parameters as $k => $v) {
            if (is_array($v)) {
                $normalized = $this->normalizeParameters($v, $encode, true, $normalized, $k);
            } else {
                if ($key) {
                    //Multidimensional array; using foo=baz&foo=bar rather than foo[bar]=baz&foo[baz]=bar as this is
                    //what the server expects when creating the signature
                    $k = $key;
                }
                if ($encode) {
                    $normalized[] = $this->encode($k) . '=' . $this->encode($v);
                } else {
                    $normalized[] = $k . '=' . $v;
                }
            }
        }

        return $returnarray ? $normalized : implode('&', $normalized);
    }

    /**
     * Returns an encoded string according to the RFC3986.
     *
     * @param $string
     */
    private function encode($string)
    {
        return str_replace('%7E', '~', rawurlencode($string));
    }

    /**
     * OAuth1.0 nonce generator
     *
     * @param int $bits
     *
     * @return string
     */
    private function generateNonce($bits = 64)
    {
        $result = '';
        $accumulatedBits = 0;
        $random = mt_getrandmax();
        for($totalBits = 0; $random != 0; $random >>= 1) {
            ++$totalBits;
        }
        $usableBits = intval($totalBits / 8) * 8;

        while ($accumulatedBits < $bits) {
            $bitsToAdd = min($totalBits - $usableBits, $bits - $accumulatedBits);
            if ($bitsToAdd % 4 != 0) {
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
}