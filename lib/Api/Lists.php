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
 */
class Lists extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'lists';

    /**
     * Add a lead to the list
     *
     * @param int $id     List ID
     * @param int $leadId Lead ID
     *
     * @return array|mixed
     */
    public function addLead($id, $leadId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/lead/add/'.$leadId, array(), 'POST');
    }


    /**
     * Remove a lead from the list
     *
     * @param int $id     List ID
     * @param int $leadId Lead ID
     *
     * @return array|mixed
     */
    public function removeLead($id, $leadId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/lead/remove/'.$leadId, array(), 'POST');
    }
}
