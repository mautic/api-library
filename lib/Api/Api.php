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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Base API class
 */
class Api implements LoggerAwareInterface
{
    /**
     * Common endpoint for this API
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Base URL for API endpoints
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * @var AuthInterface
     */
    private $auth;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AuthInterface $auth
     * @param string        $baseUrl
     */
    public function __construct(AuthInterface $auth, $baseUrl = '')
    {
        $this->auth    = $auth;
        $this->setBaseUrl($baseUrl);
    }

    /**
     * Get the logger.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        // If a logger hasn't been set, use NullLogger
        if (!($this->logger instanceof LoggerInterface)) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set the base URL for API endpoints
     *
     * @param string $url
     *
     * @return $this
     */
    public function setBaseUrl($url)
    {
        if (strpos($url, -1) != '/') {
            $url .= '/';
        }
        
        if(substr($url,-4,4) != 'api/'){
            $url .= 'api/';
        }
        
        $this->baseUrl = $url;

        return $this;
    }

    /**
     * Returns a not supported error
     *
     * @param string $action
     *
     * @return array
     */
    protected function actionNotSupported($action)
    {
        return array(
            'error' => array(
                'code'    => 500,
                'message' => "$action is not supported at this time."
            )
        );
    }

    /**
     * Make the API request
     *
     * @param string $endpoint
     * @param array  $parameters
     * @param string $method
     *
     * @return array|mixed
     */
    public function makeRequest($endpoint, array $parameters = array(), $method = 'GET')
    {
        $url = $this->baseUrl.$endpoint;

        if (strpos($url, 'http') === false) {
            return array(
                'error' => array(
                    'code'    => 500,
                    'message' => sprintf(
                        'URL is incomplete.  Please use %s, set the base URL as the third argument to MauticApi::getContext(), or make $endpoint a complete URL.',
                        __CLASS__.'setBaseUrl()'
                    )
                )
            );
        }

        try {
            $response = $this->auth->makeRequest($url, $parameters, $method);

            $this->getLogger()->debug('API Response', array('response' => $response));

            if (!is_array($response)) {
                $this->getLogger()->warning($response);

                //assume an error
                return array(
                    'error' => array(
                        'code'    => 500,
                        'message' => $response
                    )
                );
            }

            if (isset($response['error']) && isset($response['error_description'])) {
                $message = $response['error'].': '.$response['error_description'];

                $this->getLogger()->warning($message);

                return array(
                    'error' => array(
                        'code'    => 403,
                        'message' => $message
                    )
                );
            }
        } catch (\Exception $e) {
            $this->getLogger()->error('Failed connecting to Mautic API: '.$e->getMessage(), array('trace' => $e->getTraceAsString()));

            return array(
                'error' => array(
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage()
                )
            );
        }

        //return the response if no error condition is met
        return $response;
    }

    /**
     * Get a single item
     *
     * @param int $id
     *
     * @return array|mixed
     */
    public function get($id)
    {
        return $this->makeRequest("{$this->endpoint}/$id");
    }

    /**
     * Get a list of items
     *
     * @param string $search
     * @param int    $start
     * @param int    $limit
     * @param string $orderBy
     * @param string $orderByDir
     * @param bool   $publishedOnly
     *
     * @return array|mixed
     */
    public function getList($search = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC', $publishedOnly = false)
    {
        $parameters = array();

        $args = array('search', 'start', 'limit', 'orderBy', 'orderByDir', 'publishedOnly');

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
     * @param string $search
     * @param int    $start
     * @param int    $limit
     * @param string $orderBy
     * @param string $orderByDir
     *
     * @return array|mixed
     */
    public function getPublishedList($search = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC')
    {
        return $this->getList($search, $start, $limit, $orderBy, $orderByDir, true);
    }

    /**
     * Create a new item (if supported)
     *
     * @param array $parameters
     *
     * @return array|mixed
     */
    public function create(array $parameters)
    {
        return $this->makeRequest($this->endpoint.'/new', $parameters, 'POST');
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

        return $this->makeRequest($this->endpoint.'/'.$id.'/edit', $parameters, $method);
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
        return $this->makeRequest($this->endpoint.'/'.$id.'/delete', array(), 'DELETE');
    }
}
