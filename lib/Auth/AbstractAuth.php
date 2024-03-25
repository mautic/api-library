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

use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Mautic\Exception\UnexpectedResponseFormatException;
use Mautic\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractAuth.
 */
abstract class AbstractAuth implements AuthInterface
{
    protected ClientInterface $client;

    /**
     * If set to true, $_SESSION['debug'] will be populated.
     */
    protected bool $_debug = false;

    /**
     * Holds the HTTP response.
     */
    protected ResponseInterface $_httpResponse;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

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
        return array_map(
            static function ($values) {
                return $values[0];
            },
            $this->_httpResponse->getHeaders()
        );
    }

    /**
     * Returns the HTTP response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->_httpResponse;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedResponseFormatException|Exception
     */
    public function makeRequest($url, array $parameters = [], $method = 'GET', array $settings = [])
    {
        $this->log('makeRequest('.$url.', '.http_build_query($parameters).', '.$method.',...)');

        [$url, $parameters] = $this->separateUrlParams($url, $parameters);

        // Make sure $method is capitalized for congruency
        $method  = strtoupper($method);
        $headers = (isset($settings['headers']) && is_array($settings['headers'])) ? $settings['headers'] : [];

        [$headers, $parameters] = $this->prepareRequest($url, $headers, $parameters, $method, $settings);

        // Prepare parameters/body
        $body = null;
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            if (!empty($parameters['file']) && file_exists($parameters['file'])) {
                $elements = [];
                foreach ($parameters as $key => $value) {
                    $elements[] = [
                        'name'     => $key,
                        'contents' => 'file' === $key ? Utils::tryFopen($value, 'r+') : $value,
                    ];
                }

                $body      = new MultipartStream($elements);
                $headers[] = 'Content-Type: multipart/form-data; boundary='.$body->getBoundary();
            } else {
                $body      = Utils::streamFor(json_encode($parameters));
                $headers[] = 'Content-Type: application/json';
            }

            $this->log('Posted parameters = '.print_r(json_encode($parameters), true));
        }

        $query = $this->getQueryParameters(null !== $body, $parameters);
        $this->log('Query parameters = '.print_r($query, true));

        // Create a query string for GET/DELETE requests
        if (count($query) > 0) {
            $queryGlue = false === strpos($url, '?') ? '?' : '&';
            $url .= $queryGlue.http_build_query($query, '', '&');
            $this->log('URL updated to '.$url);
        }

        $headers[] = 'Accept: application/json';

        // Build request
        $request = new Request($method, $url);
        foreach ($headers as $header) {
            [$name, $value] = explode(':', $header, 2);
            $request        = $request->withHeader(trim($name), trim($value));
        }
        if ($body) {
            $request = $request->withBody($body);
        }

        // Send request
        $this->_httpResponse = $this->client->sendRequest($request);

        // Parse response
        $response = new Response($this->_httpResponse);

        if ($this->_debug) {
            $_SESSION['oauth']['debug']['returnedStatusCode'] = $response->getStatusCode();
            $_SESSION['oauth']['debug']['returnedHeaders']    = $response->getHeaders();
            $_SESSION['oauth']['debug']['returnedBody']       = $response->getBody();
        }

        // Handle zip file response
        if ($response->isZip()) {
            return $response->saveToFile($settings['temporaryFilePath'] ?? sys_get_temp_dir());
        }

        return $response->getDecodedBody();
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
}
