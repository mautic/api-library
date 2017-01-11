<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

/*
|--------------------------------------------------------------------------
| Basic Authentication Flow
|--------------------------------------------------------------------------
|
| use Mautic\Auth\ApiAuth;
|
| // ApiAuth->newAuth() will accept an array of Auth settings
| $settings = array(
|     'AuthMethod'       => 'BasicAuth' // Must be one of 'OAuth' or 'BasicAuth'
|     'userName'         => '',         // The username for authentication; Best practise would be to set up a new user for each external site
|     'password'         => '',         // Make this a Long passPhrase e.g. (Try.!wE4.And.*@ws4.Guess.!a4911.This.*-+1.Sucker.!)
|     'apiUrl'           => '',         // NOTE: Required for Unit tests; *must* contain a valid url
| );
|
| // Initiate the auth object
| $initAuth = new ApiAuth();
| $auth = $initAuth->newAuth($settings, $settings['AuthMethod']);
|
| // None of the following is required anymore!
| // However, class methods will respond approprietly if you use the old workflow
| //
| if ($usingOldWorkflow) {
|     try {
|         // BasicAuth will always return True if username and password supplied
|         if ($auth->validateAccessToken()) {
|             // This method will always return False!
|             if ($auth->accessTokenUpdated()) {
|                 // There are NO access tokens; returns an empty array
|                 $accessTokenData = $auth->getAccessTokenData();
|             }
|         }
|     } catch (Exception $e) {
|         // Do Error handling
|     }
| }
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
| array('error' => array(
|       'code'    => 403,
|       'message' => 'access_denied: OAuth2 authentication required' )
| )
|
*/

namespace Mautic\Auth;

use Mautic\Exception\UnexpectedResponseFormatException;
use Mautic\Exception\RequiredParameterMissingException;

/**
 * Basic Authentication Client mashed together by MarkLL
 */
class BasicAuth extends ApiAuth implements AuthInterface
{

    /**
     * Username or email, basically the Login Identifier
     *
     * @var string
     */
    private $userName;

    /**
     * Password associated with Username
     *
     * @var string
     */
    private $password;

    /**
     * If set to true, $_SESSION['debug'] will be populated
     *
     * @var bool
     */
    protected $_debug = false;

    /**
     * Holds string of HTTP response headers
     *
     * @var string
     */
    protected $_httpResponseHeaders;

    /**
     * Holds array of HTTP response CURL info
     *
     * @var array
     */
    protected $_httpResponseInfo;

