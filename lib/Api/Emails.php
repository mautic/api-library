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
 * Emails Context
 */
class Emails extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'emails';

    /**
     * {@inheritdoc}
     */
    protected $listName = 'emails';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'email';


    /**
     * Send email to the assigned lists
     *
     * @param int $id
     *
     * @return array|mixed
     */
    public function send($id)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/send', array(), 'POST');
    }

    /**
     * Send email to a specific contact
     *
     * @param int $id
     * @param int $contactId
     * @param array $data
     *
     * @return array|mixed
     */
    public function sendToContact($id, $contactId, $data = [])
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/send/contact/'.$contactId, $data, 'POST');
    }

    /**
     * Send custom email to a specific contact
     *
     * @param int $id
     * @param int $contactId
     * @param array $data
     *
     * @return array|mixed
     */
    public function sendCustomToContact($id, $contactId, $data = [])
    {
        return $this->makeRequest($this->endpoint.'/contact/'.$contactId.'/send/custom', $data, 'POST');
    }

    /**
     * Send email to a specific lead
     *
     * @deprecated use sendToContact instead
     *
     * @param int $id
     * @param int $leadId
     *
     * @return array|mixed
     */
    public function sendToLead($id, $leadId)
    {
        return $this->sendToContact($id, $leadId);
    }
}
