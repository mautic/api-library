<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Api;

use Mautic\Auth\AuthInterface;
use Mautic\Exception\ContextNotFoundException;

/**
 * Base API class
 *
 * @package Mautic\Api
 */
class Api
{
    /**
     * Common endpoint for this API
     *
     * @var
     */
    protected $endpoint;

    /**
     * Base URL for API endpoints
     *
     * @var
     */
    protected $baseUrl;

    /**
     * @var AuthInterface $auth
     */
    private $auth;

    /**
     * @param AuthInterface $auth
     * @param string        $baseUrl
     */
    public function __construct(AuthInterface $auth, $baseUrl = '')
    {
        $this->auth    = $auth;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Set the base URL for API endpoints
     *
     * @param $url
     */
    public function setBaseUrl($url)
    {
        if (strpos($url, -1) != '/') {
            $url .= '/';
        }
        $this->baseUrl = $url;
    }

    /**
     * Returns a not supported error
     *
     * @param $action
     *
     * @return array
     */
    protected function actionNotSupported($action)
    {
        return array(
            'error' => array(
                'code' => 500,
                'message' => "$action is not supported at this time."
            )
        );
    }

    /**
     * Make the API request
     *
     * @param        $endpoint
     * @param array  $parameters
     * @param string $method
     *
     * @return array|mixed
     */
    public function makeRequest($endpoint, array $parameters = array(), $method = 'GET')
    {
        $url = $this->baseUrl . $endpoint;

        if (strpos($url, 'http') === false) {
            $response = array(
                'error' => array(
                    'code'    => 500,
                    'message' => 'URL is incomplete.  Please use $this->setBaseUrl(), set base URL via as the third argument to MauticApi::getContext(), or make $endpoint a complete URL.'
                )
            );
        } else {
            try {
                $response = $this->auth->makeRequest($url, $parameters, $method);

                if (!is_array($response)) {
                    //assume an error
                    $response = array(
                        'error' => array(
                            'code' => 500,
                            'message' => $response
                        )
                    );
                } elseif (isset($response['error'])) {
                    if (isset($response['error_description'])) {
                        $error = array(
                            'error' => array(
                                'code' => 403,
                                'message' => $response['error'] . ": " . $response['error_description']
                            )
                        );
                        $response = $error;
                    }
                }
            } catch (\Exception $e) {
                $response = array(
                    'error' => array(
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage()
                    )
                );
            }
        }

        return $response;
    }

    /**
     * Get a single item
     *
     * @param $id
     */
    public function get($id)
    {
        return $this->makeRequest("{$this->endpoint}/$id");
    }

    /**
     * Get a list of items
     *
     * @param string $filter
     * @param int    $start
     * @param int    $limit
     * @param string $orderBy
     * @param string $orderByDir
     * @param bool   $publishedOnly
     */
    public function getList($filter = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC', $publishedOnly = false)
    {
        $parameters = array();

        $args = array('filter', 'start', 'limit', 'orderBy', 'orderByDir', 'publishedOnly');

        foreach ($args as $arg) {
            if (!empty($$arg)) {
                $parameters[$arg] = $$arg;
            }
        }

        return $this->makeRequest($this->endpoint, $parameters);
    }

    /**
     * Proxy function to getList with $publishedOnly set to true
     *
     * @param string $filter
     * @param int    $start
     * @param int    $limit
     * @param string $orderBy
     * @param string $orderByDir
     *
     * @return array|mixed
     */
    public function getPublishedList($filter = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC')
    {
        return $this->getList($filter, $start, $limit, $orderBy, $orderByDir, true);
    }

    /**
     * Create a new item (if supported)
     *
     * @param array $parameters
     */
    public function create(array $parameters)
    {
        return $this->makeRequest($this->endpoint. '/new', $parameters, 'POST');
    }

    /**
     * Edit an item with option to create if it doesn't exist
     *
     * @param int   $id
     * @param array $parameters
     * @param bool  $createIfNotExists = false
     *
     * @return array|mixed
     */
    public function edit($id, array $parameters, $createIfNotExists = false)
    {
        $method = $createIfNotExists ? 'PUT' : 'PATCH';
        return $this->makeRequest($this->endpoint . '/' . $id . '/edit', $parameters, $method);
    }

    /**
     * Delete an item
     *
     * @param $id
     *
     * @return array|mixed
     */
    public function delete($id)
    {
        return $this->makeRequest($this->endpoint . '/' . $id . '/delete', array(), 'DELETE');
    }
}