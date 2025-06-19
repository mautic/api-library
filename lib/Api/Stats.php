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

/**
 * Stats Context.
 */
class Stats extends Api
{
    protected $endpoint = 'stats';

    protected $listName = 'stats';

    /**
     * Get a list of stat items.
     *
     * @param string $table
     * @param int    $start
     * @param int    $limit
     *
     * @return array
     */
    public function get($table = '', $start = 0, $limit = 0, array $order = [], array $where = [])
    {
        $parameters = [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
            'where' => $where,
        ];

        $parameters = array_filter($parameters);

        $endpoint = $this->endpoint;
        if ($table) {
            $endpoint .= '/'.$table;
        }

        return $this->makeRequest($endpoint, $parameters);
    }

    public function delete($id)
    {
        return $this->actionNotSupported('delete');
    }

    public function getList($search = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC', $publishedOnly = false, $minimal = false)
    {
        return $this->actionNotSupported('getList');
    }

    public function create(array $parameters)
    {
        return $this->actionNotSupported('create');
    }

    public function getPublishedList($search = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC')
    {
        return $this->actionNotSupported('getPublishedList');
    }

    public function edit($id, array $parameters, $createIfNotExists = false)
    {
        return $this->actionNotSupported('edit');
    }
}
