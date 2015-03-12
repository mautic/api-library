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
 * Campaigns Context
 *
 * @package Mautic\Api
 */
class Campaigns extends Api
{

    /**
     * @var string
     */
    protected $endpoint = 'campaigns';

    /**
     * {@inheritdoc}
     *
     * @param array $parameters
     */
    public function create(array $parameters)
    {
        return $this->actionNotSupported('create');
    }

    /**
     * {@inheritdoc}
     *
     * @param int   $id
     * @param array $parameters
     * @param bool  $createIfNotExists = false
     *
     * @return array|mixed
     */
    public function edit($id, array $parameters, $createIfNotExists = false)
    {
        return $this->actionNotSupported('edit');
    }

    /**
     * {@inheritdoc}
     *
     * @param $id
     *
     * @return array|mixed
     */
    public function delete($id)
    {
        return $this->actionNotSupported('delete');
    }

    /**
     * Add a lead to the campaign
     *
     * @param $id       Campaign ID
     * @param $leadId   Lead ID
     *
     * @return array|mixed
     */
    public function addLead($id, $leadId)
    {
        return $this->makeRequest($this->endpoint . '/' . $id . '/lead/add/' . $leadId, array(), 'POST');
    }


    /**
     * Remove a lead from the campaign
     *
     * @param $id       Campaign ID
     * @param $leadId   Lead ID
     *
     * @return array|mixed
     */
    public function removeLead($id, $leadId)
    {
        return $this->makeRequest($this->endpoint . '/' . $id . '/lead/remove/' . $leadId, array(), 'POST');
    }
}