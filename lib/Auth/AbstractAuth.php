<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\Auth;

use Mautic\Exception\UnexpectedResponseFormatException;
use Mautic\Response;

/**
 * Class AbstractAuth.
 */
abstract class AbstractAuth implements AuthInterface
{
    /**
     * If set to true, $_SESSION['debug'] will be populated.
     *
     * @var bool
     */
    protected $_debug = false;

    /**
     * Holds string of HTTP response headers.
     *
     * @var string
     */
    protected $_httpResponseHeaders;

    /**
     * Holds array of HTTP response CURL info.
     *
     * @var array
     */
    protected $_httpResponseInfo;

    /**
     * Timeout for a cURL request.
     *
     * @var int
     */
    protected $_curlTimeout = null;

    /**
     * @param string $url
     * @param string $method
     *
     * @return mixed
     */
    abstract protected function prepareRequest($url, array $headers, array $parameters, $method, array $settings);

    /**
     * Enables debug mode.
     *
     * @return $this
     */
    public function enableDebugMode()
    {
        $this->_debug = true;

        return $this;
    }

    /**
     * Returns $_SESSION['oauth']['debug'] if $this->_debug = true.
     *
     * @return array
     */
    public function getDebugInfo()
    {
        return ($this->_debug && !empty($_SESSION['oauth']['debug'])) ? $_SESSION['oauth']['debug'] : [];
    }

    /**
     * Returns array of HTTP response headers.
     *
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->parseHeaders($this->_httpResponseHeaders);
    }

    /**
     * Returns array of HTTP response headers.
     *
     * @return array
     */
    public function getResponseInfo()
    {
        return $this->_httpResponseInfo;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedResponseFormatException|Exception
     */
    public function makeRequest($url, array $parameters = [], $method = 'GET', array $settings = [])
    {
        $this->log('makeRequest('.$url.', '.http_build_query($parameters).', '.$method.',...)');

        list($url, $parameters) = $this->separateUrlParams($url, $parameters);

        // make sure $method is capitalized for congruency
        $method  = strtoupper($method);
        $headers = (isset($settings['headers']) && is_array($settings['headers'])) ? $settings['headers'] : [];

        list($headers, $parameters) = $this->prepareRequest($url, $headers, $parameters, $method, $settings);

        // Set default CURL options
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER         => true,
        ];

        if (null !== $this->_curlTimeout) {
            $options[CURLOPT_TIMEOUT] = $this->_curlTimeout;
        }

        // CURLOPT_FOLLOWLOCATION cannot be activated when an open_basedir is set
        $options[CURLOPT_FOLLOWLOCATION] = (ini_get('open_basedir')) ? false : true;

        // Set custom REST method if not GET or POST
        if (!in_array($method, ['GET', 'POST'])) {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }

        // Set post fields for POST/PUT/PATCH requests
        $isPost = false;
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $isPost = true;
            // Set file to upload
            // Sending file data requires an array to set
            // the Content-Type header to multipart/form-data
            if (!empty($parameters['file']) && file_exists($parameters['file'])) {
                $options[CURLOPT_INFILESIZE] = filesize($parameters['file']);
                $parameters['file']          = $this->createCurlFile($parameters['file']);
                $headers[]                   = 'Content-Type: multipart/form-data';
            } else {
                $parameters = json_encode($parameters);
                $headers[]  = 'Content-Type: application/json';
            }

            $options[CURLOPT_POST]       = true;
            $options[CURLOPT_POSTFIELDS] = $parameters;

            $this->log('Posted parameters = '.print_r($parameters, true));
        }

        $query = $this->getQueryParameters($isPost, $parameters);
        $this->log('Query parameters = '.print_r($query, true));

        // Create a query string for GET/DELETE requests
        if (count($query) > 0) {
            $queryGlue = false === strpos($url, '?') ? '?' : '&';
            $url       = $url.$queryGlue.http_build_query($query, '', '&');
            $this->log('URL updated to '.$url);
        }

        // Set the URL
        $options[CURLOPT_URL] = $url;

        $headers[]                   = 'Accept: application/json';
        $options[CURLOPT_HTTPHEADER] = $headers;

        // Make CURL request
        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $response = new Response(curl_exec($curl), curl_getinfo($curl));

        $this->_httpResponseHeaders = $response->getHeaders();
        $this->_httpResponseInfo    = $response->getInfo();

        curl_close($curl);

        if ($this->_debug) {
            $_SESSION['oauth']['debug']['info']            = $response->getInfo();
            $_SESSION['oauth']['debug']['returnedHeaders'] = $response->getHeaders();
            $_SESSION['oauth']['debug']['returnedBody']    = $response->getBody();
        }

        // Handle zip file response
        if ($response->isZip()) {
            $temporaryFilePath = isset($settings['temporaryFilePath']) ? $settings['temporaryFilePath'] : sys_get_temp_dir();

            return $response->saveToFile($temporaryFilePath);
        } else {
            return $response->getDecodedBody();
        }
    }

    /**
     * @deprecated 2.6.0 to be removed in 3.0; use createCurlFile instead
     *
     * @param string $filename
     * @param string $mimetype
     * @param string $postname
     *
     * @return \CURLFile|string
     */
    protected function crateCurlFile($filename, $mimetype = '', $postname = '')
    {
        return $this->createCurlFile($filename, $mimetype, $postname);
    }

    /**
     * Build the CURL file based on PHP version.
     *
     * @param string $filename
     * @param string $mimetype
     * @param string $postname
     *
     * @return string|\CURLFile
     */
    protected function createCurlFile($filename, $mimetype = '', $postname = '')
    {
        if (!function_exists('curl_file_create')) {
            // For PHP < 5.5
            return "@$filename;filename="
                .($postname ?: basename($filename))
                .($mimetype ? ";type=$mimetype" : '');
        }

        // For PHP >= 5.5
        return curl_file_create($filename, $mimetype, $postname);
    }

    /**
     * @param bool  $isPost
     * @param array $parameters
     *
     * @return array
     */
    protected function getQueryParameters($isPost, $parameters)
    {
        return ($isPost) ? [] : (array) $parameters;
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
     * Build the HTTP response array out of the headers string.
     *
     * @param string $headersStr
     *
     * @return array
     */
    protected function parseHeaders($headersStr)
    {
        $headersArr  = [];
        $headersHlpr = explode("\r\n", $headersStr);

        foreach ($headersHlpr as $header) {
            $pos = strpos($header, ':');
            if (false === $pos) {
                $headersArr[] = trim($header);
            } else {
                $headersArr[trim(substr($header, 0, $pos))] = trim(substr($header, $pos + 1));
            }
        }

        return $headersArr;
    }

    /**
     * Separates parameters from base URL.
     *
     * @param string $url
     * @param array  $params
     *
     * @return array
     */
    protected function separateUrlParams($url, $params)
    {
        $a = parse_url($url);

        if (!empty($a['query'])) {
            parse_str($a['query'], $qparts);
            $cleanParams = [];
            foreach ($qparts as $k => $v) {
                $cleanParams[$k] = $v ? $v : '';
            }
            $params   = array_merge($params, $cleanParams);
            $urlParts = explode('?', $url, 2);
            $url      = $urlParts[0];
        }

        return [$url, $params];
    }

    /**
     * Set the timeout for a cURL request.
     *
     * @param int $timeout
     */
    public function setCurlTimeout($timeout)
    {
        $this->_curlTimeout = $timeout;
    }
}
