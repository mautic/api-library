<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Api;

/**
 * Reports Context
 */
class Reports extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'reports';

    /**
     * {@inheritdoc}
     */
    protected $listName = 'reports';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'report';

    /**
     * {@inheritdoc}
     */
    protected $searchCommands = array(
        'ids',
        'is:published',
        'is:unpublished',
        'is:mine',
    );

    /**
     * @var array
     */
    protected $endpointsSupported = array(
        'get',
        'getList'
    );

    /**
     * Get a single report data
     *
     * @param int $id
     * @param int $limit
     * @param int $page
     * @param \DateTimeInterface|null $dateFrom
     * @param \DateTimeInterface|null $dateTo
     *
     * @return array|mixed
     */
    public function get($id, $limit = 10, $page = 1, \DateTimeInterface $dateFrom = null, \DateTimeInterface $dateTo = null)
    {
        $options = array(
            'limit' => (int) $limit,
            'page'  => (int) $page,
        );

        if ($dateFrom) {
            $options['dateFrom'] = $dateFrom->format('c');
        }

        if ($dateTo) {
            $options['dateTo'] = $dateTo->format('c');
        }

        return $this->makeRequest("{$this->endpoint}/$id", $options);
    }
}
