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
 *
 * @package Mautic\Api
 */
class Emails extends Api
{

    /**
     * @var string
     */
    protected $endpoint = 'emails';

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
     * Send email to the assigned lists
     *
     * @param $id
     *
     * @return array|mixed
     */
    public function send($id)
    {
        return $this->makeRequest($this->endpoint . '/' . $id . '/send', array(), 'POST');
    }

    /**
     * Send email to a specific lead
     *
     * @param $id
     * @param $leadId
     *
     * @return array|mixed
     */
    public function sendToLead($id, $leadId)
    {
        return $this->makeRequest($this->endpoint . '/' . $id . '/send/lead/' . $leadId, array(), 'POST');
    }
}