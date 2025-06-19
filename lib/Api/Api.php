<?php

/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Api;

use Mautic\Auth\ApiAuth;
use Mautic\Auth\AuthInterface;
use Mautic\QueryBuilder\QueryBuilder;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Base API class.
 */
class Api implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Used by unit testing to force use of BC endpoints.
     *
     * @var bool
     */
    public $bcTesting = false;

    /**
     * Common endpoint for this API.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Name of the array element where the list of items is.
     *
     * @var string
     */
    protected $listName;

    /**
     * Name of the array element where the item data is.
     *
     * @var string
     */
    protected $itemName;

    /**
     * Array of default endpoints supported by the context; if empty, all are supported.
     *
     * @var array
     */
    protected $endpointsSupported = [];

    /**
     * Array of deprecated endpoints to use if a response fails as a 404 with a previous version of Mautic.
     *
     * @var array
     */
    protected $bcRegexEndpoints = [];

    /**
     * Prevents from checking BC on a BC request.
     *
     * @var bool
     */
    protected $bcAttempt = false;

    /**
     * Base URL for API endpoints.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Array of available search commands.
     *
     * @var array
     */
    protected $searchCommands = [];

    /**
     * @var ApiAuth
     */
    private $auth;

    /**
     * @param string $baseUrl
     */
    public function __construct(AuthInterface $auth, $baseUrl = '')
    {
        $this->auth = $auth;
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
     * Get the array of available search commands.
     *
     * @return array
     */
    public function getSearchCommands()
    {
        return $this->searchCommands;
    }

    /**
     * Check if the search command is available.
     *
     * @param string $command
     *
     * @return bool
     */
    public function hasSearchCommand($command)
    {
        return in_array($command, $this->searchCommands);
    }

    /**
     * Returns list name.
     *
     * @return string
     */
    public function listName()
    {
        return $this->listName;
    }

    /**
     * Returns item name.
     *
     * @return string
     */
    public function itemName()
    {
        return $this->itemName;
    }

    /**
     * Set the base URL for API endpoints.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setBaseUrl($url)
    {
        if ('/' != substr($url, -1)) {
            $url .= '/';
        }

        if ('api/' != substr($url, -4, 4)) {
            $url .= 'api/';
        }

        $this->baseUrl = $url;

        return $this;
    }

    /**
     * Make the API request.
     *
     * @param string $endpoint
     * @param string $method
     *
     * @return array
     *
     * @throws \Exception
     */
    public function makeRequest($endpoint, array $parameters = [], $method = 'GET')
    {
        $response = [];

        // Validate if this endpoint has a BC url
        $bcEndpoint = null;
        if (!$this->bcAttempt) {
            if (!empty($this->bcRegexEndpoints)) {
                foreach ($this->bcRegexEndpoints as $regex => $bc) {
                    if (preg_match('@'.$regex.'@', $endpoint)) {
                        $this->bcAttempt = true;
                        $bcEndpoint      = preg_replace('@'.$regex.'@', $bc, $endpoint);

                        break;
                    }
                }
            }
        }

        $url      = $this->baseUrl.$endpoint;

        // Don't make the call if we're unit testing a BC endpoint
        if (!$bcEndpoint || !$this->bcTesting || ($bcEndpoint && $this->bcTesting && $this->bcAttempt)) {
            // Hack for unit testing to ensure this isn't being called due to a bad regex
            if ($this->bcTesting && !$this->bcAttempt) {
                $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                if (!is_array($this->bcTesting)) {
                    $this->bcTesting = [$this->bcTesting];
                }

                // The method is not catching the BC endpoint so fail the test
                if (in_array($bt[1]['function'], $this->bcTesting)) {
                    throw new \Exception($endpoint.' not matched in '.var_export($this->bcRegexEndpoints, true));
                }
            }
            $this->bcAttempt = false;

            if (false === strpos($url, 'http')) {
                $error = [
                    'code'    => 500,
                    'message' => sprintf(
                        'URL is incomplete.  Please use %s, set the base URL as the third argument to $MauticApi->newApi(), or make $endpoint a complete URL.',
                        __CLASS__.'setBaseUrl()'
                    ),
                ];
            } else {
                try {
                    $settings = [];
                    if (method_exists($this, 'getTemporaryFilePath')) {
                        $settings['temporaryFilePath'] = $this->getTemporaryFilePath();
                    }
                    $response = $this->auth->makeRequest($url, $parameters, $method, $settings);

                    $this->getLogger()->debug('API Response', ['response' => $response]);

                    if (!is_array($response)) {
                        $this->getLogger()->warning($response);

                        // assume an error
                        $error = [
                            'code'    => 500,
                            'message' => $response,
                        ];
                    }
                } catch (\Exception $e) {
                    $this->getLogger()->error('Failed connecting to Mautic API: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

                    $error = [
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage(),
                    ];
                }
            }

            if (!empty($error)) {
                return [
                    'errors' => [$error],
                ];
            } elseif (!empty($response['errors'])) {
                $this->getLogger()->error('Mautic API returned errors: '.var_export($response['errors'], true));
            }

            // Ensure a code is present in the error array
            if (!empty($response['errors'])) {
                foreach ($response['errors'] as $key => $error) {
                    if (!isset($response['errors'][$key]['code'])) {
                        $response['errors'][$key]['code'] = $this->auth->getResponse()->getStatusCode();
                    }
                }
            }
        }

        // Check for a 404 code and a BC URL then try again if applicable
        if ($bcEndpoint && ($this->bcTesting || (!empty($response['errors'][0]['code']) && 404 === (int) $response['errors'][0]['code']))) {
            $this->bcAttempt = true;

            return $this->makeRequest($bcEndpoint, $parameters, $method);
        }

        return $response;
    }

    /**
     * Returns HTTP response headers.
     *
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->auth->getResponseHeaders();
    }

    /**
     * Returns Mautic version from the HTTP response headers
     * (the header exists since Mautic 2.4.0).
     *
     * @return string|null if not known
     */
    public function getMauticVersion()
    {
        $headers = array_change_key_case($this->auth->getResponseHeaders(), CASE_LOWER);

        if (isset($headers['mautic-version'])) {
            return $headers['mautic-version'];
        }

        return null;
    }

    /**
     * Get a single item.
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
     * @param int $id
     *
     * @return array|bool
     */
    public function getCustom($id, array $select = [])
    {
        $supported = $this->isSupported('get');

        return (true === $supported) ? $this->makeRequest("{$this->endpoint}/$id", ['select' => $select]) : $supported;
    }

    /**
     * Get a list of items.
     *
     * @param string $search
     * @param int    $start
     * @param int    $limit
     * @param string $orderBy
     * @param string $orderByDir
     * @param bool   $publishedOnly
     * @param bool   $minimal
     *
     * @return array|mixed
     */
    public function getList($search = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC', $publishedOnly = false, $minimal = false)
    {
        $parameters = [
            'search'        => $search,
            'start'         => $start,
            'limit'         => $limit,
            'orderBy'       => $orderBy,
            'orderByDir'    => $orderByDir,
            'publishedOnly' => $publishedOnly,
            'minimal'       => $minimal,
        ];

        $parameters = array_filter($parameters);

        return $this->makeRequest($this->endpoint, $parameters);
    }

    /**
     * @param int $start
     * @param int $limit
     *
     * @return array|bool
     */
    public function getCustomList(QueryBuilder $queryBuilder, $start = 0, $limit = 0)
    {
        $parameters = [
            'select' => $queryBuilder->getSelect(),
            'where'  => $queryBuilder->getWhere(),
            'order'  => $queryBuilder->getOrder(),
            'start'  => $start,
            'limit'  => $limit,
        ];

        $parameters = array_filter($parameters);

        $supported = $this->isSupported('getList');

        return (true === $supported) ? $this->makeRequest($this->endpoint, $parameters) : $supported;
    }

    /**
     * Proxy function to getList with $publishedOnly set to true.
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
     * Create a new item (if supported).
     *
     * @return array|mixed
     */
    public function create(array $parameters)
    {
        $supported = $this->isSupported('create');

        return (true === $supported) ? $this->makeRequest($this->endpoint.'/new', $parameters, 'POST') : $supported;
    }

    /**
     * Create a batch of new items.
     *
     * @return array|mixed
     */
    public function createBatch(array $parameters)
    {
        $supported = $this->isSupported('createBatch');

        return (true === $supported) ? $this->makeRequest($this->endpoint.'/batch/new', $parameters, 'POST') : $supported;
    }

    /**
     * Edit an item with option to create if it doesn't exist.
     *
     * @param int  $id
     * @param bool $createIfNotExists = false
     *
     * @return array|mixed
     */
    public function edit($id, array $parameters, $createIfNotExists = false)
    {
        $method    = $createIfNotExists ? 'PUT' : 'PATCH';
        $supported = $this->isSupported('edit');

        return (true === $supported) ? $this->makeRequest($this->endpoint.'/'.$id.'/edit', $parameters, $method) : $supported;
    }

    /**
     * Edit a batch of items.
     *
     * @param bool $createIfNotExists
     *
     * @return array|mixed
     */
    public function editBatch(array $parameters, $createIfNotExists = false)
    {
        $method    = $createIfNotExists ? 'PUT' : 'PATCH';
        $supported = $this->isSupported('editBatch');

        return (true === $supported) ? $this->makeRequest($this->endpoint.'/batch/edit', $parameters, $method) : $supported;
    }

    /**
     * Delete an item.
     *
     * @param int $id
     *
     * @return array|mixed
     */
    public function delete($id)
    {
        $supported = $this->isSupported('delete');

        return (true === $supported) ? $this->makeRequest($this->endpoint.'/'.$id.'/delete', [], 'DELETE') : $supported;
    }

    /**
     * Delete a batch of items.
     *
     * @return array|mixed
     */
    public function deleteBatch(array $ids)
    {
        $supported = $this->isSupported('deleteBatch');

        return (true === $supported) ? $this->makeRequest($this->endpoint.'/batch/delete', ['ids' => $ids], 'DELETE') : $supported;
    }

    /**
     * Returns a not supported error.
     *
     * @param string $action
     *
     * @return array
     */
    protected function actionNotSupported($action)
    {
        return [
            'errors' => [
                [
                    'code'    => 500,
                    'message' => "$action is not supported at this time.",
                ],
            ],
        ];
    }

    /**
     * Verify that a default endpoint is supported by the API.
     *
     * @param string $action
     *
     * @return bool
     */
    protected function isSupported($action)
    {
        if (empty($this->endpointsSupported) || in_array($action, $this->endpointsSupported)) {
            return true;
        }

        return $this->actionNotSupported($action);
    }
}
