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
 * Campaigns Context.
 */
class Campaigns extends Api
{
    protected $endpoint = 'campaigns';

    protected $listName = 'campaigns';

    protected $itemName = 'campaign';

    /**
     * @var array
     */
    protected $bcRegexEndpoints = [
        'campaigns/(.*?)/contact/(.*?)/add'    => 'campaigns/$1/contact/add/$2', // 2.6.0
        'campaigns/(.*?)/contact/(.*?)/remove' => 'campaigns/$1/contact/remove/$2', // 2.6.0
    ];

    protected $searchCommands = [
        'ids',
        'is:published',
        'is:unpublished',
        'is:mine',
        'is:uncategorized',
        'category',
    ];

    /**
     * Add a lead to the campaign.
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
     * Add a contact to the campaign.
     *
     * @param int $id        Campaign ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function addContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/'.$contactId.'/add', [], 'POST');
    }

    /**
     * Remove a lead from the campaign.
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
     * Remove a contact from the campaign.
     *
     * @param int $id        Campaign ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function removeContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/'.$contactId.'/remove', [], 'POST');
    }

    /**
     * Get a list of stat items.
     *
     * @param int $id    Campaign ID
     * @param int $start
     * @param int $limit
     *
     * @return array|mixed
     */
    public function getContacts($id, $start = 0, $limit = 0, array $order = [], array $where = [])
    {
        $parameters = [];
        $args       = ['start', 'limit', 'order', 'where'];

        foreach ($args as $arg) {
            if (!empty($$arg)) {
                $parameters[$arg] = $$arg;
            }
        }

        return $this->makeRequest($this->endpoint.'/'.$id.'/contacts', $parameters);
    }

    /**
     * Clone an Existing campaign.
     *
     * @param int $id Campaign ID
     *
     * @return array|mixed
     */
    public function cloneCampaign($id)
    {
        return $this->makeRequest($this->endpoint.'/clone/'.$id, [], 'POST');
    }
}
