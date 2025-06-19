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
 * Data Context.
 */
class Data extends Api
{
    protected $endpoint = 'data';

    protected $listName = 'types';

    protected $itemName = 'data';

    /**
     * Get a single item.
     *
     * @param int   $id
     * @param array $options
     *
     * @return array|mixed
     */
    public function get($id, $options = [])
    {
        return $this->makeRequest("{$this->endpoint}/$id", $options);
    }

    public function getPublishedList($search = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC')
    {
        return $this->actionNotSupported(__FUNCTION__);
    }

    public function create(array $parameters)
    {
        return $this->actionNotSupported(__FUNCTION__);
    }

    public function edit($id, array $parameters, $createIfNotExists = false)
    {
        return $this->actionNotSupported(__FUNCTION__);
    }

    public function delete($id)
    {
        return $this->actionNotSupported(__FUNCTION__);
    }
}
