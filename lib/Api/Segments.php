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
 * Segments Context.
 */
class Segments extends Api
{
    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'segments';

    /**
     * {@inheritdoc}
     */
    protected $listName = 'lists';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'list';

    /**
     * @var array
     */
    protected $bcRegexEndpoints = [
        'segments/(.*?)/contact/(.*?)/add'    => 'segments/$1/contact/add/$2', // 2.6.0
        'segments/(.*?)/contact/(.*?)/remove' => 'segments/$1/contact/remove/$2', // 2.6.0
    ];

    /**
     * Add a contact to the segment.
     *
     * @param int $segmentId Segment ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function addContact($segmentId, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$segmentId.'/contact/'.$contactId.'/add', [], 'POST');
    }

    /**
     * Add a contact list of ids to the segment
     * list of contact must be added in ids[] query parameter.
     *
     * @param int   $segmentId  Segment ID
     * @param array $contactIds
     *
     * @return array|mixed
     */
    public function addContacts($segmentId, $contactIds)
    {
        return $this->makeRequest($this->endpoint.'/'.$segmentId.'/contacts/add', $contactIds, 'POST');
    }

    /**
     * Add a lead to the segment.
     *
     * @deprecated 2.0.1, use addContact() instead
     *
     * @param int $id     Segment ID
     * @param int $leadId Lead ID
     *
     * @return array|mixed
     */
    public function addLead($id, $leadId)
    {
        return $this->addContact($id, $leadId);
    }

    /**
     * Remove a contact from the segment.
     *
     * @param int $segmentId Segment ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function removeContact($segmentId, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$segmentId.'/contact/'.$contactId.'/remove', [], 'POST');
    }

    /**
     * Remove a lead from the segment.
     *
     * @deprecated 2.0.1, use addContact() instead
     *
     * @param int $id     Segment ID
     * @param int $leadId Lead ID
     *
     * @return array|mixed
     */
    public function removeLead($id, $leadId)
    {
        return $this->removeContact($id, $leadId);
    }
}
