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
 * Lists Context
 *
 * @package Mautic\Api
 */
class Lists extends Api
{

    /**
     * @var string
     */
    protected $endpoint = 'lists';

    /**
     * Add a lead to the list
     *
     * @param $id       List ID
     * @param $leadId   Lead ID
     *
     * @return array|mixed
     */
    public function addLead($id, $leadId)
    {
        return $this->makeRequest($this->endpoint . '/' . $id . '/lead/add/' . $leadId, array(), 'POST');
    }


    /**
     * Remove a lead from the list
     *
     * @param $id       List ID
     * @param $leadId   Lead ID
     *
     * @return array|mixed
     */
    public function removeLead($id, $leadId)
    {
        return $this->makeRequest($this->endpoint . '/' . $id . '/lead/remove/' . $leadId, array(), 'POST');
    }
}