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
 * Leads Context
 */
class Leads extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'leads';

    /**
     * Get a list of users available as lead owners
     *
     * @return array|mixed
     */
    public function getOwners()
    {
        return $this->makeRequest('leads/list/owners');
    }

    /**
     * Get a list of custom fields
     *
     * @return array|mixed
     */
    public function getFieldList()
    {
        return $this->makeRequest('leads/list/fields');
    }

    /**
     * Get a list of lead lists
     *
     * @return array|mixed
     */
    public function getLists()
    {
        return $this->makeRequest('leads/list/lists');
    }

    /**
     * Get the notes on a lead
     *
     * @param int $id ID of lead
     *
     * @return array|mixed
     */
    public function getNotes($id)
    {
        return $this->makeRequest('leads/'.$id.'/notes');
    }
}
