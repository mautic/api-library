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
    public function create(array $parameters)
    {
        return $this->actionNotSupported('create');
    }

    /**
     * {@inheritdoc}
     */
    public function edit($id, array $parameters, $createIfNotExists = false)
    {
        return $this->actionNotSupported('edit');
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->actionNotSupported('delete');
    }

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
     * Send email to a specific lead
     *
     * @param int $id
     * @param int $leadId
     *
     * @return array|mixed
     */
    public function sendToLead($id, $leadId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/send/lead/'.$leadId, array(), 'POST');
    }
}