    /**
     * @param string $userName              The username to use for Authentication *Required*
     * @param string $password              The Password to use                    *Required*
     *
     * @throws RequiredParameterMissingException
     */
    public function setup($userName, $password) {
        // we MUST have the username and password. No Blanks allowed!
        //
        // remove blanks else Empty doesn't work
        $userName = trim($userName);
        $password = trim($password);

        if (empty($userName) || empty($password)) {
            //Throw exception if the required parameters were not found
            $this->log('parameters did not include username and/or password');
            throw new RequiredParameterMissingException('One or more required parameters was not supplied. Both userName and password required!');
        }

        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * Enables debug mode
     *
     * @return $this
     */
    public function enableDebugMode()
    {
        $this->_debug = true;

        return $this;
    }

    /**
     * Returns $_SESSION['oauth']['debug'] if $this->_debug = true
     *
     * @return array
     */
    public function getDebugInfo()
    {
        return ($this->_debug && !empty($_SESSION['oauth']['debug'])) ? $_SESSION['oauth']['debug'] : array();
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthorized()
    {
        return $this->logRedundantUsage(
            __FUNCTION__, 
            // As long as there are credentials; we consider it Authorized
            !empty($this->userName) && !empty($this->password),
            'Not required for Basic Auth as username/password required at setup'
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedResponseFormatException
     *
     * @return string|array
     */
    public function makeRequest($url, array $parameters = array(), $method = 'GET', array $settings = array())
    {
        $this->log('makeRequest('.$url.', '.http_build_query($parameters).', '.$method.',...)');

        // Note: this removes ALL query parameters!
        list($url, $parameters) = $this->separateUrlParams($url, $parameters);

        //make sure $method is capitalized for congruency
        $method = strtoupper($method);

        $this->log('making request using Basic Authorization');

        //Set Basic Auth parameters/headers
        $headers = array($this->buildAuthorizationHeader(), 'Expect:');

        //Set default CURL options
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER         => true
        );

        // CURLOPT_FOLLOWLOCATION cannot be activated when an open_basedir is set
        if (ini_get('open_basedir')) {
            $options[CURLOPT_FOLLOWLOCATION] = false;
        } else {
            $options[CURLOPT_FOLLOWLOCATION] = true;
        }

        //Set custom REST method if not GET or POST
        if (!in_array($method, array('GET', 'POST'))) {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }

        //Set post fields for POST/PUT/PATCH requests
        $query = array();
        if (in_array($method, array('POST', 'PUT', 'PATCH'))) {
            // Set file to upload
            // Sending file data requires an array to set
            // the Content-Type header to multipart/form-data
            if (!empty($parameters['file']) && file_exists($parameters['file'])) {
                $options[CURLOPT_INFILESIZE] = filesize($parameters['file']);
                $parameters['file'] = $this->crateCurlFile($parameters['file']);
                array_merge($headers, array("Content-Type:multipart/form-data"));
                // Leaving parameters an Array will encode the data as multipart/form-data.

            } else {
                // passing a string will encode the data as application/x-www-form-urlencoded.
                $parameters = http_build_query($parameters, '', '&');
            }

            $options[CURLOPT_POST]       = true;
            $options[CURLOPT_POSTFIELDS] = $parameters; // either string or Array

            // This is just for logging
            if (is_array($parameters)) {
                $parameters = print_r($parameters, true);
            }
            $this->log('Posted parameters = '.$parameters);

        // It a GET so include it in the URL
        } else {
            $query = $parameters;
        }

        // Create a query string for GET/DELETE requests
        if (count($query) > 0) {
            // url is always stripped of it's query above
            $url       = $url.'?'.http_build_query($query);
            $this->log('URL updated to '.$url);
        }

        // Set the URL
        $options[CURLOPT_URL] = $url;

        // Always need to set CURL headers for Basic Auth requests
        $options[CURLOPT_HTTPHEADER] = $headers;

        // Make CURL request
        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $response                   = curl_exec($curl);
        $responseArray              = explode("\r\n\r\n", $response);
        $body                       = array_pop($responseArray);
        $this->_httpResponseHeaders = implode("\r\n\r\n", $responseArray);
        $this->_httpResponseInfo    = curl_getinfo($curl);

        curl_close($curl);

        if ($this->_debug) {
            $_SESSION['oauth']['debug']['info']            = $this->_httpResponseInfo;
            $_SESSION['oauth']['debug']['returnedHeaders'] = $this->_httpResponseHeaders;
            $_SESSION['oauth']['debug']['returnedBody']    = $body;
        }

        $responseGood = false;

        // Check to see if the response is JSON
        $parsed = json_decode($body, true);

        if ($parsed === null) {
            if (strpos($body, '=') !== false) {
                parse_str($body, $parsed);
                $responseGood = true;
            }
        } else {
            $responseGood = true;
        }

        //Show error when http_code is not appropriate
        if (!in_array($this->_httpResponseInfo['http_code'], array(200, 201))) {
            if ($responseGood) {
                return $parsed;
            }

            throw new UnexpectedResponseFormatException($body);
        }

        return ($responseGood) ? $parsed : $body;
    }

    /**
     * Returns array of HTTP response headers
     *
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->parseHeaders($this->_httpResponseHeaders);
    }

    /**
     * Returns array of HTTP response headers
     *
     * @return array
     */
    public function getResponseInfo()
    {
        return $this->_httpResponseInfo;
    }

    /**
     * Validate existing access token; Hook for OAuth flow only
     *
     * @return bool
     */
    public function validateAccessToken()
    {
        return $this->logRedundantUsage(__FUNCTION__, true);
    }

   /**
     * Check if the access token was updated; Hook for OAuth flow only
     *
     * @return bool
     */
    public function accessTokenUpdated()
    {
        return $this->logRedundantUsage(__FUNCTION__, false);
    }

    /**
     * Returns an empty array as access token not required
     *
     * @return array
     */
    public function getAccessTokenData()
    {
        return $this->logRedundantUsage(__FUNCTION__, array());
    }

    /**
     * Build header for Basic Authentication
     *
     * @param $oauth
     *
     * @return string
     */
    private function buildAuthorizationHeader()
    {
        /*
        |--------------------------------------------------------------------------
        | Authorization Header
        |--------------------------------------------------------------------------
        |
        | Authorization is passed in the Header using Basic Authentication.
        |
        | Basically we take the username and password and seperate it with a 
        | colon (:) and base 64 encode it:
        |
        |   'Authorization: Basic username:password'
        |
        |   ==> with base64 encoding of the username and password
        |
        |   'Authorization: Basic dXNlcjpwYXNzd29yZA=='
        |
        */
        return 'Authorization: Basic ' . base64_encode($this->userName.':'.$this->password);
    }

    /**
     * Build the HTTP response array out of the headers string
     *
     * @param  string $headersStr
     *
     * @return array
     */
    protected function parseHeaders($headersStr)
    {
        $headersArr = array();
        $headersHlpr = explode("\r\n", $headersStr);

        foreach ($headersHlpr as $header) {
            $pos = strpos($header, ':');
            if ($pos === false) {
                $headersArr[] = trim($header);
            } else {
                $headersArr[trim(substr($header, 0, $pos))] = trim(substr($header, ($pos + 1)));
            }
        }

        return $headersArr;
    }

    /**
     * Build the CURL file based on PHP version
     *
     * @param  string $filename
     * @param  string $mimetype
     * @param  string $postname
     *
     * @return string|CURLFile
     */
    protected function crateCurlFile($filename, $mimetype = '', $postname = '')
    {
        if (!function_exists('curl_file_create')) {
            // For PHP < 5.5
            return "@$filename;filename="
                . ($postname ?: basename($filename))
                . ($mimetype ? ";type=$mimetype" : '');
        }

        // For PHP >= 5.5
        return curl_file_create($filename, $mimetype, $postname);
    }

    /**
     * @param string $message
     */
    protected function log($message)
    {
        if ($this->_debug) {
            $_SESSION['oauth']['debug']['flow'][date('m-d H:i:s')][] = $message;
        }
    }

    /**
     * Logs that usage is redundant and returns a valid response.
     *
     * @param string $function
     * @param string $value
     * @param string $message
     *
     */
    protected function logRedundantUsage($function, $value, $message = 'Not required for Basic Auth')
    {
        $this->log( $function .'() - '. $message);
        return $value;
    }

    /**
     * Separates parameters from base URL
     *
     * @return array
     */
    protected function separateUrlParams($url, $params)
    {
        $a = parse_url($url);

        if (!empty($a['query'])) {
            parse_str($a['query'], $qparts);
            foreach ($qparts as $k => $v) {
                $cleanParams[$k] = $v ?: '';
            }
            $params = array_merge($params, $cleanParams);
            $urlParts = explode('?', $url, 2);
            $url = $urlParts[0];
        }

        return array($url, $params);
    }
}
