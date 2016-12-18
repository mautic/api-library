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
 */
class Campaigns extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'campaigns';

    /**
     * {@inheritdoc}
     */
    protected $listName = 'campaigns';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'campaign';

    /**
     * Add a lead to the campaign
     *
     * @deprecated 2.0.1, use addContact instead
     *
     * @param int $id     Campaign ID
     * @param int $leadId Lead ID
     *
     * @return array|mixed
     */
    public function addLead($id, $leadId)
    {
        return $this->addContact($id, $leadId);
    }

    /**
     * Add a contact to the campaign
     *
     * @param int $id        Campaign ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function addContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/add/'.$contactId, array(), 'POST');
    }

    /**
     * Remove a lead from the campaign
     *
     * @deprecated 2.0.1, use removeContact instead
     *
     * @param int $id     Campaign ID
     * @param int $leadId Lead ID
     *
     * @return array|mixed
     */
    public function removeLead($id, $leadId)
    {
        return $this->removeContact($id, $leadId);
    }

    /**
     * Remove a contact from the campaign
     *
     * @param int $id        Campaign ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function removeContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/remove/'.$contactId, array(), 'POST');
    }
}
