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
 * Segments Context
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
     * Add a contact to the segment
     *
     * @param int $segmentId Segment ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function addContact($segmentId, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$segmentId.'/contact/add/'.$contactId, array(), 'POST');
    }

    /**
     * Add a lead to the segment
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
     * Remove a contact from the segment
     *
     * @param int $segmentId Segment ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function removeContact($segmentId, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$segmentId.'/contact/remove/'.$contactId, array(), 'POST');
    }

    /**
     * Remove a lead from the segment
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
